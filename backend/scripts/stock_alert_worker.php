<?php

declare(strict_types=1);

/**
 * Worker CLI de Notificação de Reposição de Estoque ("Avise-me quando chegar" - ADR 0008)
 * 
 * Consome eventos de reposição da fila 'notification.stock_alert' do RabbitMQ
 * e aplica o disparo em lote proporcional com prioridade FIFO.
 * 
 * Uso via CLI:
 *   php scripts/stock_alert_worker.php [--once] [--max-messages=100]
 *   php scripts/stock_alert_worker.php --product-id=1042 [--variant-id=308] --quantity=5
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Containers\AppContainer;
use Alpha\Services\Notification\StockAlertService;
use Alpha\Model\Domain\Repositories\StockAlertRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Mappers\MapperFactory;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo "[ERRO] vendor/autoload.php não encontrado.\n";
    exit(1);
}
require_once $autoloadPath;
require_once __DIR__ . '/../config.php';

$container = new AppContainer();
$mapperFactory = new MapperFactory($container);
$repoFactory = new \Alpha\Model\Domain\Repositories\RepositoryFactory($mapperFactory, $container);
$stockAlertRepo = $repoFactory->get(StockAlertRepository::class);
$productRepo = $repoFactory->get(ProductRepository::class);
$service = new StockAlertService($stockAlertRepo, $productRepo, $container);

$opts = getopt('', ['once', 'max-messages:', 'product-id:', 'variant-id:', 'quantity:', 'expire-old']);

// Modo 1: Expiração manual de alertas obsoletos (TTL de 90 dias)
if (isset($opts['expire-old'])) {
    /** @var \Alpha\Mappers\EntityMappers\StockAlertMapper $mapper */
    $mapper = $mapperFactory->get(\Alpha\Mappers\EntityMappers\StockAlertMapper::class);
    $expired = $mapper->expireOldAlerts();
    echo sprintf("[%s] Rotina de limpeza: alertas antigos marcados como expirados.\n", date('Y-m-d H:i:s'));
    exit(0);
}

// Modo 2: Processamento manual direto para um SKU
if (isset($opts['product-id'])) {
    $prodId = (int)$opts['product-id'];
    $varId = isset($opts['variant-id']) ? (int)$opts['variant-id'] : null;
    $qty = isset($opts['quantity']) ? (int)$opts['quantity'] : 1;

    echo sprintf("[%s] Processando reposição manual para Produto ID %d (Variação: %s, Qtd: %d)...\n",
        date('Y-m-d H:i:s'), $prodId, $varId ?? 'N/A', $qty);

    $result = $service->processReplenishment($prodId, $varId, $qty);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit(0);
}

// Modo 3: Consumo contínuo da fila RabbitMQ
$host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
$port = (int)($_ENV['RABBITMQ_PORT'] ?? 5672);
$user = $_ENV['RABBITMQ_USER'] ?? 'guest';
$pass = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';
$queueName = 'notification.stock_alert';

$runOnce = isset($opts['once']);
$maxMessages = isset($opts['max-messages']) ? (int)$opts['max-messages'] : 0;

echo sprintf("[%s] Iniciando Worker de Alertas de Estoque (Fila: '%s', Host: '%s:%d')...\n", 
    date('Y-m-d H:i:s'), $queueName, $host, $port);

try {
    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $connection->channel();
    $channel->queue_declare($queueName, false, true, false, false);

    $processedCount = 0;

    $callback = function (AMQPMessage $msg) use ($service, &$processedCount, $maxMessages, $runOnce, $channel) {
        $body = $msg->getBody();
        $data = json_decode($body, true);

        if (is_array($data) && isset($data['product_id'])) {
            $productId = (int)$data['product_id'];
            $variantId = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
            $quantity = (int)($data['new_quantity'] ?? 1);
            $storeId = (int)($data['store_id'] ?? 1);

            $result = $service->processReplenishment($productId, $variantId, $quantity, $storeId);
            echo sprintf("[%s] Reposição processada: Produto #%d, Notificados: %d\n",
                date('Y-m-d H:i:s'), $productId, $result['notified_count'] ?? 0);
        }

        $msg->ack();
        $processedCount++;

        if ($maxMessages > 0 && $processedCount >= $maxMessages) {
            $channel->basic_cancel($msg->getConsumerTag());
        }
        if ($runOnce) {
            $channel->basic_cancel($msg->getConsumerTag());
        }
    };

    $channel->basic_qos(0, 1, false);
    $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
        if ($runOnce || ($maxMessages > 0 && $processedCount >= $maxMessages)) {
            break;
        }
    }

    $channel->close();
    $connection->close();
    echo sprintf("[%s] Worker finalizado. Total processado: %d mensagens.\n", date('Y-m-d H:i:s'), $processedCount);

} catch (\Throwable $e) {
    echo sprintf("[%s] Erro no Worker RabbitMQ: %s\n", date('Y-m-d H:i:s'), $e->getMessage());
    exit(1);
}
