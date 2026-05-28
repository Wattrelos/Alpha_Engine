<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\ExtensionMapper;
use Alpha\Model\Domain\Entities\Extension;
use Alpha\Model\Domain\InterfaceEntity;

class ExtensionRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): ExtensionMapper
    {
        return $this->mapperFactory->get(ExtensionMapper::class);
    }

    public function find(int $id): ?InterfaceEntity
    {
        return $this->getMapper()->findById($id);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }

    /**
     * Retorna todas as Extensões instaladas.
     * 
     * @return Extension[]
     */
    public function findAll(): array
    {
        $cacheKey = 'extension.all';
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $extensions = $this->getMapper()->findAll();

        if ($this->cache) {
            $this->cache->set($cacheKey, $extensions);
        }

        return $extensions;
    }

    /**
     * Retorna Extensões instaladas de um tipo específico (ex: 'payment', 'captcha').
     * 
     * @param string $type
     * @return Extension[]
     */
    public function getExtensionsByType(string $type): array
    {
        $cacheKey = 'extension.type.' . $type;
        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $extensions = $this->getMapper()->getExtensionsByType($type);

        if ($this->cache) {
            $this->cache->set($cacheKey, $extensions);
        }

        return $extensions;
    }

    /**
     * Retorna uma Extensão específica por tipo e código.
     * 
     * @param string $type
     * @param string $code
     * @return Extension|null
     */
    public function getExtensionByCode(string $type, string $code): ?Extension
    {
        return $this->getMapper()->getExtensionByCode($type, $code);
    }

    /**
     * Retorna a lista de nomes das extensões instaladas.
     * 
     * @return array
     */
    public function getDistinctExtensions(): array
    {
        return $this->getMapper()->getDistinctExtensions();
    }
}