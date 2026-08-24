<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ProjectBoqMapper;
use Alpha\Mappers\EntityMappers\ProjectBoqItemMapper;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoq;
use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\DataAccessObject;

class ProjectBoqRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = ProjectBoqMapper::class;

    protected function getMapper(): ProjectBoqMapper
    {
        return $this->mapperFactory->get(ProjectBoqMapper::class);
    }

    protected function getItemMapper(): ProjectBoqItemMapper
    {
        return $this->mapperFactory->get(ProjectBoqItemMapper::class);
    }

    public function find(int $id): ?ProjectBoq
    {
        /** @var ProjectBoq|null $boq */
        $boq = $this->getMapper()->findById($id);
        if ($boq) {
            $boq->setItems($this->findItemsByBoqId($boq->getId()));
        }
        return $boq;
    }

    public function findByRfqId(int $rfqId): ?ProjectBoq
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_boq')
            ->where('rfq_id = ?', [$rfqId])
            ->orderBy('id', 'DESC')
            ->limit(1);

        $rows = (new DataAccessObject())->executeQuery($qb);
        if (empty($rows)) {
            return null;
        }

        /** @var ProjectBoq $boq */
        $boq = (new DataAccessObject())->hydrate(ProjectBoq::class, $rows[0]);
        $boq->setItems($this->findItemsByBoqId($boq->getId()));
        return $boq;
    }

    /**
     * @return ProjectBoqItem[]
     */
    public function findItemsByBoqId(int $boqId): array
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'project_boq_item')
            ->where('boq_id = ?', [$boqId])
            ->orderBy('id', 'ASC');

        $rows = (new DataAccessObject())->executeQuery($qb);
        $dao = new DataAccessObject();

        return array_map(fn($row) => $dao->hydrate(ProjectBoqItem::class, $row), $rows);
    }

    public function save(ProjectBoq $boq): ?int
    {
        return $this->getMapper()->save($boq);
    }

    public function saveItem(ProjectBoqItem $item): ?int
    {
        $id = $this->getItemMapper()->save($item);
        $this->recalculateTotal($item->getBoqId());
        return $id;
    }

    public function deleteItem(int $itemId): bool
    {
        /** @var ProjectBoqItem|null $item */
        $item = $this->getItemMapper()->findById($itemId);
        if (!$item) {
            return false;
        }

        $boqId = $item->getBoqId();
        $deleted = $this->getItemMapper()->delete($itemId);
        if ($deleted) {
            $this->recalculateTotal($boqId);
        }

        return $deleted;
    }

    public function recalculateTotal(int $boqId): float
    {
        $items = $this->findItemsByBoqId($boqId);
        $total = 0.0;
        foreach ($items as $item) {
            $total += $item->getTotalPrice();
        }

        /** @var ProjectBoq|null $boq */
        $boq = $this->getMapper()->findById($boqId);
        if ($boq) {
            $boq->setTotalEstimatedAmount($total);
            $this->getMapper()->update($boq);
        }

        return $total;
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
