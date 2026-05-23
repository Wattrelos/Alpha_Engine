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
        // Alpha Engine: Resolvendo dependências explicitamente via Registry
        $cache  = $this->registry->get('cache');
        $config = $this->registry->get('config');
        $url    = $this->registry->get('url');

        $store_id    = (int)$config->get('config_store_id');
        $language_id = (int)$config->get('config_language_id');

        // Alpha Engine: Implementação de cache para evitar processamento recursivo custoso
        $cache_key = 'menu.categories.' . $store_id . '.' . $language_id;

        $cached_data = $cache->get($cache_key);

        if ($cached_data !== null) {
            return new Collection([
                'categories' => $cached_data
            ]);
        }

        /** @var CategoryMapper $categoryMapper */
        $categoryMapper = $this->registry->get('alpha_mapper_factory')->get(CategoryMapper::class);
        
        $categories = [];
        
        // 1. Busca categorias de nível 0 (raiz)
        // Alpha Engine: Agora utiliza o filtro 'top' nativo no Mapper para performance
        $results = $categoryMapper->getSubCategories(0, $language_id, $store_id, true);

        foreach ($results as $result) {
            $children_data = [];
            $children = $categoryMapper->getSubCategories((int)$result['id'], $language_id, $store_id);

            foreach ($children as $child) {
                $children_data[] = [
                    'name' => $child['name'],
                    'href' => $url->link('product/category', 'language=' . $config->get('config_language') . '&path=' . $result['id'] . '_' . $child['id'])
                ];
            }

            $categories[] = [
                'name'     => $result['name'],
                'children' => $children_data,
                'column'   => 1, // Padrão OpenCart
                'href'     => $url->link('product/category', 'language=' . $config->get('config_language') . '&path=' . $result['id'])
            ];
        }

        // Alpha Engine: Armazena o resultado no cache
        $cache->set($cache_key, $categories);

        return new Collection([
            'categories' => $categories
        ]);
    }

    /**
     * Alpha Engine: Gera o HTML nativo do menu principal baseado nos dados em cache.
     * Elimina a necessidade de renderização Twig, garantindo resposta em sub-milissegundos.
     */
    public function getMenuHtml(): string
    {
        $data = $this->getMenuData();
        $categories = $data->get('categories') ?? [];

        if (empty($categories)) {
            return '<ul class="nav navbar-nav"><li><a href="#" class="nav-link">Nenhuma categoria encontrada</a></li></ul>';
        }

        $html = '<ul class="nav navbar-nav">';

        foreach ($categories as $category) {
            if (!empty($category['children'])) {
                $html .= '<li class="nav-item dropdown">';
                $html .= '<a href="' . $category['href'] . '" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '</a>';
                $html .= '<div class="dropdown-menu">';
                $html .= '<div class="dropdown-inner d-flex">';
                
                // Lógica de particionamento em colunas (padrão do admin do OpenCart)
                $columns = max(1, (int)($category['column'] ?? 1));
                $chunks = array_chunk($category['children'], ceil(count($category['children']) / $columns));
                
                foreach ($chunks as $chunk) {
                    $html .= '<ul class="list-unstyled p-2 m-0">';
                    foreach ($chunk as $child) {
                        $html .= '<li><a href="' . $child['href'] . '" class="dropdown-item">' . htmlspecialchars($child['name'], ENT_QUOTES, 'UTF-8') . '</a></li>';
                    }
                    $html .= '</ul>';
                }
                
                $html .= '</div>'; // .dropdown-inner
                $html .= '<a href="' . $category['href'] . '" class="dropdown-item see-all text-center border-top pt-2 mt-1"><strong>Ver tudo em ' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '</strong></a>';
                $html .= '</div>'; // .dropdown-menu
                $html .= '</li>';
            } else {
                $html .= '<li class="nav-item"><a href="' . $category['href'] . '" class="nav-link">' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '</a></li>';
            }
        }

        $html .= '</ul>';

        return $html;
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}