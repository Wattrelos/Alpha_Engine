<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Address;

/**
 * Mapper para a entidade Address.
 * Isola a camada de banco de dados (tabela address).
 * 
 * Os métodos de persistência e consulta básicos (findAll, findById, findBy, etc.)
 * são resolvidos dinamicamente pela classe pai (BaseMapper) utilizando as propriedades abaixo.
 */
class AddressMapper extends BaseMapper
{
    protected string $tableName = 'address';
    protected string $entityClass = Address::class;

    public function save(\Alpha\Model\Domain\InterfaceEntity $entity): int
    {
        if ($entity->getId() > 0) {
            $this->dao->update($entity);
            return $entity->getId();
        }
        return $this->dao->create($entity);
    }

    public function delete(int $id): bool
    {
        $entity = new Address();
        $entity->setId($id);
        return (bool)$this->dao->delete($entity);
    }
}