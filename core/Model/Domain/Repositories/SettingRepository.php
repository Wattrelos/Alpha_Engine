<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\SettingMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * Class SettingRepository
 * 
 * Centraliza o carregamento das configurações da loja utilizando Cache em Memória.
 * O banco de dados só é consultado 1 vez por requisição (fim de múltiplos SELECTs).
 */
class SettingRepository extends AbstractRepository implements BaseRepositoryInterface
{
    private array $data = [];
    private bool $isLoaded = false;
    private int $loadedStoreId = -1;

    protected function getMapper(): SettingMapper
    {
        return $this->mapperFactory->get(SettingMapper::class);
    }

    /**
     * Carrega as configurações da loja atual (e da default) via Mapper apenas 1 vez.
     */
    private function loadForStore(int $storeId): void
    {
        if (!$this->isLoaded || $this->loadedStoreId !== $storeId) {
            $this->data = $this->getMapper()->findByStoreId($storeId);
            $this->isLoaded = true;
            $this->loadedStoreId = $storeId;
        }
    }

    public function getSettings(int $storeId = 0): array
    {
        $this->loadForStore($storeId);
        
        // O banco de dados já cuidou da filtragem e da ordenação correta.
        return $this->data;
    }

    public function getSetting(string $code, int $storeId = 0): array
    {
        $this->loadForStore($storeId);
        $settingData = [];

        foreach ($this->data as $row) {
            if ((int)$row['store_id'] === $storeId && $row['code'] === $code) {
                if (!$row['serialized']) {
                    $settingData[$row['key']] = $row['value'];
                } else {
                    $settingData[$row['key']] = $row['value'] ? json_decode($row['value'], true) : [];
                }
            }
        }
        return $settingData;
    }

    public function getValue(string $key, int $storeId = 0): string
    {
        $this->loadForStore($storeId);
        foreach ($this->data as $row) {
            if ((int)$row['store_id'] === $storeId && $row['key'] === $key) {
                return (string)$row['value'];
            }
        }
        return '';
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return $this->data; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}