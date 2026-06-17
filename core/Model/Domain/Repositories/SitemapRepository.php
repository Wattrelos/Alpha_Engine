<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CategoryMapper;
use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * SitemapRepository - Gerencia a lógica de agregação de links para o mapa do site.
 * 
 * Melhoras Alpha Engine:
 * - Orquestração de Taxonomia: Gera a árvore de categorias de forma performática.
 * - Centralização de Rotas: Isola a construção de URLs institucionais e de conta.
 */
class SitemapRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Consolida todos os dados necessários para renderizar o sitemap.
     * 
     * @return Collection
     */
    public function getSitemapData(): Collection
    {
        $data = [];
        $store_id = (int)$this->config->get('config_store_id');
        $language_id = (int)$this->config->get('config_language_id');
        $language_param = 'language=' . $this->config->get('config_language');
        $customer_token = isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : '';
        $full_token = $language_param . $customer_token;

        // 1. Categorias (Árvore de 3 níveis)
        // Alpha Engine: Implementação de cache para evitar processamento recursivo custoso
        $cache_key = 'sitemap.categories.' . $store_id . '.' . $language_id;

        $categories = $this->cache ? $this->cache->get($cache_key) : null;

        if (!$categories) {
            $categories = $this->getCategoryTree($store_id, $language_id);
            if ($this->cache) {
                $this->cache->set($cache_key, $categories);
            }
        }
        $data['categories'] = $categories;

        // 2. Links de Conta e Vendas
        $data['special']  = $this->url->link('product/special', $language_param);
        $data['account']  = $this->url->link('account', $full_token, true);
        $data['edit']     = $this->url->link('account/edit', $full_token, true);
        $data['password'] = $this->url->link('account/password', $full_token, true);
        $data['address']  = $this->url->link('account/address', $full_token, true);
        $data['history']  = $this->url->link('account/orders', $full_token, true);
        $data['cart']     = $this->url->link('checkout/cart', $language_param);
        $data['checkout'] = $this->url->link('checkout/checkout', $language_param, true);
        $data['search']   = $this->url->link('product/search', $language_param);
        $data['contact']  = $this->url->link('information/contact', $language_param);

        // 3. Páginas de Informação
        // Alpha Engine: Cache de páginas institucionais
        $cache_key = 'sitemap.informations.' . $store_id . '.' . $language_id;

        $informations = $this->cache ? $this->cache->get($cache_key) : null;

        if (!$informations) {
            $informations = $this->getInformations($store_id, $language_id);
            if ($this->cache) {
                $this->cache->set($cache_key, $informations);
            }
        }
        $data['informations'] = $informations;

        return new Collection($data);
    }

    /**
     * Resolve a árvore de categorias para o sitemap.
     */
    private function getCategoryTree(int $store_id, int $language_id): array
    {
        /** @var CategoryMapper $categoryMapper */
        $categoryMapper = $this->mapperFactory->get(CategoryMapper::class);

        $categories = [];
        $language_param = 'language=' . $this->config->get('config_language');

        // Alpha Engine: Evitando o problema de N+1 Queries.
        // Carrega todas as categorias de uma vez e monta a árvore em memória.
        $allCategories = $categoryMapper->getAllCategories($language_id, $store_id);

        $categoryMap = [];
        foreach ($allCategories as $cat) {
            $parentId = $cat['parent_id'] !== null ? (int)$cat['parent_id'] : '';
            $categoryMap[$parentId][] = $cat;
        }

        // Verifica se existem categorias raiz (parent_id = null ou vazio)
        if (isset($categoryMap[''])) {
            foreach ($categoryMap[''] as $category_1) {
                $level_2_data = [];

                if (isset($categoryMap[(int)$category_1['id']])) {
                    foreach ($categoryMap[(int)$category_1['id']] as $category_2) {
                        $level_3_data = [];

                        if (isset($categoryMap[(int)$category_2['id']])) {
                            foreach ($categoryMap[(int)$category_2['id']] as $category_3) {
                                $level_3_data[] = [
                                    'id'   => (int)$category_3['id'],
                                    'name' => $category_3['name'],
                                    'href' => $this->url->link('product/category', $language_param . '&path=' . $category_3['id'])
                                ];
                            }
                        }

                        $level_2_data[] = [
                            'id'       => (int)$category_2['id'],
                            'name'     => $category_2['name'],
                            'children' => $level_3_data,
                            'href'     => $this->url->link('product/category', $language_param . '&path=' . $category_2['id'])
                        ];
                    }
                }

                $categories[] = [
                    'id'       => (int)$category_1['id'],
                    'name'     => $category_1['name'],
                    'children' => $level_2_data,
                    'href'     => $this->url->link('product/category', $language_param . '&path=' . $category_1['id'])
                ];
            }
        }

        return $categories;
    }

    /**
     * Busca a lista de páginas institucionais ativas.
     */
    private function getInformations(int $store_id, int $language_id): array
    {
        /** @var InformationMapper $informationMapper */
        $informationMapper = $this->mapperFactory->get(InformationMapper::class);

        $informations = [];
        $language_param = 'language=' . $this->config->get('config_language');

        foreach ($informationMapper->getInformations($language_id, $store_id) as $result) {
            $informations[] = [
                'id'    => (int)$result['id'],
                'title' => $result['title'],
                'href'  => $this->url->link('information/information', $language_param . '&information_id=' . $result['id'])
            ];
        }

        return $informations;
    }

    // Métodos obrigatórios da Interface BaseRepository
    public function find(int $id): ?InterfaceEntity
    {
        return null;
    }
    public function findAll(): array
    {
        return [];
    }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return null;
    }
}
