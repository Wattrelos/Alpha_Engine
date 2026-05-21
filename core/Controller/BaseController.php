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
     * Carrega as chaves de tradução de um arquivo de linguagem
     * e faz o merge automático no array de dados do Controller.
     */
    protected function loadLanguageData(string $route, array &$data = []): void
    {
        // Alpha Engine: Utiliza o objeto Language nativo para carregar as traduções
        // eliminando completamente a dependência do Loader legado.
        $languageData = $this->language->load($route);
        $data = array_merge($data, is_array($languageData) ? $languageData : []);
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
        $modules = [];
        
        // TODO: Orquestrar a resolução do Layout ID e buscar os módulos vinculados
        // através do LayoutRepository. Como o foco atual é a página de Carrinho/Checkout,
        // retornaremos um array vazio temporariamente para desobstruir a renderização global.
        
        return $modules;
    }

    /**
     * Método utilitário para renderizar a View.
     * Injeta automaticamente os componentes globais (Header, Footer, Colunas) 
     * caso eles já não tenham sido definidos no array de dados.
     */
    protected function render(string $route, array $data = []): void
    {
        $data['column_left']    = $data['column_left'] ?? $this->load->controller('common/column_left');
        $data['column_right']   = $data['column_right'] ?? $this->load->controller('common/column_right');
        $data['content_top']    = $data['content_top'] ?? $this->load->controller('common/content_top');
        $data['content_bottom'] = $data['content_bottom'] ?? $this->load->controller('common/content_bottom');
        $data['footer']         = $data['footer'] ?? $this->load->controller('common/footer');
        $data['header']         = $data['header'] ?? $this->load->controller('common/header');

        $this->response->setOutput($this->load->view($route, $data));
    }
}