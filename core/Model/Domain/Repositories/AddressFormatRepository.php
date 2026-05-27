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
        // Cacheia a entidade de formato de endereço por 24 horas para evitar N+1
        return $this->getCacheStrategy()->remember("address_format.{$id}", 86400, function () use ($id) {
            return $this->getMapper()->findById($id);
        });
    }

    public function findAll(): array
    {
        return $this->getCacheStrategy()->remember("address_format.all", 86400, function () {
            return $this->getMapper()->findAll();
        });
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
}