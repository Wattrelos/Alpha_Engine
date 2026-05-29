<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerAffiliateMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Entities\CustomerAffiliate;
use Alpha\Support\EntityHydrator;

class CustomerAffiliateRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = CustomerAffiliateMapper::class;

    public function find(int $id): ?InterfaceEntity
    {
        return $this->mapperFactory->get($this->mapperClass)->findById($id);
    }

    public function findAll(): array
    {
        return $this->mapperFactory->get($this->mapperClass)->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperFactory->get($this->mapperClass)->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->mapperFactory->get($this->mapperClass)->search($criteria);
        return $results[0] ?? null;
    }

    /**
     * Encontra um afiliado pelo seu código de rastreamento (utilizado no Checkout)
     */
    public function findByTracking(string $tracking): ?CustomerAffiliate
    {
        return $this->findOneBy(['tracking' => $tracking]);
    }

    public function save(CustomerAffiliate $affiliate): void
    {
        $this->mapperFactory->get($this->mapperClass)->save($affiliate);
    }

    public function processSave(int $customerId, array $data): void
    {
        /** @var CustomerAffiliate|null $affiliate */
        $affiliate = $this->find($customerId);
        
        if (!$affiliate) {
            $affiliate = new CustomerAffiliate();
            $affiliate->setCustomerId($customerId);
            $affiliate->setTracking(oc_token(36));
            
            $config = $this->registry->get('config');
            $affiliate->setCommission((float)$config->get('config_affiliate_commission'));
            
            $approvalRequired = $config->get('config_affiliate_approval');
            $affiliate->setStatus(!$approvalRequired);
        }

        EntityHydrator::fillEntity($affiliate, $data);
        
        $this->save($affiliate);
    }

    /**
     * Legacy Bridge: Retorna representação de Array para compatibilidade de Proxy.
     */
    public function getAffiliate(int $customerId): ?array
    {
        /** @var CustomerAffiliate|null $entity */
        $entity = $this->find($customerId);
        if (!$entity) {
            return null;
        }

        return [
            'customer_id'         => $entity->getCustomerId(),
            'company'             => $entity->getCompany(),
            'website'             => $entity->getWebsite(),
            'tracking'            => $entity->getTracking(),
            'commission'          => $entity->getCommission(),
            'tax'                 => $entity->getTax(),
            'payment_method'      => $entity->getPaymentMethod(),
            'cheque'              => $entity->getCheque(),
            'paypal'              => $entity->getPaypal(),
            'bank_name'           => $entity->getBankName(),
            'bank_branch_number'  => $entity->getBankBranchNumber(),
            'bank_swift_code'     => $entity->getBankSwiftCode(),
            'bank_account_name'   => $entity->getBankAccountName(),
            'bank_account_number' => $entity->getBankAccountNumber(),
            'custom_field'        => $entity->getCustomFieldArray(),
            'status'              => $entity->isStatus(),
            'date_added'          => $entity->getDateAdded(),
        ];
    }

    /**
     * Legacy Bridge: Aliases de Mutações
     */
    public function addAffiliate(int $customerId, array $data): void
    {
        $this->processSave($customerId, $data);
    }

    public function editAffiliate(int $customerId, array $data): void
    {
        $this->processSave($customerId, $data);
    }
}
