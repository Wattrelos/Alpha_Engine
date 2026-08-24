<?php

declare(strict_types=1);

namespace Alpha\Services\Audit;

use Alpha\Events\QueueService;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Support\LgpdSanitizer;
use PDO;
use Throwable;

/**
 * AuditLoggerService - Serviço Centralizado de Auditoria da Alpha Engine.
 * 
 * Oferece estratégia de auditoria multinível resiliente:
 *   Nível 1: Publicação assíncrona no RabbitMQ (mensageria de alta performance).
 *   Nível 2 (Fallback Hostinger/MySQL): Inserção na tabela `tbkk_audit_logs` no MySQL com auto-provisionamento.
 *   Nível 3 (Fallback Emergencial): Gravação em arquivo de log local storage/logs/audit.log.
 * 
 * Todos os eventos são sanitizados obrigatoriamente via LgpdSanitizer antes da persistência.
 */
class AuditLoggerService
{
    private static bool $tableChecked = false;
    private ?QueueService $queueService;

    public function __construct(?QueueService $queueService = null)
    {
        $this->queueService = $queueService ?? new QueueService();
    }

    /**
     * Registra um evento de auditoria no sistema aplicando higienização LGPD e fallback automático.
     */
    public function logEvent(
        string $event,
        array $payload = [],
        string $username = 'ANONYMOUS',
        string $ip = '',
        int $storeId = 1
    ): bool {
        $ip = !empty($ip) ? $ip : ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User-Agent';

        // 1. Sanitiza todos os dados de entrada de acordo com as diretrizes da LGPD
        $sanitizedPayload = LgpdSanitizer::sanitizeArray($payload);
        $cleanUsername = LgpdSanitizer::sanitizeLogMessage($username);

        $eventData = [
            'store_id'   => $storeId,
            'event'      => $event,
            'username'   => $cleanUsername,
            'ip'         => $ip,
            'user_agent' => mb_substr($userAgent, 0, 255),
            'payload'    => $sanitizedPayload,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Tenta Nível 1: Mensageria RabbitMQ se ativada no .env
        $rabbitEnabled = filter_var($_ENV['RABBITMQ_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($rabbitEnabled) {
            try {
                $this->queueService->publish('audit_events', $eventData);
                return true;
            } catch (Throwable $e) {
                // Falha no RabbitMQ: prossegue para o Fallback MySQL
            }
        }

        // Tenta Nível 2 (Hostinger Fallback): Gravacao direta no MySQL (tbkk_audit_logs)
        try {
            return $this->logToDatabase($eventData);
        } catch (Throwable $e) {
            // Falha no MySQL: prossegue para Nível 3 (Arquivo Local)
            return $this->logToFile($eventData);
        }
    }

    /**
     * Insere a entrada de auditoria na tabela MySQL tbkk_audit_logs.
     */
    private function logToDatabase(array $data): bool
    {
        $this->ensureTableExists();

        $db = ConnectionDB::getInstance()->getConnection();
        $sql = "INSERT INTO " . (defined('DB_PREFIX') ? DB_PREFIX : 'tbkk_') . "audit_logs 
                (store_id, event, username, ip, user_agent, payload, created_at) 
                VALUES (:store_id, :event, :username, :ip, :user_agent, :payload, :created_at)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':store_id'   => $data['store_id'],
            ':event'      => $data['event'],
            ':username'   => $data['username'],
            ':ip'         => $data['ip'],
            ':user_agent' => $data['user_agent'],
            ':payload'    => json_encode($data['payload'], JSON_UNESCAPED_UNICODE),
            ':created_at' => $data['created_at']
        ]);
    }

    /**
     * Fallback emergencial em arquivo texto storage/logs/audit.log.
     */
    private function logToFile(array $data): bool
    {
        $logDir = defined('DIR_STORAGE') ? DIR_STORAGE . 'logs/' : dirname(__DIR__, 3) . '/storage/logs/';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . 'audit.log';

        $entry = sprintf(
            "[%s] STORE: %d | EVENT: '%s' | USER: '%s' | IP: '%s' | PAYLOAD: %s\n",
            $data['created_at'],
            $data['store_id'],
            $data['event'],
            $data['username'],
            $data['ip'],
            json_encode($data['payload'], JSON_UNESCAPED_UNICODE)
        );

        $result = @file_put_contents($logFile, $entry, FILE_APPEND) !== false;
        @chmod($logFile, 0666);
        return $result;
    }

    /**
     * Provisionamento automático da tabela tbkk_audit_logs no MySQL se ausente.
     */
    private function ensureTableExists(): void
    {
        if (self::$tableChecked) {
            return;
        }

        try {
            $db = ConnectionDB::getInstance()->getConnection();
            $tableName = (defined('DB_PREFIX') ? DB_PREFIX : 'tbkk_') . 'audit_logs';

            $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `store_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `event` VARCHAR(100) NOT NULL,
                `username` VARCHAR(191) NULL,
                `ip` VARCHAR(45) NOT NULL,
                `user_agent` VARCHAR(255) NULL,
                `payload` JSON NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_event` (`event`),
                INDEX `idx_store_id` (`store_id`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $db->exec($sql);
            self::$tableChecked = true;
        } catch (Throwable $e) {
            error_log("Erro ao verificar/criar tabela de auditoria: " . $e->getMessage());
        }
    }

    /**
     * Consulta os registros de auditoria salvos no MySQL.
     */
    public function getAuditLogs(int $storeId = 1, int $limit = 50, int $offset = 0): array
    {
        try {
            $this->ensureTableExists();
            $db = ConnectionDB::getInstance()->getConnection();
            $tableName = (defined('DB_PREFIX') ? DB_PREFIX : 'tbkk_') . 'audit_logs';

            $sql = "SELECT id, store_id, event, username, ip, user_agent, payload, created_at 
                    FROM `{$tableName}` 
                    WHERE store_id = :store_id 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':store_id', $storeId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$row) {
                if (!empty($row['payload']) && is_string($row['payload'])) {
                    $row['payload'] = json_decode($row['payload'], true);
                }
            }
            return $results;
        } catch (Throwable $e) {
            error_log("Erro ao buscar logs de auditoria: " . $e->getMessage());
            return [];
        }
    }
}
