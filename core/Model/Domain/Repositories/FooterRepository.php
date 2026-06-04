<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * FooterRepository - Orquestra a infraestrutura do rodapé global.
 *
 * - Navigation Aggregator: Consolida links de Informação, Atendimento e Conta.
 * - Multi-Store Logic: Filtra páginas de informação específicas para a loja atual.
 */
class FooterRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Consolida todos os links e dados para o rodapé.
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
            'account'      => $this->url->link('account', $language_param, true),
            'order'        => $this->url->link('account/orders', $language_param, true),
            'wishlist'     => $this->url->link('account/wishlist', $language_param, true),
            'newsletter'   => $this->url->link('account/newsletter', $language_param, true),
            'store_name'   => $this->config->get('config_name'),
            'current_year' => date('Y'),
            'scripts'      => $this->document->getScripts('footer'),
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

        foreach ($informationMapper->getInformations($this->language_id, $this->store_id) as $result) {
            $informations[] = [
                'title' => $result['title'],
                'href'  => $this->url->link('information/information', $language_param . '&information_id=' . $result['id'])
            ];
        }

        return $informations;
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
