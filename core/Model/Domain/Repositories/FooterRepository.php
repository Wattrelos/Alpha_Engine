<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Mappers\EntityMappers\ExtensionMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * FooterRepository - Orquestra a infraestrutura do rodapé global.
 * 
 * Melhoras Alpha Engine:
 * - Navigation Aggregator: Consolida links de Informação, Atendimento e Conta.
 * - Multi-Store Logic: Filtra páginas de informação específicas para a loja atual.
 * - Extension Management: Resolve módulos de rodapé de forma Loader-Free.
 */
class FooterRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Alpha Engine: Consolida todos os links e dados para o rodapé.
     */
    public function getFooterData(): Collection
    {
        $language_param = 'language=' . $this->config->get('config_language');
        
        $data = [
            'informations' => $this->getInformationLinks(),
            'contact'      => $this->url->link('information/contact', $language_param),
            'return'       => $this->url->link('account/return.add', $language_param),
            'sitemap'      => $this->url->link('information/sitemap', $language_param),
            'manufacturer' => $this->url->link('product/manufacturer', $language_param),
            'voucher'      => $this->url->link('checkout/voucher', $language_param),
            'affiliate'    => $this->url->link('account/affiliate', $language_param),
            'special'      => $this->url->link('product/special', $language_param),
            'account'      => $this->url->link('account/account', $language_param, true),
            'order'        => $this->url->link('account/order', $language_param, true),
            'wishlist'     => $this->url->link('account/wishlist', $language_param, true),
            'newsletter'   => $this->url->link('account/newsletter', $language_param, true),
            'powered'      => sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time())),
            'scripts'      => $this->document->getScripts('footer'),
            'extensions'   => $this->getFooterModules()
        ];

        return new Collection($data);
    }

    /**
     * Busca links de páginas de informação marcadas para exibição no rodapé.
     */
    private function getInformationLinks(): array
    {
        /** @var InformationMapper $informationMapper */
        $informationMapper = $this->mapperFactory->get(InformationMapper::class);
        
        $informations = [];
        $language_param = 'language=' . $this->config->get('config_language');

        // Alpha Engine: Buscamos as informações ativas (filtro 'bottom' removido pois a coluna não existe no banco)
        foreach ($informationMapper->getInformations($this->language_id, $this->store_id) as $result) {
            $informations[] = [
                'title' => $result['title'],
                'href'  => $this->url->link('information/information', $language_param . '&information_id=' . $result['id'])
            ];
        }

        return $informations;
    }

    /**
     * Alpha Engine: Resolve e renderiza módulos de rodapé via PSR-4 (Loader-Free).
     */
    private function getFooterModules(): array
    {
        $modules = [];
        $extensions = $this->getFooterExtensions();

        foreach ($extensions as $extension) {
            $code = $extension->getCode();
            
            if ($this->config->get('footer_' . $code . '_status')) {
                // Alpha Engine: Mapeamento de Namespace para instanciamento direto
                $namespace = 'Opencart\Catalog\Controller\Extension\\' . 
                             str_replace('_', '', ucwords($extension->getExtension(), '_')) . 
                             '\Footer\\' . 
                             str_replace('_', '', ucwords($code, '_'));

                if (class_exists($namespace)) {
                    // Executamos o controlador sem passar pelo sistema de proxy do Loader
                    $result = (new $namespace($this->registry))->index();
                    
                    if ($result) {
                        $modules[] = $result;
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * Alpha Engine: Busca extensões específicas do rodapé via Mapper.
     */
    public function getFooterExtensions(): array
    {
        /** @var ExtensionMapper $extensionMapper */
        $extensionMapper = $this->mapperFactory->get(ExtensionMapper::class);
        return $extensionMapper->getExtensionsByType('footer');
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}