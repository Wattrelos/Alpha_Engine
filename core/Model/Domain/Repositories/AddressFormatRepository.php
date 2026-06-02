<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\AddressFormatMapper;
use Alpha\Model\Domain\InterfaceEntity;

class AddressFormatRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): AddressFormatMapper
    {
        return $this->mapperFactory->get(AddressFormatMapper::class);
    }

    public function find(int $id): ?InterfaceEntity
    {
        $cacheKey = "address_format.{$id}";
        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entity = $this->getMapper()->findById($id);

        if ($entity && $this->cache !== null) {
            $this->cache->set($cacheKey, $entity, 86400);
        }

        return $entity;
    }

    public function findAll(): array
    {
        $cacheKey = "address_format.all";
        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $entities = $this->getMapper()->findAll();

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $entities, 86400);
        }

        return $entities;
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        // Critérios complexos pulam o cache e vão direto ao DAO
        return $this->getMapper()->findOneBy($criteria);
    }

    /**
     * [LEGACY DTO] Retorna o formato de endereço como Array Plano para compatibilidade legada.
     *
     * @param int $address_format_id
     * @return array
     */
    public function getAddressFormat(int $address_format_id): array
    {
        $entity = $this->find($address_format_id);

        if ($entity) {
            return [
                'address_format_id' => $entity->getId(),
                'name'              => $entity->getName(),
                'address_format'    => $entity->getAddressFormat()
            ];
        }

        return [];
    }
}
