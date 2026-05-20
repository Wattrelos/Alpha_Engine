<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CategoryMapper;
use Alpha\Support\Collection;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * MenuRepository - Gerencia a lógica de navegação global (Menu de Categorias).
 * 
 * Melhoras Alpha Engine:
 * - Orquestração de Top-Level: Filtra e hidrata apenas categorias marcadas como 'top'.
 * - Hidratação em Cascata: Resolve subcategorias para menus dropdown de 2 níveis.
 */
class MenuRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Coleta categorias de topo e seus filhos para o menu principal.
     */
    public function getMenuData(): Collection
    {
        // Alpha Engine: Implementação de cache para evitar processamento recursivo custoso
        $cache_key = 'menu.categories.' . (int)$this->store_id . '.' . (int)$this->language_id;

        $cached_data = $this->cache->get($cache_key);

        if ($cached_data !== null) {
            return new Collection([
                'categories' => $cached_data
            ]);
        }

        /** @var CategoryMapper $categoryMapper */
        $categoryMapper = $this->mapperFactory->get(CategoryMapper::class);
        
        $categories = [];
        
        // 1. Busca categorias de nível 0 (raiz)
        // Alpha Engine: Agora utiliza o filtro 'top' nativo no Mapper para performance
        $results = $categoryMapper->getSubCategories(0, $this->language_id, $this->store_id, true);

        foreach ($results as $result) {
            $children_data = [];
            $children = $categoryMapper->getSubCategories((int)$result['id'], $this->language_id, $this->store_id);

            foreach ($children as $child) {
                $children_data[] = [
                    'name' => $child['name'],
                    'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $result['id'] . '_' . $child['id'])
                ];
            }

            $categories[] = [
                'name'     => $result['name'],
                'children' => $children_data,
                'column'   => 1, // Padrão OpenCart
                'href'     => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $result['id'])
            ];
        }

        // Alpha Engine: Armazena o resultado no cache
        $this->cache->set($cache_key, $categories);

        return new Collection([
            'categories' => $categories
        ]);
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}