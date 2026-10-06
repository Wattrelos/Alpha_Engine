<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use DateTimeImmutable;
use DateTimeZone;

/**
 * StockAlertMapper - Gerencia a persistência e consulta de alertas de estoque (Alpha Engine).
 * 
 * Conforme ADR 0008 e ADR 0005 (uso exclusivo de DATETIME em UTC).
 */
class StockAlertMapper extends BaseMapper
{
    protected string $tableName = 'product_stock_alert';

    /**
     * Insere uma nova solicitação ou renova uma pendência existente para o mesmo e-mail e produto.
     *
     * @param array $data
     * @return int ID do registro
     */
    public function subscribe(array $data): int
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $nowStr = $now->format('Y-m-d H:i:s');
        $expiresStr = $now->modify('+90 days')->format('Y-m-d H:i:s');

        $storeId = (int)($data['store_id'] ?? 1);
        $languageId = (int)($data['language_id'] ?? 2);
        $productId = (int)$data['product_id'];
        $variantId = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
        $customerId = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
        $name = trim($data['name'] ?? '');
        $email = trim(strtolower($data['email'] ?? ''));
        $phone = !empty($data['phone']) ? trim($data['phone']) : null;
        $ip = trim($data['ip'] ?? '127.0.0.1');
        $userAgent = !empty($data['user_agent']) ? substr(trim($data['user_agent']), 0, 255) : null;
        $consentPrivacy = !empty($data['consent_privacy']) ? 1 : 1;
        $consentMarketing = !empty($data['consent_marketing']) ? 1 : 0;
        $unsubscribeToken = hash('sha256', $productId . '|' . ($variantId ?? 0) . '|' . $email . '|' . bin2hex(random_bytes(16)));

        // Verifica duplicidade com status pendente
        $queryCheck = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("store_id = ?", [$storeId])
            ->where("product_id = ?", [$productId])
            ->where("email = ?", [$email])
            ->where("status = ?", ['pending']);

        if ($variantId !== null) {
            $queryCheck->where("variant_id = ?", [$variantId]);
        } else {
            $queryCheck->where("variant_id IS NULL");
        }

        $existing = $this->dao->executeQuery($queryCheck);

        if (!empty($existing)) {
            $existingId = (int)$existing[0]['id'];
            $updateSql = "UPDATE `" . $this->getFullTableName() . "` 
                          SET `name` = :name, 
                              `phone` = :phone, 
                              `ip` = :ip, 
                              `user_agent` = :user_agent, 
                              `consent_marketing` = :consent_marketing, 
                              `updated_at` = :updated_at, 
                              `expires_at` = :expires_at 
                          WHERE `id` = :id";
            $this->dao->executeRawSQL($updateSql, [
                'name' => $name,
                'phone' => $phone,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'consent_marketing' => $consentMarketing,
                'updated_at' => $nowStr,
                'expires_at' => $expiresStr,
                'id' => $existingId
            ]);
            return $existingId;
        }

        $insertSql = "INSERT INTO `" . $this->getFullTableName() . "` 
                      (`store_id`, `language_id`, `product_id`, `variant_id`, `customer_id`, `name`, `email`, `phone`, 
                       `status`, `unsubscribe_token`, `ip`, `user_agent`, `consent_privacy`, `consent_marketing`, 
                       `created_at`, `updated_at`, `expires_at`) 
                      VALUES 
                      (:store_id, :language_id, :product_id, :variant_id, :customer_id, :name, :email, :phone, 
                       'pending', :unsubscribe_token, :ip, :user_agent, :consent_privacy, :consent_marketing, 
                       :created_at, :updated_at, :expires_at)";

        $this->dao->executeRawSQL($insertSql, [
            'store_id' => $storeId,
            'language_id' => $languageId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'customer_id' => $customerId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'unsubscribe_token' => $unsubscribeToken,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'consent_privacy' => $consentPrivacy,
            'consent_marketing' => $consentMarketing,
            'created_at' => $nowStr,
            'updated_at' => $nowStr,
            'expires_at' => $expiresStr
        ]);

        return (int)\Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection()->lastInsertId();
    }

    /**
     * Localiza alerta por token único de cancelamento.
     */
    public function findByToken(string $token): ?array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("unsubscribe_token = ?", [$token])
            ->limit(1);

        $results = $this->dao->executeQuery($query);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Cancela o alerta a partir do token (Opt-out em 1 clique).
     */
    public function unsubscribeByToken(string $token): bool
    {
        $now = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $sql = "UPDATE `" . $this->getFullTableName() . "` 
                SET `status` = 'cancelled', `updated_at` = :updated_at 
                WHERE `unsubscribe_token` = :token AND `status` != 'cancelled'";
        
        $this->dao->executeRawSQL($sql, [
            'updated_at' => $now,
            'token' => $token
        ]);

        return true;
    }

    /**
     * Busca os alertas pendentes ordenados por FIFO (created_at ASC) respeitando a cota.
     */
    public function getPendingAlertsForReplenishment(int $storeId, int $productId, ?int $variantId = null, int $limit = 50): array
    {
        $nowStr = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("store_id = ?", [$storeId])
            ->where("product_id = ?", [$productId])
            ->where("status = ?", ['pending'])
            ->where("expires_at > ?", [$nowStr])
            ->orderBy("created_at", "ASC")
            ->limit($limit);

        if ($variantId !== null && $variantId > 0) {
            $query->where("(variant_id = ? OR variant_id IS NULL)", [$variantId]);
        }

        return $this->dao->executeQuery($query);
    }

    /**
     * Marca os alertas fornecidos como 'sent' e registra notified_at.
     */
    public function markAsSent(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $nowStr = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "UPDATE `" . $this->getFullTableName() . "` 
                SET `status` = 'sent', `notified_at` = ?, `updated_at` = ? 
                WHERE `id` IN ({$placeholders})";

        $params = array_merge([$nowStr, $nowStr], array_values($ids));
        $this->dao->executeRawSQL($sql, $params);
    }

    /**
     * Marca alertas expirados automaticamente com base na data limite.
     */
    public function expireOldAlerts(): int
    {
        $nowStr = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $sql = "UPDATE `" . $this->getFullTableName() . "` 
                SET `status` = 'expired', `updated_at` = :updated_at 
                WHERE `status` = 'pending' AND `expires_at` <= :now";

        $this->dao->executeRawSQL($sql, [
            'updated_at' => $nowStr,
            'now'        => $nowStr
        ]);
        return 1;
    }
}
