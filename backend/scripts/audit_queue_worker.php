<?php

declare(strict_types=1);

/**
 * Worker CLI de Auditoria em Background — Alpha Engine
 * 
 * Consome eventos de auditoria publicados na fila durável 'audit_events' do RabbitMQ,
 * aplica a higienização de PII via LgpdSanitizer e grava os registros no log imutável storage/logs/audit.log.
 * 
 * Uso via CLI:
 *   php scripts/audit_queue_worker.php [--once] [--max-messages=100]
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Alpha\Support\LgpdSanitizer;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo "[ERRO] vendor/autoload.php não encontrado. Execute 'composer install'.\n";
    exit(1);
}
require_once $autoloadPath;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

$host = $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1';
$port = (int)($_ENV['RABBITMQ_PORT'] ?? 5672);
$user = $_ENV['RABBITMQ_USER'] ?? 'guest';
$pass = $_ENV['RABBITMQ_PASSWORD'] ?? 'guest';
$queueName = 'audit_events';

$opts = getopt('', ['once', 'max-messages:']);
$runOnce = isset($opts['once']);
$maxMessages = isset($opts['max-messages']) ? (int)$opts['max-messages'] : 0;

$logDir = __DIR__ . '/../storage/logs/';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
$logFile = $logDir . 'audit.log';

echo sprintf("[%s] Iniciando Worker de Auditoria (Fila: '%s', Host: '%s:%d')...\n", date('Y-m-d H:i:s'), $queueName, $host, $port);

try {
    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $connection->channel();
    $channel->queue_declare($queueName, false, true, false, false);

    $processedCount = 0;

    $callback = function (AMQPMessage $msg) use ($logFile, &$processedCount, $maxMessages, $runOnce, $channel) {
        $body = $msg->getBody();
        $data = json_decode($body, true);

        if (is_array($data)) {
            $sanitizedData = LgpdSanitizer::sanitizeArray($data);
            $logEntry = sprintf(
                "[%s] AUDIT_EVENT | Event: '%s' | User: '%s' | IP: '%s' | Payload: %s\n",
                date('Y-m-d H:i:s'),
                $sanitizedData['event'] ?? 'UNKNOWN',
                $sanitizedData['username'] ?? ($sanitizedData['user_id'] ?? 'ANONYMOUS'),
                $sanitizedData['ip'] ?? '0.0.0.0',
                json_encode($sanitizedData, JSON_UNESCAPED_UNICODE)
            );
        } else {
            $sanitizedMsg = LgpdSanitizer::sanitizeLogMessage($body);
            $logEntry = sprintf("[%s] AUDIT_RAW | %s\n", date('Y-m-d H:i:s'), $sanitizedMsg);
        }

        @file_put_contents($logFile, $logEntry, FILE_APPEND);
        $msg->ack();
        $processedCount++;

        echo sprintf("[%s] Evento #%d processado e gravado em audit.log\n", date('Y-m-d H:i:s'), $processedCount);

        if ($maxMessages > 0 && $processedCount >= $maxMessages) {
            echo sprintf("Atingido limite de %d mensagens. Encerrando worker.\n", $maxMessages);
            $channel->close();
        }
    };

    $channel->basic_qos(null, 1, null);
    $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    if ($runOnce) {
        echo "Modo --once ativado. Verificando mensagens pendentes...\n";
        $channel->wait(null, true, 2);
    } else {
        echo "Aguardando novas mensagens de auditoria. Pressione CTRL+C para sair.\n";
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    $channel->close();
    $connection->close();

} catch (\Throwable $e) {
    $fallbackMsg = sprintf("[%s] WORKER_FALLBACK | Erro na conexão RabbitMQ (%s). Evento registrado localmente.\n", date('Y-m-d H:i:s'), $e->getMessage());
    @file_put_contents($logFile, $fallbackMsg, FILE_APPEND);
    echo sprintf("[FALLBACK] RabbitMQ indisponível: %s\n", $e->getMessage());
}
