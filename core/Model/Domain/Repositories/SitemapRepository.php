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
        $language_param = 'language=' . $this->config->get('config_language');
        $customer_token = isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : '';
        $full_token = $language_param . $customer_token;

        // 1. Categorias (Árvore de 3 níveis)
        // Alpha Engine: Implementação de cache para evitar processamento recursivo custoso
        $cache_key = 'sitemap.categories.' . (int)$this->store_id . '.' . (int)$this->language_id;
        
        $categories = $this->cache->get($cache_key);

        if (!$categories) {
            $categories = $this->getCategoryTree();
            $this->cache->set($cache_key, $categories);
        }
        $data['categories'] = $categories;

        // 2. Links de Conta e Vendas
        $data['special']  = $this->url->link('product/special', $language_param);
        $data['account']  = $this->url->link('account/account', $full_token, true);
        $data['edit']     = $this->url->link('account/edit', $full_token, true);
        $data['password'] = $this->url->link('account/password', $full_token, true);
        $data['address']  = $this->url->link('account/address', $full_token, true);
        $data['history']  = $this->url->link('account/order', $full_token, true);
        $data['download'] = $this->url->link('account/download', $full_token, true);
        $data['cart']     = $this->url->link('checkout/cart', $language_param);
        $data['checkout'] = $this->url->link('checkout/checkout', $language_param, true);
        $data['search']   = $this->url->link('product/search', $language_param);
        $data['contact']  = $this->url->link('information/contact', $language_param);

        // 3. Páginas de Informação
        // Alpha Engine: Cache de páginas institucionais
        $cache_key = 'sitemap.informations.' . (int)$this->store_id . '.' . (int)$this->language_id;

        $informations = $this->cache->get($cache_key);

        if (!$informations) {
            $informations = $this->getInformations();
            $this->cache->set($cache_key, $informations);
        }
        $data['informations'] = $informations;

        return new Collection($data);
    }

    /**
     * Resolve a árvore de categorias para o sitemap.
     */
    private function getCategoryTree(): array
    {
        /** @var CategoryMapper $categoryMapper */
        $categoryMapper = $this->mapperFactory->get(CategoryMapper::class);
        
        $categories = [];
        $language_param = 'language=' . $this->config->get('config_language');

        $categories_1 = $categoryMapper->getSubCategories(0, $this->language_id, $this->store_id);

        foreach ($categories_1 as $category_1) {
            $level_2_data = [];
            $categories_2 = $categoryMapper->getSubCategories((int)$category_1['id'], $this->language_id, $this->store_id);

            foreach ($categories_2 as $category_2) {
                $level_3_data = [];
                $categories_3 = $categoryMapper->getSubCategories((int)$category_2['id'], $this->language_id, $this->store_id);

                foreach ($categories_3 as $category_3) {
                    $level_3_data[] = [
                        'name' => $category_3['name'],
                        'href' => $this->url->link('product/category', $language_param . '&path=' . $category_1['id'] . '_' . $category_2['id'] . '_' . $category_3['id'])
                    ];
                }

                $level_2_data[] = [
                    'name'     => $category_2['name'],
                    'children' => $level_3_data,
                    'href'     => $this->url->link('product/category', $language_param . '&path=' . $category_1['id'] . '_' . $category_2['id'])
                ];
            }

            $categories[] = [
                'name'     => $category_1['name'],
                'children' => $level_2_data,
                'href'     => $this->url->link('product/category', $language_param . '&path=' . $category_1['id'])
            ];
        }

        return $categories;
    }

    /**
     * Busca a lista de páginas institucionais ativas.
     */
    private function getInformations(): array
    {
        /** @var InformationMapper $informationMapper */
        $informationMapper = $this->mapperFactory->get(InformationMapper::class);
        
        $informations = [];
        $language_param = 'language=' . $this->config->get('config_language');

        foreach ($informationMapper->getInformations($this->language_id, $this->store_id) as $result) {
            $informations[] = [
                'title' => $result['title'],
                'href'  => $this->url->link('information/information', $language_param . '&information_id=' . $result['id'])
            ];
        }

        return $informations;
    }

    // Métodos obrigatórios da Interface BaseRepository
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}