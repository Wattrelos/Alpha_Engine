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

    /**
     * Alpha Engine: Retorna uma extensão específica por tipo e código.
     */
    public function getExtensionByCode(string $type, string $code): ?Extension
    {
        $results = $this->search(['type' => $type, 'code' => $code]);
        return $results[0] ?? null;
    }

    /**
     * Alpha Engine: Retorna a lista de nomes das extensões instaladas.
     */
    public function getDistinctExtensions(): array
    {
        $sql = "SELECT DISTINCT(`extension`) FROM " . $this->getFullTableName() . " ORDER BY `extension` ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}