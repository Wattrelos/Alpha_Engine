<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ServiceProviderProfileMapper;
use Alpha\Model\Domain\Entities\Quotation\ServiceProviderProfile;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\DataAccessObject;

class ServiceProviderProfileRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = ServiceProviderProfileMapper::class;

    protected function getMapper(): ServiceProviderProfileMapper
    {
        return $this->mapperFactory->get(ServiceProviderProfileMapper::class);
    }

    public function find(int $id): ?ServiceProviderProfile
    {
        return $this->getMapper()->findById($id);
    }

    public function findByCustomerId(int $customerId): ?ServiceProviderProfile
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'service_provider_profile')
            ->where('customer_id = ?', [$customerId])
            ->limit(1);

        $rows = (new DataAccessObject())->executeQuery($qb);
        if (empty($rows)) {
            return null;
        }

        /** @var ServiceProviderProfile $profile */
        $profile = (new DataAccessObject())->hydrate(ServiceProviderProfile::class, $rows[0]);
        return $profile;
    }

    /**
     * Retorna todos os prestadores ativos.
     * @return ServiceProviderProfile[]
     */
    public function findActiveProviders(): array
    {
        $qb = (new QueryBuilder())
            ->from(DB_PREFIX . 'service_provider_profile')
            ->where('status = ?', [1]);

        $rows = (new DataAccessObject())->executeQuery($qb);
        $dao = new DataAccessObject();

        return array_map(fn($row) => $dao->hydrate(ServiceProviderProfile::class, $row), $rows);
    }

    public function save(ServiceProviderProfile $profile): ?int
    {
        return $this->getMapper()->save($profile);
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
