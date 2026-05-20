<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Module;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * ModuleMapper - Gerencia a persistência e recuperação de configurações de módulos (Alpha Engine).
 * 
 * Melhoras Alpha Engine:
 * - Hidratação via Entidade: Converte registros do banco em objetos Module tipados.
 * - Performance: Utiliza o Identity Map do DAO para evitar queries repetitivas de configurações.
 * - Segurança: Proteção contra SQL Injection via QueryBuilder e Prepared Statements.
 */
class ModuleMapper extends BaseMapper 
{
    protected string $entityClass = Module::class;
    protected string $tableName = 'module';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Recupera a entidade Module hidratada pelo seu ID.
     */
    public function getModule(int $moduleId): ?Module 
    {
        $module = new Module();
        $module->setId($moduleId);
        
        $results = $this->dao->read($module);
        
        return $results ? $results[0] : null;
    }

    /**
     * Obtém os dados de um módulo em formato de array (Compatibilidade Legada).
     */
    public function getModuleData(int $moduleId): array 
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'module', 'm')
            ->where("m.id = ?", [$moduleId])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    /**
     * Salva ou atualiza os dados de um módulo utilizando o motor Alpha.
     */
    public function save(InterfaceEntity $entity): ?int
    {
        return ($entity->getId() > 0) ? $this->dao->update($entity) : $this->dao->create($entity);
    }
}