<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProjectRfqMapper;
use Alpha\Model\Domain\Entities\Quotation\ProjectRfq;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\DataAccessObject;

class ProjectRfqRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = ProjectRfqMapper::class;

    protected function getMapper(): ProjectRfqMapper
    {
        return $this->mapperFactory->get(ProjectRfqMapper::class);
    }

    public function find(int $id): ?ProjectRfq
    {
        return $this->getMapper()->findById($id);
    }

    /**
     * @return ProjectRfq[]
     */
    public function findByCustomerId(int $customerId): array
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_rfq')
            ->where('customer_id = ?', [$customerId])
            ->orderBy('id', 'DESC');

        $rows = (new DataAccessObject())->executeQuery($qb);
        $dao = new DataAccessObject();

        return array_map(fn($row) => $dao->hydrate(ProjectRfq::class, $row), $rows);
    }

    /**
     * @return ProjectRfq[]
     */
    public function findOpenProjects(): array
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_rfq')
            ->where("status IN ('open', 'bidding')")
            ->orderBy('id', 'DESC');

        $rows = (new DataAccessObject())->executeQuery($qb);
        $dao = new DataAccessObject();

        return array_map(fn($row) => $dao->hydrate(ProjectRfq::class, $row), $rows);
    }

    public function save(ProjectRfq $rfq): ?int
    {
        return $this->getMapper()->save($rfq);
    }

    public function updateStatus(int $rfqId, string $status, ?int $selectedProviderId = null): bool
    {
        $rfq = $this->find($rfqId);
        if (!$rfq) {
            return false;
        }

        $rfq->setStatus($status);
        if ($selectedProviderId !== null) {
            $rfq->setSelectedProviderId($selectedProviderId);
        }

        return $this->getMapper()->update($rfq);
    }

    // BaseRepositoryInterface bindings
    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}
