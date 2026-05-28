<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Upload;

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
        $sql = "SELECT * FROM " . $this->getFullTableName() . " WHERE `code` = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();

        return $row ? $this->dao->hydrate($this->entityClass, $row) : null;
    }
}
