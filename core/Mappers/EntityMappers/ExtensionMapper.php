<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Extension;

class ExtensionMapper extends BaseMapper
{
    protected string $tableName = 'extension';
    protected string $entityClass = Extension::class;

    /**
     * Alpha Engine: Retorna extensões ativas por tipo hidratadas como Entidades
     */
    public function getExtensionsByType(string $type): array
    {
        return $this->search(['type' => $type]);
    }
}