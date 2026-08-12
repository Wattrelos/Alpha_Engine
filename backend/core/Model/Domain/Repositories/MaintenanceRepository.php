<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\DTOs\MaintenanceDataDTO;
use Alpha\Model\Domain\InterfaceEntity;

class MaintenanceRepository extends AbstractRepository
{
    public function getMaintenanceData(): MaintenanceDataDTO
    {
        $this->loadLanguage('common/maintenance');

        $data = [
            'heading_title' => $this->language->get('heading_title'),
            'breadcrumbs'   => [],
            'message'       => $this->language->get('text_message')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_maintenance'),
            'href' => $this->url->link('common/maintenance', 'language=' . $this->config->get('config_language'))
        ];

        return new MaintenanceDataDTO($data);
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}
