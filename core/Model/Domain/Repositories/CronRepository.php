<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CronMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * CronRepository - Autoridade de Domínio para Tarefas Agendadas (Cron).
 */
class CronRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): CronMapper
    {
        return $this->mapperFactory->get(CronMapper::class);
    }

    public function editCron(int $cron_id): void
    {
        $this->getMapper()->editCron($cron_id);
    }

    public function editStatus(int $cron_id, bool $status): void
    {
        $this->getMapper()->editStatus($cron_id, $status);
    }

    public function getCron(int $cron_id): array
    {
        return $this->getMapper()->getCron($cron_id);
    }

    public function getCronByCode(string $code): array
    {
        return $this->getMapper()->getCronByCode($code);
    }

    public function getCrons(): array
    {
        return $this->getMapper()->getCrons();
    }

    public function getTotalCrons(): int
    {
        return $this->getMapper()->getTotalCrons();
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}