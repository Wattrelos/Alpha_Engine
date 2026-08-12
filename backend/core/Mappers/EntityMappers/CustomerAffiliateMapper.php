<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Customer\CustomerAffiliate;

class CustomerAffiliateMapper extends BaseMapper
{
    protected function getFullTableName(): string
    {
        return DB_PREFIX . 'customer_affiliate';
    }

    public function findByCustomerId(int $customerId): ?CustomerAffiliate
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where('customer_id = ?', [$customerId])
            ->limit(1);

        $result = $this->dao->executeQuery($query);

        if (empty($result)) {
            return null;
        }

        return $this->hydrate($result[0]);
    }


    /**
     * Método auxiliar para hidratar array diretamente para entidade.
     */
    private function hydrate(array $row): CustomerAffiliate
    {
        $entity = new CustomerAffiliate();

        if (isset($row['customer_id'])) {
            $entity->setCustomerId($row['customer_id']);
        }
        if (isset($row['company'])) {
            $entity->setCompany($row['company']);
        }
        if (isset($row['website'])) {
            $entity->setWebsite($row['website']);
        }
        if (isset($row['tracking'])) {
            $entity->setTracking($row['tracking']);
        }
        if (isset($row['payment_method'])) {
            $entity->setPaymentMethod($row['payment_method']);
        }
        if (isset($row['cheque'])) {
            $entity->setCheque($row['cheque']);
        }
        if (isset($row['paypal'])) {
            $entity->setPaypal($row['paypal']);
        }
        if (isset($row['bank_name'])) {
            $entity->setBankName($row['bank_name']);
        }
        if (isset($row['bank_branch_number'])) {
            $entity->setBankBranchNumber($row['bank_branch_number']);
        }
        if (isset($row['bank_swift_code'])) {
            $entity->setBankSwiftCode($row['bank_swift_code']);
        }
        if (isset($row['bank_account_name'])) {
            $entity->setBankAccountName($row['bank_account_name']);
        }
        if (isset($row['bank_account_number'])) {
            $entity->setBankAccountNumber($row['bank_account_number']);
        }
        if (isset($row['commission'])) {
            $entity->setCommission($row['commission']);
        }
        if (isset($row['tax'])) {
            $entity->setTax($row['tax']);
        }
        if (isset($row['status'])) {
            $entity->setStatus($row['status']);
        }
        if (isset($row['date_added'])) {
            $entity->setDateAdded($row['date_added']);
        }

        return $entity;
    }
}
