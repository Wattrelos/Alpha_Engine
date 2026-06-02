<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Upload;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para a entidade Upload.
 * Isola a camada de banco de dados (tabela upload).
 */
class UploadMapper extends BaseMapper
{
    protected string $tableName = 'upload';
    protected string $entityClass = Upload::class;

    /**
     * Busca um upload pelo seu código hash.
     * 
     * @param string $code
     * @return Upload|null
     */
    public function findByCode(string $code): ?Upload
    {
        $query = (new QueryBuilder())
            ->select('*')
            ->from($this->getFullTableName())
            ->where('`code` = ?', [$code])
            ->limit(1);

        $results = $this->dao->executeQuery($query);
        return $results ? $this->dao->hydrate($this->entityClass, $results[0]) : null;
    }
}
