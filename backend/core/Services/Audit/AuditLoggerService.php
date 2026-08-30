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
    private QueueService $queueService;

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
     * Consulta os registros de auditoria salvos no MySQL com suporte a filtros e paginação.
     */
    public function getAuditLogs(int $storeId = 1, int $limit = 50, int $offset = 0): array
    {
        return $this->getFilteredAuditLogs($storeId, $limit, $offset);
    }

    /**
     * Consulta registros de auditoria com filtros avançados.
     */
    public function getFilteredAuditLogs(
        int $storeId = 1,
        int $limit = 20,
        int $offset = 0,
        ?string $search = null,
        ?string $event = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        try {
            $this->ensureTableExists();
            $db = ConnectionDB::getInstance()->getConnection();
            $tableName = (defined('DB_PREFIX') ? DB_PREFIX : 'tbkk_') . 'audit_logs';

            $where = ["store_id = :store_id"];
            $params = [':store_id' => $storeId];

            if (!empty($search)) {
                $where[] = "(username LIKE :search1 OR ip LIKE :search2 OR event LIKE :search3 OR payload LIKE :search4)";
                $params[':search1'] = '%' . $search . '%';
                $params[':search2'] = '%' . $search . '%';
                $params[':search3'] = '%' . $search . '%';
                $params[':search4'] = '%' . $search . '%';
            }

            if (!empty($event)) {
                $where[] = "event = :event";
                $params[':event'] = $event;
            }

            if (!empty($startDate)) {
                $where[] = "created_at >= :start_date";
                $params[':start_date'] = $startDate . ' 00:00:00';
            }

            if (!empty($endDate)) {
                $where[] = "created_at <= :end_date";
                $params[':end_date'] = $endDate . ' 23:59:59';
            }

            $whereSql = implode(' AND ', $where);
            $sql = "SELECT id, store_id, event, username, ip, user_agent, payload, created_at 
                    FROM `{$tableName}` 
                    WHERE {$whereSql} 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                if (is_int($val)) {
                    $stmt->bindValue($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val, PDO::PARAM_STR);
                }
            }
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
            error_log("Erro ao buscar logs de auditoria filtrados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna a contagem total de registros de auditoria com os mesmos filtros aplicados.
     */
    public function getTotalAuditLogsCount(
        int $storeId = 1,
        ?string $search = null,
        ?string $event = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): int {
        try {
            $this->ensureTableExists();
            $db = ConnectionDB::getInstance()->getConnection();
            $tableName = (defined('DB_PREFIX') ? DB_PREFIX : 'tbkk_') . 'audit_logs';

            $where = ["store_id = :store_id"];
            $params = [':store_id' => $storeId];

            if (!empty($search)) {
                $where[] = "(username LIKE :search1 OR ip LIKE :search2 OR event LIKE :search3 OR payload LIKE :search4)";
                $params[':search1'] = '%' . $search . '%';
                $params[':search2'] = '%' . $search . '%';
                $params[':search3'] = '%' . $search . '%';
                $params[':search4'] = '%' . $search . '%';
            }

            if (!empty($event)) {
                $where[] = "event = :event";
                $params[':event'] = $event;
            }

            if (!empty($startDate)) {
                $where[] = "created_at >= :start_date";
                $params[':start_date'] = $startDate . ' 00:00:00';
            }

            if (!empty($endDate)) {
                $where[] = "created_at <= :end_date";
                $params[':end_date'] = $endDate . ' 23:59:59';
            }

            $whereSql = implode(' AND ', $where);
            $sql = "SELECT COUNT(*) FROM `{$tableName}` WHERE {$whereSql}";

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                if (is_int($val)) {
                    $stmt->bindValue($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val, PDO::PARAM_STR);
                }
            }
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log("Erro ao contar logs de auditoria: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna um registro de log específico por ID.
     */
    public function getAuditLogById(int $id, int $storeId = 1): ?array
    {
        try {
            $this->ensureTableExists();
            $db = ConnectionDB::getInstance()->getConnection();
            $tableName = (defined('DB_PREFIX') ? DB_PREFIX : 'tbkk_') . 'audit_logs';

            $sql = "SELECT id, store_id, event, username, ip, user_agent, payload, created_at 
                    FROM `{$tableName}` 
                    WHERE id = :id AND store_id = :store_id 
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':store_id', $storeId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                if (!empty($row['payload']) && is_string($row['payload'])) {
                    $row['payload'] = json_decode($row['payload'], true);
                }
                return $row;
            }
            return null;
        } catch (Throwable $e) {
            error_log("Erro ao buscar log de auditoria por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retorna estatísticas consolidadas dos logs de auditoria para o dashboard / painel.
     */
    public function getAuditStats(int $storeId = 1): array
    {
        try {
            $this->ensureTableExists();
            $db = ConnectionDB::getInstance()->getConnection();
            $tableName = (defined('DB_PREFIX') ? DB_PREFIX : 'tbkk_') . 'audit_logs';

            // 1. Total de requisições hoje
            $stmtToday = $db->prepare("SELECT COUNT(*) FROM `{$tableName}` WHERE store_id = :store_id AND DATE(created_at) = CURDATE()");
            $stmtToday->execute([':store_id' => $storeId]);
            $totalToday = (int)$stmtToday->fetchColumn();

            // 2. Total geral de logs
            $stmtTotal = $db->prepare("SELECT COUNT(*) FROM `{$tableName}` WHERE store_id = :store_id");
            $stmtTotal->execute([':store_id' => $storeId]);
            $totalAll = (int)$stmtTotal->fetchColumn();

            // 3. IPs únicos nas últimas 24 horas
            $stmtIps = $db->prepare("SELECT COUNT(DISTINCT ip) FROM `{$tableName}` WHERE store_id = :store_id AND created_at >= NOW() - INTERVAL 1 DAY");
            $stmtIps->execute([':store_id' => $storeId]);
            $uniqueIps24h = (int)$stmtIps->fetchColumn();

            // 4. Tipos de eventos mais frequentes
            $stmtEvents = $db->prepare("SELECT event, COUNT(*) as total FROM `{$tableName}` WHERE store_id = :store_id GROUP BY event ORDER BY total DESC LIMIT 5");
            $stmtEvents->execute([':store_id' => $storeId]);
            $topEvents = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

            // 5. Navegadores / dispositivos mais frequentes
            $stmtAgents = $db->prepare("SELECT user_agent, COUNT(*) as total FROM `{$tableName}` WHERE store_id = :store_id AND user_agent IS NOT NULL AND user_agent != '' GROUP BY user_agent ORDER BY total DESC LIMIT 10");
            $stmtAgents->execute([':store_id' => $storeId]);
            $rawAgents = $stmtAgents->fetchAll(PDO::FETCH_ASSOC);

            $browserCounts = ['Chrome' => 0, 'Firefox' => 0, 'Safari' => 0, 'Edge' => 0, 'Opera' => 0, 'Mobile/Outros' => 0];
            foreach ($rawAgents as $row) {
                $ua = $row['user_agent'] ?? '';
                $count = (int)$row['total'];
                if (stripos($ua, 'Edg') !== false) {
                    $browserCounts['Edge'] += $count;
                } elseif (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) {
                    $browserCounts['Opera'] += $count;
                } elseif (stripos($ua, 'Chrome') !== false) {
                    $browserCounts['Chrome'] += $count;
                } elseif (stripos($ua, 'Firefox') !== false) {
                    $browserCounts['Firefox'] += $count;
                } elseif (stripos($ua, 'Safari') !== false) {
                    $browserCounts['Safari'] += $count;
                } else {
                    $browserCounts['Mobile/Outros'] += $count;
                }
            }

            return [
                'total_today'    => $totalToday,
                'total_all'      => $totalAll,
                'unique_ips_24h' => $uniqueIps24h,
                'top_events'     => $topEvents,
                'browser_counts' => $browserCounts,
            ];
        } catch (Throwable $e) {
            error_log("Erro ao compilar estatísticas de auditoria: " . $e->getMessage());
            return [
                'total_today'    => 0,
                'total_all'      => 0,
                'unique_ips_24h' => 0,
                'top_events'     => [],
                'browser_counts' => [],
            ];
        }
    }
}
