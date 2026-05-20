<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\LengthClassMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * LengthClassRepository - Autoridade de Domínio para Unidades de Medida de Comprimento.
 *
 * Centraliza o acesso às unidades de medida (cm, mm, inch, etc), utilizando o 
 * LengthClassMapper para persistência e garantindo que o Identity Map seja respeitado.
 */
class LengthClassRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Busca uma unidade de comprimento pelo seu ID único.
     *
     * @param int $id
     * @return InterfaceEntity|null
     */
    public function find(int $id): ?InterfaceEntity
    {
        return $this->getMapper()->findById($id);
    }

    /**
     * Retorna todas as unidades de comprimento cadastradas.
     *
     * @return InterfaceEntity[]
     */
    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    /**
     * Busca unidades baseado em critérios específicos.
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Busca uma única unidade baseado em critérios.
     *
     * @param array $criteria
     * @return InterfaceEntity|null
     */
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return $this->getMapper()->findOneBy($criteria);
    }

    /**
     * Define o Mapper principal para a classe (requerido por AbstractRepository).
     */
    protected function getMapper()
    {
        return $this->mapperFactory->get(LengthClassMapper::class);
    }
}