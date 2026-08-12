<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerGroupMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * Class CustomerGroupRepository
 * 
 * Camada de domínio (Fachada) para a gestão de grupos de clientes.
 * Centraliza as regras de negócio e provê compatibilidade com controladores legados.
 */
class CustomerGroupRepository extends AbstractRepository implements BaseRepositoryInterface {

    protected function getMapper(): CustomerGroupMapper {
        return $this->mapperFactory->get(CustomerGroupMapper::class);
    }

    /**
     * Legacy Bridge: Retorna todos os grupos de clientes (formatados para a view/registro)
     */
    public function getCustomerGroups(int $languageId): array {
        $groups = $this->getMapper()->getCustomerGroups($languageId);
        
        // Interoperabilidade DTO: Garante a presença da chave 'id' pura exigida 
        // pelo padrão de interoperabilidade no Registration Controller.
        foreach ($groups as &$group) {
            $group['id'] = $group['id'] ?? $group['customer_group_id'] ?? 0;
            $group['customer_group_id'] = $group['id'];
        }
        
        return $groups;
    }

    /**
     * Legacy Bridge: Retorna os dados formatados de um grupo de cliente específico.
     */
    public function getCustomerGroup(int $customerGroupId, int $languageId): array {
        $group = $this->getMapper()->getCustomerGroup($customerGroupId, $languageId);
        if ($group) {
            $group['id'] = $group['id'] ?? $group['customer_group_id'] ?? 0;
            $group['customer_group_id'] = $group['id'];
        }
        return $group;
    }

    // --- BaseRepositoryInterface bindings ---

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
