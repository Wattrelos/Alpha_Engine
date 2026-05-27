<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\InterfaceEntity;

/**
 * DownloadRepository
 * Gerencia os arquivos e produtos digitais comprados pelo cliente.
 */
class DownloadRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Legacy Bridge: Retorna os dados de um download específico atrelado ao cliente.
     */
    public function getDownload(int $download_id): array
    {
        $implode = [];
        $order_statuses = (array)$this->config->get('config_complete_status');

        foreach ($order_statuses as $order_status_id) {
            $implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
        }

        if ($implode) {
            $query = $this->db->query("SELECT `d`.`filename`, `d`.`mask` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) LEFT JOIN `" . DB_PREFIX . "product_to_download` `p2d` ON (`op`.`product_id` = `p2d`.`product_id`) LEFT JOIN `" . DB_PREFIX . "download` `d` ON (`p2d`.`download_id` = `d`.`download_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $implode) . ") AND `d`.`download_id` = '" . (int)$download_id . "'");

            return $query->row;
        }

        return [];
    }

    /**
     * Legacy Bridge: Retorna a lista de todos os downloads disponíveis para o cliente.
     */
    public function getDownloads(int $start = 0, int $limit = 20): array
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 20;
        }

        $implode = [];
        $order_statuses = (array)$this->config->get('config_complete_status');

        foreach ($order_statuses as $order_status_id) {
            $implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
        }

        if ($implode) {
            $query = $this->db->query("SELECT DISTINCT `d`.`download_id`, `o`.`order_id`, `o`.`date_added`, `dd`.`name`, `d`.`filename` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) LEFT JOIN `" . DB_PREFIX . "product_to_download` `p2d` ON (`op`.`product_id` = `p2d`.`product_id`) LEFT JOIN `" . DB_PREFIX . "download` `d` ON (`p2d`.`download_id` = `d`.`download_id`) LEFT JOIN `" . DB_PREFIX . "download_description` `dd` ON (`d`.`download_id` = `dd`.`download_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' AND `o`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND (" . implode(" OR ", $implode) . ") AND `dd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY `d`.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

            return $query->rows;
        }

        return [];
    }

    /**
     * Legacy Bridge: Retorna o total de downloads para paginação.
     */
    public function getTotalDownloads(): int
    {
        $implode = [];
        $order_statuses = (array)$this->config->get('config_complete_status');

        foreach ($order_statuses as $order_status_id) {
            $implode[] = "`o`.`order_status_id` = '" . (int)$order_status_id . "'";
        }

        if ($implode) {
            $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` `o` LEFT JOIN `" . DB_PREFIX . "order_product` `op` ON (`o`.`order_id` = `op`.`order_id`) LEFT JOIN `" . DB_PREFIX . "product_to_download` `p2d` ON (`op`.`product_id` = `p2d`.`product_id`) WHERE `o`.`customer_id` = '" . (int)$this->customer->getId() . "' AND `o`.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND (" . implode(" OR ", $implode) . ") AND `p2d`.`download_id` > '0'");

            return $query->row['total'];
        }

        return 0;
    }

    /**
     * Legacy Bridge: Registra relatórios de downloads efetuados (Auditoria).
     */
    public function addReport(int $download_id, string $ip, string $country = ''): void
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "download_report` SET `download_id` = '" . (int)$download_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `ip` = '" . $this->db->escape($ip) . "', `country` = '" . $this->db->escape($country) . "', `date_added` = NOW()");
    }

    // --- Implementações Obrigatórias da Interface BaseRepositoryInterface ---

    public function find(int $id): ?InterfaceEntity
    {
        return null;
    }

    public function findAll(): array
    {
        return [];
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return null;
    }
}