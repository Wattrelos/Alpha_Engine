<?php

namespace Alpha\Controller;

use Alpha\Mappers\MapperFactory;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\View\ViewRenderer;
use Alpha\Support\Cache\CacheStrategyInterface;
use Alpha\Support\Language;
use Alpha\Support\Customer;
use Alpha\Support\Presenters\ImagePresenter;
use Psr\Container\ContainerInterface;

/**
 * BaseController — Master Controller da Alpha Engine (100% Standalone).
 *
 * Raiz da hierarquia de controllers da Alpha Engine.
 * Não herda de nenhum engine legado — todas as dependências chegam
 * exclusivamente via AppContainer (PSR-11).
 *
 * Responsabilidades:
 *  - Resolver o contexto da loja (storeId, languageId) a partir das configSettings.
 *  - Expor atalhos tipados para Repositórios, Mappers, ViewRenderer e Cache.
 *  - Prover helpers reutilizáveis (loadLanguageData, remember, renderPosition, getTemplate).
 */
abstract class BaseController
{
    protected int $storeId;
    protected int $languageId;
    protected ViewRenderer $viewRenderer;
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        // Resolve configSettings (array) do AppContainer
        $settings = $container->has('configSettings') ? $container->get('configSettings') : [];
        $this->storeId    = (int)($settings['config_store_id'] ?? 1);
        $this->languageId = (int)($settings['config_language_id'] ?? 2);

        // Inicia o renderizador de view blindado contra WSOD
        $this->viewRenderer = new ViewRenderer($container);

        // Garante que o LayoutRepository esteja disponível no container
        if (!$container->has('layout')) {
            $container->bind('layout', $this->getRepository(\Alpha\Model\Domain\Repositories\LayoutRepository::class));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Fábrica de Domínio
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna uma instância de Repositório Alpha pelo FQCN.
     *
     * @param class-string $class
     */
    protected function getRepository(string $class): mixed
    {
        return RepositoryFactory::getInstance()->get($class);
    }

    /**
     * Retorna uma instância de Mapper Alpha pelo FQCN.
     *
     * @param class-string $class
     */
    protected function getMapper(string $class): mixed
    {
        return MapperFactory::getInstance()->get($class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers de Apresentação
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna o ImagePresenter inicializado com a URL e o diretório de imagens da loja.
     */
    protected function getImagePresenter(): ImagePresenter
    {
        $settings = $this->container->has('configSettings') ? $this->container->get('configSettings') : [];
        $url      = $settings['config_url'] ?? HTTP_SERVER;
        $imageDir = defined('DIR_IMAGE') ? DIR_IMAGE : (DIR_ROOT . 'image/');

        return new ImagePresenter($url, $imageDir);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internacionalização (Alpha\Support\Language)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Carrega as traduções de uma rota e injeta todas as chaves no array de dados da view.
     *
     * Substitui completamente o padrão legado `$this->load->language($route)`.
     * A Alpha Engine usa Alpha\Support\Language, registrado no container como 'language'.
     *
     * @param string $route    Rota do arquivo de idioma (ex: 'catalog/product')
     * @param array  $data     Array de dados da view (passado por referência)
     */
    protected function loadLanguageData(string $route, array &$data): void
    {
        /** @var Language|null $lang */
        $lang = $this->container->has('language') ? $this->container->get('language') : null;

        if (!$lang instanceof Language) {
            return;
        }

        // load() carrega o namespace e retorna o array flat de traduções
        $translations = $lang->load($route);

        foreach ($translations as $key => $value) {
            $data[$key] = $value;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Configuração
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Carrega um arquivo de configuração via ConfigurationRepository.
     *
     * Substitui o padrão legado `$this->load->config($filename)`.
     */
    protected function loadConfig(string $filename): void
    {
        $this->getRepository(\Alpha\Model\Domain\Repositories\ConfigurationRepository::class)->loadFile($filename);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cache (CacheStrategyInterface)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cache de dados genéricos com namespacing automático por loja e idioma.
     *
     * Substitui o padrão legado que combinava $this->registry + $this->alpha_cache + $this->cache.
     * Resolve o driver de cache exclusivamente via container PSR-11.
     * Se nenhum cache estiver registrado, executa o generator diretamente (sem cache).
     *
     * @param string   $cacheKey  Chave única (inclua contexto de moeda/grupo se envolver preços)
     * @param callable $generator Closure que gera os dados caso o cache esteja frio
     * @param int      $ttl       Tempo de vida em segundos (padrão: 3600)
     * @return mixed
     */
    protected function remember(string $cacheKey, callable $generator, int $ttl = 3600): mixed
    {
        $namespacedKey = sprintf('%s.s%d.l%d', $cacheKey, $this->storeId, $this->languageId);

        /** @var CacheStrategyInterface|null $cache */
        $cache = $this->container->has(CacheStrategyInterface::class)
            ? $this->container->get(CacheStrategyInterface::class)
            : null;

        if ($cache !== null) {
            $cached = $cache->get($namespacedKey);
            if ($cached !== null && $cached !== false) {
                return $cached;
            }
        }

        $output = $generator();

        if ($cache !== null) {
            $cache->set($namespacedKey, $output, $ttl);
        }

        return $output;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Layout
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Renderiza os módulos atrelados a uma posição do Layout.
     *
     * Na Alpha Engine, os módulos são gerenciados pelo LayoutRepository.
     * Não existe mais $this->load->model() ou $this->load->controller() — o engine legado foi removido.
     * Esta implementação usa exclusivamente o LayoutRepository e o ViewRenderer nativos.
     *
     * @param  string $route    Rota atual (ex: 'product/product')
     * @param  string $position Posição do layout (ex: 'column_left', 'content_top')
     * @return array  Array de strings HTML dos módulos renderizados
     */
    protected function renderPosition(string $route, string $position): array
    {
        /** @var \Alpha\Support\Customer|null $customer */
        $customer = $this->container->has('customer') ? $this->container->get('customer') : null;

        $settings        = $this->container->has('configSettings') ? $this->container->get('configSettings') : [];
        $currencyCode    = $_SESSION['currency'] ?? ($settings['config_currency'] ?? 'BRL');
        $customerGroupId = ($customer instanceof Customer && $customer->isLogged())
            ? $customer->getGroupId()
            : (int)($settings['config_customer_group_id'] ?? 1);

        $cacheKey = sprintf(
            'layout_pos_v2.%s.%s.c%s.cg%d',
            str_replace(['/', '.'], '_', $route),
            $position,
            $currencyCode,
            $customerGroupId
        );

        return $this->remember($cacheKey, function () use ($route, $position) {
            /** @var \Alpha\Model\Domain\Repositories\LayoutRepository $layoutRepo */
            $layoutRepo    = $this->container->has('layout') ? $this->container->get('layout') : null;
            $layoutModules = $layoutRepo ? $layoutRepo->getModulesForRoute($route) : [];
            $modules       = $layoutModules[$position] ?? [];

            // Alpha Engine: Módulos de layout são renderizados via ViewRenderer (Twig).
            // Não existe mais $this->load->controller() — cada módulo deve ser uma view Twig.
            $renderedModules = [];
            foreach ($modules as $module) {
                if (is_string($module) && $module !== '') {
                    $renderedModules[] = $module;
                }
            }

            return $renderedModules;
        }, 3600);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // View
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Renderiza uma view Twig e retorna o HTML resultante como string.
     * Envelopa a chamada no ViewRenderer blindado contra WSOD.
     *
     * @param string $route Caminho da view (ex: 'pages/product/product.html.twig')
     * @param array  $data  Variáveis a injetar no template
     */
    protected function getTemplate(string $route, array $data = []): string
    {
        return $this->viewRenderer->render($route, $data);
    }
}
