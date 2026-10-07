<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProjectBidMapper;
use Alpha\Model\Domain\Entities\Quotation\ProjectBid;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\DataAccessObject;

class ProjectBidRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = ProjectBidMapper::class;

    protected function getMapper(): ProjectBidMapper
    {
        return $this->mapperFactory->get(ProjectBidMapper::class);
    }

    public function find(int $id): ?ProjectBid
    {
        return $this->getMapper()->findById($id);
    }

    /**
     * @return ProjectBid[]
     */
    public function findByRfqId(int $rfqId): array
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_bid')
            ->where('rfq_id = ?', [$rfqId])
            ->orderBy('labor_price', 'ASC');

        $rows = (new DataAccessObject())->executeQuery($qb);
        $dao = new DataAccessObject();

        return array_map(fn($row) => $dao->hydrate(ProjectBid::class, $row), $rows);
    }

    /**
     * @return ProjectBid[]
     */
    public function findByProviderId(int $providerId): array
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_bid')
            ->where('provider_id = ?', [$providerId])
            ->orderBy('id', 'DESC');

        $rows = (new DataAccessObject())->executeQuery($qb);
        $dao = new DataAccessObject();

        return array_map(fn($row) => $dao->hydrate(ProjectBid::class, $row), $rows);
    }

    public function findByRfqAndProvider(int $rfqId, int $providerId): ?ProjectBid
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_bid')
            ->where('rfq_id = ? AND provider_id = ?', [$rfqId, $providerId])
            ->limit(1);

        $rows = (new DataAccessObject())->executeQuery($qb);
        if (empty($rows)) {
            return null;
        }

        /** @var ProjectBid $bid */
        $bid = (new DataAccessObject())->hydrate(ProjectBid::class, $rows[0]);
        return $bid;
    }

    public function countByRfqId(int $rfqId): int
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_bid')
            ->where('rfq_id = ?', [$rfqId]);

        $rows = (new DataAccessObject())->executeQuery($qb);
        return count($rows);
    }

    public function save(ProjectBid $bid): ?int
    {
        return $this->getMapper()->save($bid);
    }

    public function acceptBid(int $bidId): bool
    {
        $bid = $this->find($bidId);
        if (!$bid) {
            return false;
        }

        $bid->setStatus('accepted');
        $this->getMapper()->update($bid);

        // Rejeita automaticamente as outras propostas do mesmo RFQ
        $allBids = $this->findByRfqId($bid->getRfqId());
        foreach ($allBids as $otherBid) {
            if ($otherBid->getId() !== $bidId && $otherBid->getStatus() === 'submitted') {
                $otherBid->setStatus('rejected');
                $this->getMapper()->update($otherBid);
            }
        }

        return true;
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
