<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomFieldMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * CustomFieldRepository - Autoridade de Domínio para Campos Personalizados.
 */
class CustomFieldRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Retorna os campos personalizados estruturados para um grupo de clientes.
     * Implementa cache em memória/físico para evitar N+1 queries na renderização de formulários.
     */
    public function getCustomFields(int $customerGroupId): array
    {
        $cacheKey = "custom_field.group.{$customerGroupId}.lang.{$this->language_id}";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $mapper = $this->getMapper();
        $results = $mapper->getCustomFields($customerGroupId, $this->language_id);

        if ($this->cache) {
            $this->cache->set($cacheKey, $results, 3600); // Armazena cache por 1 hora
        }

        return $results;
    }

    protected function getMapper(): CustomFieldMapper
    {
        return $this->mapperFactory->get(CustomFieldMapper::class);
    }

    public function find(int $id): ?InterfaceEntity {
        return $this->getMapper()->findById($id);
    }
    public function findAll(): array {
        return $this->getMapper()->findAll();
    }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }
    public function findOneBy(array $criteria): ?InterfaceEntity {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}
