<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\LanguageMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Mappers\CollectionToArrayConverter;
use Alpha\Support\Collection;

/**
 * LanguageRepository - Autoridade de Domínio para Idiomas.
 * 
 * Centraliza o acesso aos dados de idiomas, utilizando o LanguageMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class LanguageRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Define o Mapper principal.
     */
    protected function getMapper(): LanguageMapper
    {
        return $this->mapperFactory->get(LanguageMapper::class);
    }

    /**
     * Busca um idioma pelo seu ID único.
     * 
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = "language.id.{$id}";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->getMapper()->findById($id);

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Retorna todos os idiomas ativos no sistema.
     * 
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        $cacheKey = "language.all";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entities = $this->getMapper()->findAll();

        if ($this->cache) {
            $this->cache->set($cacheKey, $entities);
        }

        return $entities;
    }

    /**
     * Busca idiomas baseado em critérios específicos.
     * 
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca um único idioma baseado em critérios.
     * 
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $cacheKey = "language.query." . md5(serialize($criteria));

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $results = $this->getMapper()->search($criteria);
        $entity = $results[0] ?? null;

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Busca um idioma pelo seu código ISO (ex: 'pt-br').
     * Essencial para a inicialização do contexto de idioma da loja.
     */
    public function getByCode(string $code): ?InterfaceEntity
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * Alpha Engine: Retorna todos os idiomas ativos em formato de array, 
     * indexados pelo código (ex: 'pt-br').
     * Método crucial para substituir model_localisation_language->getLanguages()
     */
    public function getLanguages(): array
    {
        $cacheKey = "language.array.all";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $languages = [];
        foreach ($this->findAll() as $entity) {
            $arrayData = CollectionToArrayConverter::convertEntity($entity);
            $code = $arrayData['code'] ?? (string)$entity->getId();
            $languages[$code] = $arrayData;
        }

        if ($this->cache) {
            $this->cache->set($cacheKey, $languages);
        }

        return $languages;
    }

    /**
     * Alpha Engine: Carrega um pacote de traduções (substituindo de vez o Loader legado).
     * 
     * @param string $route Rota do arquivo de tradução (ex: 'common/header')
     * @return array
     */
    public function loadTranslation(string $route): array
    {
        return $this->registry->get('language')->load($route) ?: [];
    }

    /**
     * Alpha Engine: Prepara o DTO/Coleção para a exibição do seletor de idiomas no header.
     */
    public function getLanguageDisplayData(): Collection
    {
        $this->loadLanguage('common/language');
        
        $data = [
            'text_language' => $this->language->get('text_language'),
            'action'        => $this->url->link('common/language.save', 'language=' . $this->config->get('config_language')),
            'code'          => $this->config->get('config_language'),
            'languages'     => []
        ];

        foreach ($this->getLanguages() as $result) {
            if ($result['status']) {
                $data['languages'][] = [
                    'name'  => $result['name'],
                    'code'  => $result['code'],
                    'image' => $result['image'] ?? ''
                ];
            }
        }

        return new Collection($data);
    }

    /**
     * Alpha Engine: Reconstrói a URL para redirecionamento após a troca de idioma.
     */
    public function getRedirectUrl(array $get): string
    {
        $route = $get['route'] ?? $this->config->get('action_default');
        
        unset($get['route'], $get['_route_'], $get['language']);

        $url = '';
        foreach ($get as $key => $value) {
            $url .= '&' . $key . '=' . $value;
        }
        
        return $this->url->link($route, ltrim($url, '&'));
    }

    /**
     * Valida se um código de idioma existe no sistema.
     */
    public function isValid(string $code): bool
    {
        return array_key_exists($code, $this->getLanguages());
    }

    /**
     * Limpa dados da sessão afetados pela troca de idioma.
     */
    public function clearLanguageContext(): void
    {
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['payment_methods']);
    }

    /**
     * Salva o cookie de idioma e gera a URL de redirecionamento seguro.
     */
    public function processSaveRedirect(string $redirect, string $code): string
    {
        $option = [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'domain'   => $this->config->get('session_domain'),
            'secure'   => $this->request->server['HTTPS'],
            'httponly' => false,
            'SameSite' => $this->config->get('config_session_samesite')
        ];

        setcookie('language', $code, $option);

        if ($redirect && (str_starts_with($redirect, $this->config->get('config_url')) || str_starts_with($redirect, $this->config->get('config_ssl')))) {
            $parsed = parse_url(str_replace('&amp;', '&', $redirect));
            
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $query_args);
                $route = $query_args['route'] ?? $this->config->get('action_default');
                unset($query_args['route'], $query_args['_route_'], $query_args['language']);
                
                $url_params = http_build_query($query_args);
                return $this->url->link($route, 'language=' . $code . ($url_params ? '&' . $url_params : ''));
            }
        }

        return $this->url->link($this->config->get('action_default'), 'language=' . $code);
    }
}