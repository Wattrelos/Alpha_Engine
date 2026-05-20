<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Language;

/**
 * LanguageMapper - Gerencia a persistência e recuperação de idiomas.
 */
class LanguageMapper extends BaseMapper
{
    protected string $entityClass = Language::class;
    protected string $tableName = 'language';

    public function __construct($registry = null)
    {
        parent::__construct($registry);
    }

    /**
     * Recupera um idioma específico pelo ID.
     */
    public function getLanguage(int $language_id): ?Language
    {
        $results = $this->dao->readByIds(Language::class, [$language_id]);
        return $results ? $results[0] : null;
    }

    /**
     * Recupera um idioma pelo seu código ISO (ex: 'pt-br').
     */
    public function getLanguageByCode(string $code): ?Language
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'language')
            ->where("code = ?", [$code])
            ->select('id');

        $rows = $this->dao->executeQuery($builder);
        return $rows ? $this->getLanguage((int)$rows[0]['id']) : null;
    }

    /**
     * Recupera todos os idiomas ativos ordenados.
     * 
     * @return Language[]
     */
    public function getLanguages(): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'language')
            ->where("status = ?", [1])
            ->orderBy("sort_order", "ASC")
            ->select('id');

        $rows = $this->dao->executeQuery($builder);
        $ids = array_column($rows, 'id');

        return $this->dao->readByIds(Language::class, $ids);
    }
}