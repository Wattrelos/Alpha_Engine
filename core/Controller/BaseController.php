<?php

namespace Alpha\Controller;

use Opencart\System\Engine\Controller;
use Opencart\System\Engine\Registry;
use Alpha\Mappers\MapperFactory;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

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

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);

        // Auto-resolução do contexto ativo da loja
        $this->storeId = (int)$this->config->get('config_store_id');
        $this->languageId = (int)$this->config->get('config_language_id');

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
     * Alpha Engine: Renderiza os módulos atrelados a uma posição do Layout.
     * 
     * @param string $position (ex: 'column_left', 'content_top')
     * @return array
     */
    protected function renderPosition(string $position): array
    {
        $route = (string)($this->request->get['route'] ?? $this->config->get('action_default'));
        $layoutModules = $this->registry->get('layout')->getModulesForRoute($route);
        
        return $layoutModules[$position] ?? [];
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
        $data['footer']         = $data['footer'] ?? (new \Opencart\Catalog\Controller\Common\Footer($this->registry))->index();
        $data['header']         = $data['header'] ?? (new \Opencart\Catalog\Controller\Common\Header($this->registry))->index();

        $this->response->setOutput($this->load->view($route, $data));
    }
}