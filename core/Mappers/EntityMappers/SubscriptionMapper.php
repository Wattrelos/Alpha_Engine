<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Subscription;

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
        $languageId = $this->registry ? (int)$this->registry->get('config')->get('config_language_id') : 1;
        
        $sql = "SELECT `s`.`subscription_id`, `s`.*, CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) AS `customer`, 
                (SELECT `ss`.`name` FROM `" . DB_PREFIX . "subscription_status` `ss` 
                 WHERE `ss`.`subscription_status_id` = `s`.`subscription_status_id` 
                   AND `ss`.`language_id` = :language_id) AS `subscription_status` 
                FROM `" . DB_PREFIX . "subscription` `s` 
                LEFT JOIN `" . DB_PREFIX . "order` `o` ON (`s`.`order_id` = `o`.`order_id`)";

        $implode = [];
        $params = [
            'language_id' => $languageId
        ];

        if (!empty($data['filter_subscription_id'])) {
            $implode[] = "`s`.`subscription_id` = :filter_subscription_id";
            $params['filter_subscription_id'] = (int)$data['filter_subscription_id'];
        }

        if (!empty($data['filter_order_id'])) {
            $implode[] = "`s`.`order_id` = :filter_order_id";
            $params['filter_order_id'] = (int)$data['filter_order_id'];
        }
        
        if (!empty($data['filter_order_product_id'])) {
            $implode[] = "`s`.`order_product_id` = :filter_order_product_id";
            $params['filter_order_product_id'] = (int)$data['filter_order_product_id'];
        }

        if (!empty($data['filter_customer'])) {
            $implode[] = "CONCAT(`o`.`firstname`, ' ', `o`.`lastname`) LIKE :filter_customer";
            $params['filter_customer'] = $data['filter_customer'] . '%';
        }

        if (!empty($data['filter_date_next'])) {
            $implode[] = "DATE(`s`.`date_next`) = DATE(:filter_date_next)";
            $params['filter_date_next'] = $data['filter_date_next'];
        }

        if (!empty($data['filter_subscription_status_id'])) {
            $implode[] = "`s`.`subscription_status_id` = :filter_subscription_status_id";
            $params['filter_subscription_status_id'] = (int)$data['filter_subscription_status_id'];
        }

        if (!empty($data['filter_date_from'])) {
            $implode[] = "DATE(`s`.`date_added`) >= DATE(:filter_date_from)";
            $params['filter_date_from'] = $data['filter_date_from'];
        }

        if (!empty($data['filter_date_to'])) {
            $implode[] = "DATE(`s`.`date_added`) <= DATE(:filter_date_to)";
            $params['filter_date_to'] = $data['filter_date_to'];
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sort_data = [
            's.subscription_id',
            's.order_id',
            's.reference',
            'customer',
            's.subscription_status',
            's.date_added'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY `s`.`subscription_id`";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            $start = isset($data['start']) && $data['start'] >= 0 ? (int)$data['start'] : 0;
            $limit = isset($data['limit']) && $data['limit'] >= 1 ? (int)$data['limit'] : 20;
            $sql .= " LIMIT " . $start . "," . $limit;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
        $stmt = $this->db->prepare("SELECT * FROM `" . DB_PREFIX . "subscription_product` WHERE `subscription_id` = :subscription_id");
        $stmt->execute(['subscription_id' => $subscription_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retorna uma opção de produto da assinatura específica.
     */
    public function getOption(int $subscription_id, int $subscription_product_id, int $product_option_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM `" . DB_PREFIX . "subscription_option` 
                                    WHERE `subscription_id` = :subscription_id 
                                      AND `subscription_product_id` = :subscription_product_id 
                                      AND `product_option_id` = :product_option_id");
        $stmt->execute([
            'subscription_id' => $subscription_id,
            'subscription_product_id' => $subscription_product_id,
            'product_option_id' => $product_option_id
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Adiciona um registro no histórico de status de uma assinatura.
     */
    public function addHistory(int $subscription_id, int $subscription_status_id, string $comment = '', bool $notify = false): void
    {
        $stmt1 = $this->db->prepare("UPDATE `" . DB_PREFIX . "subscription` SET `subscription_status_id` = :subscription_status_id, `date_modified` = NOW() WHERE `subscription_id` = :subscription_id");
        $stmt1->execute([
            'subscription_status_id' => $subscription_status_id,
            'subscription_id' => $subscription_id
        ]);

        $stmt2 = $this->db->prepare("INSERT INTO `" . DB_PREFIX . "subscription_history` 
                                     SET `subscription_id` = :subscription_id, 
                                         `subscription_status_id` = :subscription_status_id, 
                                         `comment` = :comment, 
                                         `notify` = :notify, 
                                         `date_added` = NOW()");
        $stmt2->execute([
            'subscription_id' => $subscription_id,
            'subscription_status_id' => $subscription_status_id,
            'comment' => $comment,
            'notify' => (int)$notify
        ]);
    }

    /**
     * Adiciona uma linha de log para a auditoria de assinaturas.
     */
    public function addLog(int $subscription_id, string $code, string $description, bool $status = false): void
    {
        $stmt = $this->db->prepare("INSERT INTO `" . DB_PREFIX . "subscription_log` 
                                    SET `subscription_id` = :subscription_id, 
                                        `code` = :code, 
                                        `description` = :description, 
                                        `status` = :status, 
                                        `date_added` = NOW()");
        $stmt->execute([
            'subscription_id' => $subscription_id,
            'code' => $code,
            'description' => $description,
            'status' => (int)$status
        ]);
    }
}
