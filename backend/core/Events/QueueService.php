<?php

namespace Alpha\Events;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * QueueService - Gerencia conexões e publicação de mensagens no servidor RabbitMQ.
 */
class QueueService
{
    private string $host;
    private int $port;
    private string $user;
    private string $password;

    public function __construct()
    {
        $this->host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
        $this->port = (int)($_ENV['RABBITMQ_PORT'] ?? 5672);
        $this->user = $_ENV['RABBITMQ_USER'] ?? 'guest';
        $this->password = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';
    }

    /**
     * Conecta, declara a fila de forma persistente e publica o payload.
     *
     * @param string $queueName
     * @param array $data
     * @return void
     */
    public function publish(string $queueName, array $data): void
    {
        try {
            $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);
            $channel = $connection->channel();

            // Declara a fila como durável (persistência mesmo se o broker reiniciar)
            $channel->queue_declare($queueName, false, true, false, false);

            $msgBody = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Define a entrega como persistente (delivery_mode = 2)
            $msg = new AMQPMessage($msgBody, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/json'
            ]);

            $channel->basic_publish($msg, '', $queueName);

            $channel->close();
            $connection->close();
        } catch (\Throwable $e) {
            // Em caso de falha crítica na mensageria, loga o erro sem interromper a execução do PHP.
            error_log("RabbitMQ Publish Error: " . $e->getMessage());
        }
    }
}
