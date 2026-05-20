<?php

namespace Alpha\Mappers;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para gerenciar configurações de módulos
 */
class ModuleMapper {
    private DataAccessObject $dao;

    public function __construct() {
        $this->dao = new DataAccessObject();
    }

    /**
    * Obter Módulo
    * Obtém o registro do módulo no banco de dados.
    * @param int $module_id chave primária do registro do módulo
    * @return array<mixed> registros de módulos que possuem o ID do módulo
     */
    public function getModule(int $module_id): array {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'module', 'm')
            ->where("m.id = ?", [$module_id])
            ->select('*');

        return $this->dao->executeQuery($query);
    }
}