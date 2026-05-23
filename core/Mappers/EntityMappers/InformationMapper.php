<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar a lógica de Páginas de Informação (Institucional)
 */
class InformationMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
     * Obtém uma página de informação específica
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
     * Lista todas as páginas de informação ativas
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
}