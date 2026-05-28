<?php

namespace Alpha\Controller;

use Opencart\System\Engine\Controller;
use Opencart\System\Engine\Registry;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\View\ViewRenderer;

/**
 * BaseController
 * 
 * Master Controller da Alpha Engine.
 * Atua como classe abstrata pai para todos os controladores migrados.
 * Remove o boilerplate legado, automatizando a injeção de dependências
 * para a camada de domínio (Repositórios e Mappers).
 */
abstract class BaseController extends Controller
{
    protected int $storeId;
    protected int $languageId;
    protected ViewRenderer $viewRenderer;

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);

        // Auto-resolução do contexto ativo da loja
        $this->storeId = (int)$this->config->get('config_store_id');
        $this->languageId = (int)$this->config->get('config_language_id');

        // Alpha Engine: Inicia o renderizador de view blindado contra WSOD
        $this->viewRenderer = new ViewRenderer($registry);

        // Alpha Engine: Injeção do LayoutRepository global (prometido para o Header)
        if (!$this->registry->has('layout')) {
            if (class_exists(\Alpha\Model\Domain\Repositories\LayoutRepository::class)) {
                $this->registry->set('layout', $this->getRepository(\Alpha\Model\Domain\Repositories\LayoutRepository::class));
            } else {
                // Mock fallback para desobstruir o layout e não quebrar a página enquanto a classe não existe
                $this->registry->set('layout', new class($this->registry->get('document')) {
                    private $document;
                    public function __construct($document) { $this->document = $document; }
                    public function getModulesByRoute(string $route, string $type): array { return []; }
                    public function getStylesByRoute(string $route, $document = null): array { return $document ? $document->getStyles() : ($this->document ? $this->document->getStyles() : []); }
                    public function getScriptsByRoute(string $route, string $position = 'header', $document = null): array { return $document ? $document->getScripts($position) : ($this->document ? $this->document->getScripts($position) : []); }
                    public function getLinksByRoute(string $route, $document = null): array { return $document ? $document->getLinks() : ($this->document ? $this->document->getLinks() : []); }
                });
            }
        }
    }

    /**
     * Retorna a instância injetada de um Repositório Alpha.
     * 
     * @param class-string $class O FQCN do repositório (ex: ProductRepository::class)
     * @return mixed
     */
    protected function getRepository(string $class): mixed
    {
        $factory = $this->registry->has('repositoryFactory') 
            ? $this->registry->get('repositoryFactory') 
            : RepositoryFactory::getInstance();
            
        return $factory->get($class);
    }

    /**
     * Retorna a instância injetada de um Mapper Alpha.
     * 
     * @param class-string $class O FQCN do mapper (ex: ProductMapper::class)
     * @return mixed
     */
    protected function getMapper(string $class): mixed
    {
        $factory = $this->registry->has('mapperFactory') 
            ? $this->registry->get('mapperFactory') 
            : MapperFactory::getInstance();

        return $factory->get($class);
    }

    /**
     * Retorna a instância do ImagePresenter.
     * 
     * @return \Alpha\Support\Presenters\ImagePresenter
     */
    protected function getImagePresenter(): \Alpha\Support\Presenters\ImagePresenter
    {
        return new \Alpha\Support\Presenters\ImagePresenter($this->registry);
    }

    /**
     * Atalho para enviar uma resposta JSON limpa (usado fortemente em requisições AJAX).
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        $this->response->addHeader('Content-Type: application/json');
        http_response_code($statusCode);
        $this->response->setOutput(json_encode($data));
    }

     /**
     * Alpha Engine: Carrega o arquivo de tradução da rota e injeta automaticamente
     * todas as variáveis (ex: text_home, text_login) no array fornecido.
     * 
     * O uso do '&' (referência) garante que o array original seja modificado,
     * eliminando a necessidade de repetição de código nos controladores.
     */
    protected function loadLanguageData(string $route, array &$data): void
    {
        $this->load->language($route);
        
        foreach ($this->language->all() as $key => $value) {
            $data[$key] = $value;
        }
    }

    /**
     * Alpha Engine: Substituto direto para $this->load->config()
     */
    protected function loadConfig(string $filename): void
    {
        $this->getRepository(\Alpha\Model\Domain\Repositories\ConfigurationRepository::class)->loadFile($filename);
    }

    /**
     * Alpha Engine: Renderizador de Fragmentos com Cache PSR-16.
     * Envelopa a renderização de blocos pesados (ex: Menus, Rodapés, Árvores)
     * para retornar o HTML diretamente da RAM/Redis, pulando processamento MVC.
     *
     * @param string $cacheKey Chave única do bloco (ex: 'menu_categorias')
     * @param callable $generator Função anônima que gera e retorna o HTML
     * @param int $ttl Tempo de vida em segundos (padrão: 3600 = 1 hora)
     * @return string O HTML processado
     */
    protected function renderFragment(string $cacheKey, callable $generator, int $ttl = 3600): string
    {
        // Obtém o driver de Cache Alpha (se definido) ou cai pro nativo do OpenCart
        $cache = $this->registry->has('alpha_cache') ? $this->registry->get('alpha_cache') : $this->registry->get('cache');

        // Garante o isolamento do cache por loja e idioma para não misturar moedas/traduções
        $namespacedKey = sprintf('%s.s%d.l%d', $cacheKey, $this->storeId, $this->languageId);

        $output = $cache->get($namespacedKey);

        // Alpha Engine: Validação is_string previne Warning se o driver de cache nativo retornar arrays legados
        if (is_string($output) && $output !== '') {
            return $output;
        }

        $output = (string)$generator();
        $cache->set($namespacedKey, $output, $ttl);

        return $output;
    }

    /**
     * Alpha Engine: Cache de Dados Genéricos (PSR-16 wrapper).
     * Diferente do renderFragment (exclusivo para HTML), este método permite o 
     * cacheamento de Arrays iteráveis e DTOs, preservando as tipagens originais.
     *
     * @param string $cacheKey Chave única (adicione contexto de moeda/grupo se envolver preços)
     * @param callable $generator Função que gera os dados caso o cache não exista
     * @param int $ttl Tempo de vida em segundos (padrão: 3600)
     * @return mixed Os dados cacheados ou recém-gerados
     */
    protected function remember(string $cacheKey, callable $generator, int $ttl = 3600): mixed
    {
        $cache = $this->registry->has('alpha_cache') ? $this->registry->get('alpha_cache') : $this->registry->get('cache');
        $namespacedKey = sprintf('%s.s%d.l%d', $cacheKey, $this->storeId, $this->languageId);

        $output = $cache->get($namespacedKey);

        if ($output !== false && $output !== null) {
            return $output;
        }

        $output = $generator();
        $cache->set($namespacedKey, $output, $ttl);

        return $output;
    }

    /**
     * Alpha Engine: Renderiza os módulos atrelados a uma posição do Layout.
     * 
     * @param string $position (ex: 'column_left', 'content_top')
     * @return array
     */
    protected function renderPosition(string $position): array
    {
        $route = (string)($this->request->get['route'] ?? $this->config->get('action_default'));

        // Alpha Engine: Criação de Contexto Financeiro Isolado para Cache Seguro dos Módulos
        $currencyCode = $this->session->data['currency'] ?? $this->config->get('config_currency');
        $customerGroupId = $this->customer->isLogged() ? $this->customer->getGroupId() : $this->config->get('config_customer_group_id');
        
        // Alpha Engine: Chave Única (Posição + Rota + Moeda + Grupo)
        // Alterado prefixo para 'layout_pos_v2' para invalidar e expurgar caches antigos envenenados com arrays cru
        $cacheKey = sprintf('layout_pos_v2.%s.%s.c%s.cg%d', str_replace(['/', '.'], '_', $route), $position, $currencyCode, $customerGroupId);

        return $this->remember($cacheKey, function() use ($route, $position) {
            $layoutModules = $this->registry->get('layout')->getModulesForRoute($route);
            
            $modules = $layoutModules[$position] ?? [];
            $renderedModules = [];

            foreach ($modules as $module) {
                // Se o módulo for um array cru vindo do banco de dados (ex: tabela oc_layout_module)
                if (is_array($module) && !empty($module['code'])) {
                    $part = explode('.', $module['code']);
                    $output = '';
                    
                    // Módulos com ID atrelado (ex: banner.28) requerem carga das configurações
                    if (isset($part[1])) {
                        $this->load->model('setting/module');
                        $setting_info = $this->model_setting_module->getModule((int)$part[1]);
                        
                        if ($setting_info && $setting_info['status']) {
                            $output = $this->load->controller($part[0], $setting_info);
                        }
                    } else {
                        // Módulos estáticos simples sem ID
                        $output = $this->load->controller($part[0]);
                    }

                    // Se a view do módulo compilou um HTML válido, nós o adicionamos ao array final
                    if (is_string($output) && $output !== '') {
                        $renderedModules[] = $output;
                    }
                }
            }
            
            return $renderedModules;
        }, 3600); // Memoriza os módulos compilados por 1 Hora!
    }

    /**
     * Alpha Engine: Renderiza uma view e retorna seu HTML como string.
     * Envelopa a chamada no ViewRenderer protegido contra WSOD.
     * 
     * @param string $route
     * @param array $data
     * @return string
     */
    protected function getTemplate(string $route, array $data = []): string
    {
        return $this->viewRenderer->render($route, $data);
    }

    /**
     * Método utilitário para renderizar a View.
     * Injeta automaticamente os componentes globais (Header, Footer, Colunas) 
     * caso eles já não tenham sido definidos no array de dados.
     */
    protected function render(string $route, array $data = []): void
    {
        $data['column_left']    = $data['column_left'] ?? (new \Opencart\Catalog\Controller\Common\ColumnLeft($this->registry))->index();
        $data['column_right']   = $data['column_right'] ?? (new \Opencart\Catalog\Controller\Common\ColumnRight($this->registry))->index();
        $data['content_top']    = $data['content_top'] ?? (new \Opencart\Catalog\Controller\Common\ContentTop($this->registry))->index();
        $data['content_bottom'] = $data['content_bottom'] ?? (new \Opencart\Catalog\Controller\Common\ContentBottom($this->registry))->index();
        
        // Alpha Engine: Aplica o Cache de Fragmento nativamente no Footer (Conteúdo Estático e Pesado)
        $data['footer']         = $data['footer'] ?? $this->renderFragment('layout_footer', function() {
            return (new \Opencart\Catalog\Controller\Common\Footer($this->registry))->index();
        }, 86400); // Cache de 24 horas para o rodapé

        $data['header']         = $data['header'] ?? (new \Opencart\Catalog\Controller\Common\Header($this->registry))->index();

        $this->response->setOutput($this->viewRenderer->render($route, $data));
    }
}