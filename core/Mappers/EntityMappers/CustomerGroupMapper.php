<?php
namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Class CustomerGroupMapper
 * 
 * Gerencia a persistência e recuperação de grupos de clientes.
 * Atua como camada de acesso a dados isolando o SQL da aplicação.
 */
class CustomerGroupMapper extends BaseMapper {
    
    protected string $tableName = 'customer_group';
    protected string $entityClass = \Alpha\Model\Domain\Entities\CustomerGroup::class;

    /**
     * Extrai todos os grupos de clientes disponíveis junto com a tradução.
     */
    public function getCustomerGroups(int $languageId): array {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName(), 'cg')
            ->leftJoin(DB_PREFIX . 'customer_group_description', 'cgd', 'cg.id = cgd.customer_group_id')
            ->where('cgd.language_id = ?', [$languageId])
            ->orderBy('cg.sort_order', 'ASC')
            ->orderBy('cgd.name', 'ASC');

        return $this->dao->executeQuery($query);
    }

    /**
     * Extrai os dados de um grupo de clientes específico com base no idioma.
     */
    public function getCustomerGroup(int $customerGroupId, int $languageId): array {
        $query = (new QueryBuilder())
            ->select('DISTINCT *')
            ->from($this->getFullTableName(), 'cg')
            ->leftJoin(DB_PREFIX . 'customer_group_description', 'cgd', 'cg.id = cgd.customer_group_id')
            ->where('cg.id = ? AND cgd.language_id = ?', [$customerGroupId, $languageId]);

        $results = $this->dao->executeQuery($query);
        return $results[0] ?? [];
    }
}