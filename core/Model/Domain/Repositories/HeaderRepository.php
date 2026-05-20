<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\Extension;
use Alpha\Mappers\EntityMappers\ExtensionMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * HeaderRepository - Orquestra a infraestrutura do cabeçalho global.
 * 
 * Melhoras Alpha Engine:
 * - SEO Management: Centraliza a configuração de Meta Tags no Document.
 * - Dynamic Analytics: Resolve módulos de analytics sem o Loader legado.
 * - Asset Management: Gerencia links de CSS e JS básicos do sistema.
 */
class HeaderRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Coleta dados básicos, meta tags e extensões de analytics.
     */
    public function getHeaderData(): Collection
    {
        // 1. Configuração de metadados do documento (Lógica de Domínio)
        $this->document->setTitle($this->config->get('config_meta_title'));
        $this->document->setDescription($this->config->get('config_meta_description'));
        $this->document->setKeywords($this->config->get('config_meta_keyword'));

        // 2. Agregação de dados para o template
        $data = [
            'title'       => $this->document->getTitle(),
            'description' => $this->document->getDescription(),
            'keywords'    => $this->document->getKeywords(),
            'links'       => $this->document->getLinks(),
            'styles'      => $this->document->getStyles(),
            'scripts'     => $this->document->getScripts('header'),
            'lang'        => $this->language->get('code'),
            'direction'   => $this->language->get('direction'),
            'name'        => $this->config->get('config_name'),
            'base'        => $this->config->get('config_url'),
            'home'        => $this->url->link('common/home', 'language=' . $this->config->get('config_language')),
            'logo'        => $this->config->get('config_logo') ? HTTP_SERVER . 'image/' . $this->config->get('config_logo') : '',
            'icon'        => $this->config->get('config_icon') ? HTTP_SERVER . 'image/' . $this->config->get('config_icon') : ''
        ];

        // 3. Busca de extensões de analytics ativas via Mapper
        /** @var ExtensionMapper $extensionMapper */
        $extensionMapper = $this->mapperFactory->get(ExtensionMapper::class);
        $data['analytics_extensions'] = $extensionMapper->getExtensionsByType('analytics');

        return new Collection($data);
    }

    /**
     * Alpha Engine: Resolve e executa os módulos de analytics de forma Loader-Free.
     */
    public function getAnalyticsModules(Collection $headerData): array
    {
        $analytics = [];
        $extensions = $headerData->get('analytics_extensions', []);

        /** @var Extension $extension */
        foreach ($extensions as $extension) {
            $code = $extension->getCode();
            if ($this->config->get('analytics_' . $code . '_status')) {
                // Alpha Engine: Mapeamento PSR-4 para instanciamento direto (Evita Loader legado)
                $namespace = 'Opencart\Catalog\Controller\Extension\\' . 
                             str_replace('_', '', ucwords($extension->getExtension(), '_')) . 
                             '\Analytics\\' . 
                             str_replace('_', '', ucwords($code, '_'));

                if (class_exists($namespace)) {
                    $result = (new $namespace($this->registry))->index($this->config->get('analytics_' . $code . '_status'));
                    if ($result) {
                        $analytics[] = $result;
                    }
                }
            }
        }

        return $analytics;
    }

    /**
     * Retorna os assets básicos (CSS/JS) injetados pelo repositório.
     */
    public function getBasicAssets(): array
    {
        return [
            'bootstrap'  => 'catalog/view/stylesheet/bootstrap.css',
            'icons'      => 'catalog/view/stylesheet/fonts/fontawesome/css/all.min.css',
            'stylesheet' => 'catalog/view/stylesheet/stylesheet.css',
            'jquery'     => 'catalog/view/javascript/jquery/jquery-3.7.1.min.js'
        ];
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}