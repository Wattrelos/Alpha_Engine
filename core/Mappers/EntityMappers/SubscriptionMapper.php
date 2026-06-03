<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Subscription;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * SubscriptionMapper
 * Gerencia as operações de banco de dados para Assinaturas e Recorrências.
 */
class SubscriptionMapper extends BaseMapper
{
    protected string $tableName = 'subscription';
    protected string $entityClass = Subscription::class;

    /**
     * Retorna assinaturas com base em filtros e paginação.
     */
    public function getSubscriptions(array $data): array
    {
        $languageId = $this->container ? (int)$this->container->get('config')->get('config_language_id') : 1;
        
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . "subscription", "s")
            ->leftJoin(DB_PREFIX . "order", "o", "`s`.`order_id` = `o`.`order_id`");

        $statusSubquery = "(SELECT `ss`.`name` FROM `" . DB_PREFIX . "subscription_status` `ss` WHERE `ss`.`subscription_status_id` = `s`.`subscription_status_id` AND `ss`.`language_id` = " . $languageId . ") AS `subscription_status`";

        $query->select(
            '`s`.`subscription_id`',
            '`s`.*',
            "CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) AS `customer`",
            $statusSubquery
        );

        if (!empty($data['filter_subscription_id'])) {
            $query->where("`s`.`subscription_id` = ?", [(int)$data['filter_subscription_id']]);
        }

        if (!empty($data['filter_order_id'])) {
            $query->where("`s`.`order_id` = ?", [(int)$data['filter_order_id']]);
        }
        
        if (!empty($data['filter_order_product_id'])) {
            $query->where("`s`.`order_product_id` = ?", [(int)$data['filter_order_product_id']]);
        }

        if (!empty($data['filter_customer'])) {
            $query->where("CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) LIKE ?", [$data['filter_customer'] . '%']);
        }

        if (!empty($data['filter_date_next'])) {
            $query->where("DATE(`s`.`date_next`) = DATE(?)", [$data['filter_date_next']]);
        }

        if (!empty($data['filter_subscription_status_id'])) {
            $query->where("`s`.`subscription_status_id` = ?", [(int)$data['filter_subscription_status_id']]);
        }

        if (!empty($data['filter_date_from'])) {
            $query->where("DATE(`s`.`date_added`) >= DATE(?)", [$data['filter_date_from']]);
        }

        if (!empty($data['filter_date_to'])) {
            $query->where("DATE(`s`.`date_added`) <= DATE(?)", [$data['filter_date_to']]);
        }

        $sort_data = [
            's.subscription_id',
            's.order_id',
            's.reference',
            'customer',
            's.subscription_status',
            's.date_added'
        ];

        $direction = 'ASC';
        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $direction = 'DESC';
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $query->orderBy($data['sort'], $direction);
        } else {
            $query->orderBy('`s`.`subscription_id`', $direction);
        }

        if (isset($data['start']) || isset($data['limit'])) {
            $start = isset($data['start']) && $data['start'] >= 0 ? (int)$data['start'] : 0;
            $limit = isset($data['limit']) && $data['limit'] >= 1 ? (int)$data['limit'] : 20;
            $query->limit($limit)->offset($start);
        }

        $rows = $this->dao->executeQuery($query);

        $subscription_data = [];
        foreach ($rows as $key => $result) {
            $subscription_data[$key] = [
                'payment_method'  => $result['payment_method'] ? json_decode($result['payment_method'], true) : [],
                'shipping_method' => $result['shipping_method'] ? json_decode($result['shipping_method'], true) : []
            ] + $result;
        }

        return $subscription_data;
    }

    /**
     * Retorna os produtos associados a uma assinatura específica.
     */
    public function getProducts(int $subscription_id): array
    {
        $query = (new QueryBuilder())
            ->select('*')
            ->from(DB_PREFIX . "subscription_product")
            ->where("`subscription_id` = ?", [$subscription_id]);
        return $this->dao->executeQuery($query);
    }

    /**
     * Retorna uma opção de produto da assinatura específica.
     */
    public function getOption(int $subscription_id, int $subscription_product_id, int $product_option_id): array
    {
        $query = (new QueryBuilder())
            ->select('*')
            ->from(DB_PREFIX . "subscription_option")
            ->where("`subscription_id` = ?", [$subscription_id])
            ->where("`subscription_product_id` = ?", [$subscription_product_id])
            ->where("`product_option_id` = ?", [$product_option_id]);
        $results = $this->dao->executeQuery($query);
        return $results[0] ?? [];
    }

    /**
     * Adiciona um registro no histórico de status de uma assinatura.
     */
    public function addHistory(int $subscription_id, int $subscription_status_id, string $comment = '', bool $notify = false): void
    {
        $updateQuery = (new QueryBuilder())
            ->update(DB_PREFIX . "subscription")
            ->set('`subscription_status_id`', $subscription_status_id)
            ->set('`date_modified`', date('Y-m-d H:i:s'))
            ->where('`subscription_id` = ?', [$subscription_id]);
        $this->dao->execute($updateQuery);

        $insertHistorySql = "INSERT INTO `" . DB_PREFIX . "subscription_history` 
                             SET `subscription_id` = ?, 
                                 `subscription_status_id` = ?, 
                                 `comment` = ?, 
                                 `notify` = ?, 
                                 `date_added` = NOW()";
        $this->dao->executeRawSQL($insertHistorySql, [
            $subscription_id,
            $subscription_status_id,
            $comment,
            (int)$notify
        ]);
    }

    /**
     * Adiciona uma linha de log para a auditoria de assinaturas.
     */
    public function addLog(int $subscription_id, string $code, string $description, bool $status = false): void
    {
        $insertLogSql = "INSERT INTO `" . DB_PREFIX . "subscription_log` 
                          SET `subscription_id` = ?, 
                              `code` = ?, 
                              `description` = ?, 
                              `status` = ?, 
                              `date_added` = NOW()";
        $this->dao->executeRawSQL($insertLogSql, [
            $subscription_id,
            $code,
            $description,
            (int)$status
        ]);
    }
}
