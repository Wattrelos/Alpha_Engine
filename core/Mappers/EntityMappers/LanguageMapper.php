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

    /**
     * Recupera um idioma específico pelo ID.
     */
    public function getLanguage(int $language_id): ?Language
    {
        return $this->findById($language_id);
    }

    /**
     * Recupera um idioma pelo seu código ISO (ex: 'pt-br').
     */
    public function getLanguageByCode(string $code): ?Language
    {
        $results = $this->search(['code' => $code]);
        return $results[0] ?? null;
    }

    /**
     * Recupera todos os idiomas ativos ordenados.
     * 
     * @return Language[]
     */
    public function getLanguages(): array
    {
        return $this->search(['status' => 1], ['sort_order' => 'ASC']);
    }
}