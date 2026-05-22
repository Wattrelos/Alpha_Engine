<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ExtensionMapper;
use Alpha\Model\Domain\Entities\Extension;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * ExtensionRepository - Autoridade de Domínio para Extensões (Módulos).
 */
class ExtensionRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): ExtensionMapper
    {
        return $this->mapperFactory->get(ExtensionMapper::class);
    }

    /**
     * Recupera os nomes de extensões distintas.
     */
    public function getDistinctExtensions(): array
    {
        return $this->getMapper()->getDistinctExtensions();
    }

    /**
     * Recupera extensões instaladas filtradas por tipo.
     * 
     * @param string $type
     * @return Extension[]
     */
    public function getExtensionsByType(string $type): array
    {
        return $this->getMapper()->getExtensionsByType($type);
    }

    /**
     * Recupera todas as extensões em formato de array bruto (Legacy Bridge).
     */
    public function getExtensions(): array
    {
        return $this->getMapper()->getExtensions();
    }

    /**
     * Recupera uma extensão específica pelo tipo e código.
     */
    public function getExtensionByCode(string $type, string $code): ?Extension
    {
        return $this->getMapper()->getExtensionByCode($type, $code);
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return $this->getMapper()->findById($id); }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}