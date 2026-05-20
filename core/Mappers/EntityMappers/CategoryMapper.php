<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Category;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * CategoryMapper - Inteligência de persistência para categorias.
 */
class CategoryMapper extends BaseMapper
{
    protected string $entityClass = Category::class;
    protected string $tableName = 'category';

    /**
     * Alpha Engine: Recupera subcategorias com suporte a filtro de menu superior (top).
     * 
     * @param int $parentId ID da categoria pai (0 para raiz).
     * @param int $languageId Contexto de idioma.
     * @param int $storeId Contexto de loja.
     * @param bool|null $top Se true, filtra apenas categorias marcadas para o menu superior.
     * @return array
     */
    public function getSubCategories(int $parentId, int $languageId, int $storeId, ?bool $top = null): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName(), 'c')
            ->leftJoin(DB_PREFIX . 'category_description', 'cd', 'c.id = cd.category_id')
            ->leftJoin(DB_PREFIX . 'category_to_store', 'c2s', 'c.id = c2s.category_id')
            ->where('c.parent_id = ?', [$parentId])
            ->where('cd.language_id = ?', [$languageId])
            ->where('c2s.store_id = ?', [$storeId])
            ->where('c.status = ?', [1])
            ->select('c.id, cd.name');

        // Alpha Engine: Filtro nativo de banco para economia de I/O
        if ($top !== null) {
            $query->where('c.top = ?', [(int)$top]);
        }

        $query->orderBy('c.sort_order', 'ASC')
              ->orderBy('cd.name', 'ASC');

        return $this->dao->executeQuery($query);
    }

    // Outros métodos do Mapper seriam implementados aqui...
}