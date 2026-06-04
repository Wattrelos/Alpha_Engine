<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * HeaderRepository - Orquestra a infraestrutura do cabeçalho global.
 *
 * - SEO Management: Centraliza a configuração de Meta Tags no Document.
 */
class HeaderRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Coleta dados básicos e meta tags para o cabeçalho.
     */
    public function getHeaderData(): Collection
    {
        $data = [
            'title'       => $this->config->get('config_meta_title') ?: $this->config->get('config_name'),
            'description' => $this->config->get('config_meta_description'),
            'keywords'    => $this->config->get('config_meta_keyword'),
            'name'        => $this->config->get('config_name'),
            'logo'        => $this->config->get('config_logo') ?: '',
            'icon'        => $this->config->get('config_icon') ?: '',
        ];

        return new Collection($data);
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
