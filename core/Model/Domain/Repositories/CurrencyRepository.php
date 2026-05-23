<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CurrencyMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * CurrencyRepository - Autoridade de Domínio para Moedas.
 *
 * Centraliza o acesso aos dados de moedas, utilizando o CurrencyMapper
 * para persistência e garantindo que o Identity Map do DataAccessObject seja respeitado.
 */
class CurrencyRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca uma moeda pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = "currency.id.{$id}";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->mapperFactory->get(CurrencyMapper::class)->findById($id);

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Retorna todas as moedas ativas no sistema.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        $cacheKey = "currency.all";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entities = $this->mapperFactory->get(CurrencyMapper::class)->findAll();

        if ($this->cache) {
            $this->cache->set($cacheKey, $entities);
        }

        return $entities;
    }

    /**
     * Busca moedas baseado em critérios específicos.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return InterfaceEntity[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get(CurrencyMapper::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma única moeda baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $cacheKey = "currency.query." . md5(serialize($criteria));

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->mapperFactory->get(CurrencyMapper::class)->findOneBy($criteria);

        if ($entity && $this->cache) {
            $this->cache->set($cacheKey, $entity);
        }

        return $entity;
    }

    /**
     * Alpha Engine: Prepara o DTO/Coleção para exibição do seletor de moedas no header.
     */
    public function getCurrencyDisplayData(): Collection
    {
        $this->loadLanguage('common/currency');

        $data = [
            'text_currency' => $this->language->get('text_currency'),
            'action'        => $this->url->link('common/currency.save', 'language=' . $this->config->get('config_language')),
            'code'          => $this->session->data['currency'] ?? $this->config->get('config_currency'),
            'currencies'    => []
        ];

        // Busca otimizada usando a engine de banco ao invés de hidratar todas as entidades
        $currencies = $this->mapperFactory->get(CurrencyMapper::class)->findAllActive();

        foreach ($currencies as $result) {
            $data['currencies'][] = [
                'title'        => $result['title'],
                'code'         => $result['code'],
                'symbol_left'  => $result['symbol_left'],
                'symbol_right' => $result['symbol_right']
            ];
        }

        return new Collection($data);
    }

    /**
     * Alpha Engine: Reconstrói a URL para redirecionamento após a troca de moeda.
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
     * Valida se um código de moeda existe e está ativo no sistema.
     */
    public function isValid(string $code): bool
    {
        $currencies = $this->mapperFactory->get(CurrencyMapper::class)->findAllActive();
        foreach ($currencies as $currency) {
            if ($currency['code'] === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Define o contexto da moeda na sessão e limpa dependências sensíveis (como Frete).
     */
    public function setCurrencyContext(string $code): void
    {
        $this->session->data['currency'] = $code;

        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
    }

    /**
     * Salva o cookie de moeda e gera a URL de redirecionamento seguro.
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

        setcookie('currency', $code, $option);

        if ($redirect && (str_starts_with($redirect, $this->config->get('config_url')) || str_starts_with($redirect, $this->config->get('config_ssl')))) {
            $parsed = parse_url(str_replace('&amp;', '&', $redirect));

            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $query_args);
                $route = $query_args['route'] ?? $this->config->get('action_default');
                unset($query_args['route'], $query_args['_route_']);
                
                $url_params = http_build_query($query_args);
                return $this->url->link($route, ($url_params ? $url_params : ''));
            }
        }

        return $this->url->link($this->config->get('action_default'));
    }
}