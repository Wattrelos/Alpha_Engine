<?php

namespace Alpha\Model\Domain\Repositories;

/**
 * AttributeRepository
 * Gerencia as especificações técnicas globais do catálogo.
 */
class AttributeRepository extends AbstractRepository
{
    /**
     * Retorna todos os atributos que pertencem a um grupo específico.
     *
     * @param int $attributeGroupId
     * @return array
     */
    public function findByGroupId(int $attributeGroupId): array
    {
        return $this->mapper->search(
            ['attributeGroupId' => $attributeGroupId],
            ['sortOrder' => 'ASC'] // Garante a ordenação nativa exigida pelo painel
        );
    }
}