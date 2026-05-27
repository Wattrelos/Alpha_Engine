<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\AbstractMapper;
use Alpha\Model\Domain\Entities\AddressFormat;
use Alpha\Model\Domain\InterfaceEntity;
use InvalidArgumentException;

class AddressFormatMapper extends AbstractMapper
{
    protected string $tableName = 'address_format';
    protected string $primaryKey = 'address_format_id';

    public function findById(int $id): ?AddressFormat
    {
        $data = $this->dao->get($this->tableName, [$this->primaryKey => $id]);
        return $data ? $this->hydrate($data) : null;
    }

    public function findAll(): array
    {
        $results = $this->dao->getAll($this->tableName);
        return array_map([$this, 'hydrate'], $results);
    }

    public function findOneBy(array $criteria): ?AddressFormat
    {
        $data = $this->dao->get($this->tableName, $criteria);
        return $data ? $this->hydrate($data) : null;
    }

    public function search(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $results = $this->dao->search($this->tableName, $criteria, $orderBy, $limit, $offset);
        return array_map([$this, 'hydrate'], $results);
    }

    protected function hydrate(array $data): AddressFormat
    {
        $entity = new AddressFormat();
        
        $entity->setId((int)($data['address_format_id'] ?? $data['id'] ?? 0));
        
        if (isset($data['name'])) {
            $entity->setName((string)$data['name']);
        }
        if (isset($data['address_format'])) {
            $entity->setAddressFormat((string)$data['address_format']);
        }
        
        return $entity;
    }

    protected function extract(InterfaceEntity $entity): array
    {
        if (!$entity instanceof AddressFormat) {
            throw new InvalidArgumentException("A entidade precisa ser uma instância de AddressFormat.");
        }
        
        return [
            'name'           => $entity->getName(),
            'address_format' => $entity->getAddressFormat()
        ];
    }
}