<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Model\Domain\Entities\CustomerAffiliate;

class CustomerAffiliateMapper extends BaseMapper
{
    protected string $tableName = 'customer_affiliate';
    protected string $entityClass = CustomerAffiliate::class;

    public function findById(int $id): ?CustomerAffiliate
    {
        $results = $this->search(['customer_id' => $id]);
        return $results[0] ?? null;
    }

    public function save(CustomerAffiliate $entity): void
    {
        $existing = $this->findById($entity->getCustomerId());

        if ($existing) {
            $this->dao->updateForClass($this->tableName, $entity, ['customer_id' => $entity->getCustomerId()]);
        } else {
            if (!$entity->getDateAdded()) {
                $entity->setDateAdded(date('Y-m-d H:i:s'));
            }
            $this->dao->insertForClass($this->tableName, $entity);
        }
    }
}