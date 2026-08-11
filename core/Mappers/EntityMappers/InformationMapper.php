<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Páginas de Informação (Institucional)
 * 
 * Alpha Engine: Estende BaseMapper para herdar DAO, Registry e Cache.
 */
class InformationMapper extends BaseMapper {
    protected string $tableName = 'information';

    /**
     * Obtém uma página de informação específica otimizada via SQL (Store, Language e Status)
     */
    public function getInformation(int $information_id, int $language_id, int $store_id): array {
        static $cache = [];
        $cacheKey = "{$information_id}_{$language_id}_{$store_id}";
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information', 'i')
            ->leftJoin(DB_PREFIX . 'information_description', 'id', 'i.id = id.information_id')
            ->leftJoin(DB_PREFIX . 'information_to_store', 'i2s', 'i.id = i2s.information_id')
            ->where("i.id = ?", [$information_id])
            ->where("id.language_id = ?", [$language_id])
            ->where("i2s.store_id = ?", [$store_id])
            ->where("i.status = ?", [1])
            ->select('DISTINCT *');

        $results = $this->dao->executeQuery($query);
        $cache[$cacheKey] = $results ? $results[0] : [];
        return $cache[$cacheKey];
    }

    /**
     * Lista todas as páginas de informação ativas (Menu Footer/Sitemap)
     */
    public function getInformations(int $language_id, int $store_id): array {
        // Alpha Engine: Memoization Cache (Evita gargalo de N+1 Queries no SEO/Menus)
        static $cache = [];
        $cacheKey = "{$language_id}_{$store_id}";
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information', 'i')
            ->leftJoin(DB_PREFIX . 'information_description', 'id', 'i.id = id.information_id')
            ->leftJoin(DB_PREFIX . 'information_to_store', 'i2s', 'i.id = i2s.information_id')
            ->where("id.language_id = ?", [$language_id])
            ->where("i2s.store_id = ?", [$store_id])
            ->where("i.status = ?", [1])
            ->orderBy("i.sort_order", "ASC")
            ->orderBy("LCASE(id.title)", "ASC")
            // Alpha Engine Failsafe: Evita carregar HTML/Base64 gigantesco da coluna 'description' no footer
            ->select('DISTINCT i.id', 'i.id AS information_id', 'id.title', 'i.sort_order');

        $cache[$cacheKey] = $this->dao->executeQuery($query);
        return $cache[$cacheKey];
    }

    /**
     * Obtém o layout associado à página
     */
    public function getLayoutId(int $information_id, int $store_id): int {
        static $cache = [];
        $cacheKey = "{$information_id}_{$store_id}";
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information_to_layout')
            ->where("information_id = ?", [$information_id])
            ->where("store_id = ?", [$store_id])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        $cache[$cacheKey] = $results ? (int)$results[0]['layout_id'] : 0;
        return $cache[$cacheKey];
    }

    /**
     * Lista TODAS as páginas institucionais (ativas e inativas) para o painel administrativo.
     */
    public function getAllInformationsAdmin(int $language_id = 2, int $store_id = 1): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information', 'i')
            ->leftJoin(DB_PREFIX . 'information_description', 'id', 'i.id = id.information_id AND id.language_id = ' . (int)$language_id)
            ->leftJoin(DB_PREFIX . 'seo_url', 'su', "su.key = 'information_id' AND su.value = CAST(i.id AS CHAR) AND su.language_id = " . (int)$language_id . " AND su.store_id = " . (int)$store_id)
            ->orderBy("i.sort_order", "ASC")
            ->orderBy("i.id", "ASC")
            ->select('i.id', 'i.id AS information_id', 'i.status', 'i.sort_order', 'id.title', 'id.description', 'id.meta_title', 'id.meta_description', 'id.meta_keyword', 'su.keyword AS keyword');

        return $this->dao->executeQuery($query) ?: [];
    }

    /**
     * Obtém dados completos de uma página institucional para o admin (sem filtro de status).
     */
    public function getInformationForAdmin(int $information_id, int $language_id = 2, int $store_id = 1): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information', 'i')
            ->leftJoin(DB_PREFIX . 'information_description', 'id', 'i.id = id.information_id AND id.language_id = ' . (int)$language_id)
            ->leftJoin(DB_PREFIX . 'seo_url', 'su', "su.key = 'information_id' AND su.value = CAST(i.id AS CHAR) AND su.language_id = " . (int)$language_id . " AND su.store_id = " . (int)$store_id)
            ->where("i.id = ?", [$information_id])
            ->select('i.id', 'i.id AS information_id', 'i.status', 'i.sort_order', 'id.title', 'id.description', 'id.meta_title', 'id.meta_description', 'id.meta_keyword', 'su.keyword AS keyword');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Cria uma nova página institucional no banco de dados.
     */
    public function createInformation(array $data, int $language_id = 2, int $store_id = 1): int {
        $status = isset($data['status']) ? (int)$data['status'] : 1;
        $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;

        // 1. Insert into information
        $this->dao->executeRawSQL("INSERT INTO `" . DB_PREFIX . "information` (`status`, `sort_order`) VALUES (?, ?)", [
            $status,
            $sortOrder
        ]);
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $informationId = (int)$conn->lastInsertId();

        if ($informationId > 0) {
            // 2. Insert into information_description
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $metaTitle = trim($data['meta_title'] ?? '') ?: $title;
            $metaDescription = trim($data['meta_description'] ?? '');
            $metaKeyword = trim($data['meta_keyword'] ?? '');

            $this->dao->executeRawSQL(
                "INSERT INTO `" . DB_PREFIX . "information_description` (`information_id`, `language_id`, `title`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$informationId, $language_id, $title, $description, $metaTitle, $metaDescription, $metaKeyword]
            );

            // 3. Insert into information_to_store
            $this->dao->executeRawSQL(
                "INSERT IGNORE INTO `" . DB_PREFIX . "information_to_store` (`information_id`, `store_id`) VALUES (?, ?)",
                [$informationId, $store_id]
            );

            // 4. Save SEO Keyword / Slug if provided
            $keyword = trim($data['keyword'] ?? '');
            if (!empty($keyword)) {
                $this->saveSeoUrl($informationId, $keyword, $language_id, $store_id);
            }
        }

        return $informationId;
    }

    /**
     * Atualiza os dados de uma página institucional existente.
     */
    public function updateInformation(int $information_id, array $data, int $language_id = 2, int $store_id = 1): bool {
        $status = isset($data['status']) ? (int)$data['status'] : 1;
        $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;

        // 1. Update information
        $this->dao->executeRawSQL(
            "UPDATE `" . DB_PREFIX . "information` SET `status` = ?, `sort_order` = ? WHERE `id` = ?",
            [$status, $sortOrder, $information_id]
        );

        // 2. Update/Insert information_description
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $metaTitle = trim($data['meta_title'] ?? '') ?: $title;
        $metaDescription = trim($data['meta_description'] ?? '');
        $metaKeyword = trim($data['meta_keyword'] ?? '');

        $checkDesc = $this->dao->executeQuery(
            (new QueryBuilder())
                ->from(DB_PREFIX . 'information_description')
                ->where("`information_id` = ?", [$information_id])
                ->where("`language_id` = ?", [$language_id])
                ->select('COUNT(*) AS total')
        );

        $exists = !empty($checkDesc[0]['total']);

        if ($exists) {
            $this->dao->executeRawSQL(
                "UPDATE `" . DB_PREFIX . "information_description` SET `title` = ?, `description` = ?, `meta_title` = ?, `meta_description` = ?, `meta_keyword` = ? WHERE `information_id` = ? AND `language_id` = ?",
                [$title, $description, $metaTitle, $metaDescription, $metaKeyword, $information_id, $language_id]
            );
        } else {
            $this->dao->executeRawSQL(
                "INSERT INTO `" . DB_PREFIX . "information_description` (`information_id`, `language_id`, `title`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$information_id, $language_id, $title, $description, $metaTitle, $metaDescription, $metaKeyword]
            );
        }

        // 3. Ensure information_to_store entry exists
        $this->dao->executeRawSQL(
            "INSERT IGNORE INTO `" . DB_PREFIX . "information_to_store` (`information_id`, `store_id`) VALUES (?, ?)",
            [$information_id, $store_id]
        );

        // 4. Update SEO Keyword / Slug
        $keyword = trim($data['keyword'] ?? '');
        $this->saveSeoUrl($information_id, $keyword, $language_id, $store_id);

        return true;
    }

    /**
     * Remove uma página institucional personalizada (protege IDs de sistema 1, 2, 3 e 4).
     */
    public function deleteInformation(int $information_id): bool {
        // Proteção contra exclusão das páginas essenciais do sistema
        if (in_array($information_id, [1, 2, 3, 4], true)) {
            return false;
        }

        $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "information` WHERE `id` = ?", [$information_id]);
        $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "information_description` WHERE `information_id` = ?", [$information_id]);
        $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE `information_id` = ?", [$information_id]);
        $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE `information_id` = ?", [$information_id]);
        $this->dao->executeRawSQL("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'information_id' AND `value` = ?", [(string)$information_id]);

        return true;
    }

    /**
     * Salva ou atualiza a SEO URL (slug) de uma página institucional.
     */
    private function saveSeoUrl(int $information_id, string $keyword, int $language_id = 2, int $store_id = 1): void {
        $valStr = (string)$information_id;

        $checkSeo = $this->dao->executeQuery(
            (new QueryBuilder())
                ->from(DB_PREFIX . 'seo_url')
                ->where("`key` = ?", ['information_id'])
                ->where("`value` = ?", [$valStr])
                ->where("`store_id` = ?", [$store_id])
                ->where("`language_id` = ?", [$language_id])
                ->select('*')
        );

        if (empty($keyword)) {
            if (!empty($checkSeo)) {
                $this->dao->executeRawSQL(
                    "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'information_id' AND `value` = ? AND `store_id` = ? AND `language_id` = ?",
                    [$valStr, $store_id, $language_id]
                );
            }
            return;
        }

        $cleanKeyword = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $keyword), '-'));

        if (!empty($checkSeo)) {
            $this->dao->executeRawSQL(
                "UPDATE `" . DB_PREFIX . "seo_url` SET `keyword` = ? WHERE `key` = 'information_id' AND `value` = ? AND `store_id` = ? AND `language_id` = ?",
                [$cleanKeyword, $valStr, $store_id, $language_id]
            );
        } else {
            $this->dao->executeRawSQL(
                "INSERT INTO `" . DB_PREFIX . "seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES (?, ?, 'information_id', ?, ?)",
                [$store_id, $language_id, $valStr, $cleanKeyword]
            );
        }
    }
}