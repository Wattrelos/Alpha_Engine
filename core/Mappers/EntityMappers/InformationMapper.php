<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Information;

/**
 * InformationMapper - Gerencia a lógica de Páginas de Informação (Institucional)
 */
class InformationMapper extends BaseMapper {
    protected string $entityClass = Information::class;
    protected string $tableName = 'information';
    protected string $primaryKey = 'id';

    public function __construct() {
        parent::__construct();
    }

    public function findById(int $id): ?Information {
        return $this->getInformationEntity($id);
    }

    public function getInformationEntity(int $information_id): ?Information {
        $information = new Information();
        $information->setId($information_id);
        $results = $this->dao->read($information);
        return $results ? $results[0] : null;
    }

    public function getInformation(int $information_id, int $language_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information', 'i')
            ->leftJoin(DB_PREFIX . 'information_description', 'id', 'i.id = id.information_id')
            ->leftJoin(DB_PREFIX . 'information_to_store', 'i2s', 'i.id = i2s.information_id')
            ->where("i.id = ?", [$information_id])
            ->where("id.language_id = ?", [$language_id])
            ->where("i2s.store_id = ?", [$store_id])
            ->where("i.status = ?", [1])
            ->select('i.*', 'id.title', 'id.description', 'id.meta_title', 'id.meta_description', 'id.meta_keyword');

        $results = $this->dao->executeQuery($query);
        if (!$results) {
            return [];
        }

        $row = $results[0];
        // Alpha Engine: Normalização de ID para compatibilidade com controladores legados
        $row['information_id'] = (int)$row['id'];

        return $row;
    }

    /**
     * Lista todas as páginas de informação para a loja e idioma ativos.
     */
    public function getInformations(int $language_id, int $store_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information', 'i')
            ->leftJoin(DB_PREFIX . 'information_description', 'id', 'i.id = id.information_id')
            ->leftJoin(DB_PREFIX . 'information_to_store', 'i2s', 'i.id = i2s.information_id')
            ->where("id.language_id = ?", [$language_id])
            ->where("i2s.store_id = ?", [$store_id])
            ->where("i.status = ?", [1]);

        $query->orderBy("i.sort_order", "ASC")
            ->orderBy("LCASE(id.title)", "ASC")
            ->select('i.id', 'id.title', 'i.sort_order', 'i.status');

        $results = $this->dao->executeQuery($query);

        return array_map(function($row) {
            return ['information_id' => (int)$row['id']] + $row;
        }, $results);
    }

    public function getLayoutId(int $information_id, int $store_id): int {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'information_to_layout')
            ->where("information_id = ?", [$information_id])
            ->where("store_id = ?", [$store_id]);
        $results = $this->dao->executeQuery($query->select('*'));
        return $results ? (int)$results[0]['layout_id'] : 0;
    }
}