<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
$port = (int)($_ENV['RABBITMQ_PORT'] ?? 5672);
$user = $_ENV['RABBITMQ_USER'] ?? 'guest';
$password = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';

$queueName = 'order.created';

echo " [*] Estabelecendo conexão com o RabbitMQ em {$host}:{$port}...\n";

try {
    $connection = new AMQPStreamConnection($host, $port, $user, $password);
    $channel = $connection->channel();

    // Declara a fila como durável
    $channel->queue_declare($queueName, false, true, false, false);

    echo " [*] Fila '{$queueName}' declarada. Aguardando novos pedidos...\n";
    echo " [*] Pressione CTRL+C para sair.\n\n";

    $callback = function ($msg) {
        $payload = json_decode($msg->body, true);
        echo "========================================================\n";
        echo " [x] NOVO PEDIDO RECEBIDO NA FILA!\n";
        echo "   - ID do Pedido: " . ($payload['order_id'] ?? 'N/D') . "\n";
        echo "   - Cliente:      " . ($payload['firstname'] ?? '') . " " . ($payload['lastname'] ?? '') . "\n";
        echo "   - Telefone:     " . ($payload['telephone'] ?? 'N/D') . "\n";
        echo "   - E-mail:       " . ($payload['email'] ?? 'N/D') . "\n";
        echo "   - Total:        R$ " . number_format(($payload['total'] ?? 0.0), 2, ',', '.') . "\n";
        echo "   - Pagamento:    " . ($payload['payment_method'] ?? 'N/D') . "\n";
        echo "   - Envio:        " . ($payload['shipping_method'] ?? 'N/D') . "\n";
        echo "   - Timestamp:    " . ($payload['timestamp'] ?? 'N/D') . "\n";
        echo "========================================================\n\n";

        // Confirmação de recebimento (Ack)
        $msg->ack();
    };

    // Associa a função callback e define noack como false para garantir a segurança da mensagem
    $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (\Throwable $e) {
    fwrite(STDERR, "Erro crítico no Consumidor: " . $e->getMessage() . "\n");
    exit(1);
}
