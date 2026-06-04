<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Translation;

/**
 * TranslationMapper - Acesso a dados de traduções personalizadas no banco.
 */
class TranslationMapper extends BaseMapper
{
    protected string $entityClass = Translation::class;
    protected string $tableName = 'translation';

    /**
     * Retorna as traduções customizadas de uma rota específica em formato de array bruto
     * para máxima performance e compatibilidade com o motordo código legado.
     */
    public function getRouteTranslations(string $route, int $storeId, int $languageId): array
    {
        $builder = (new QueryBuilder())
            ->from(DB_PREFIX . 'translation')
            ->where('store_id = ?', [$storeId])
            ->where('language_id = ?', [$languageId])
            ->where('route = ?', [$route]);

        return $this->dao->executeQuery($builder) ?: [];
    }
}
