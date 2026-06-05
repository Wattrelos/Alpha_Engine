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
    private int $loadedStoreId = 1; // New default value to prevent unexpected behavior

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
    public function find(int $id): ?InterfaceEntity
    {
        return null;
    }
    public function findAll(): array
    {
        return $this->data;
    }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }
    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        return null;
    }

    public function editSetting(string $code, array $data, int $storeId = 1): void
    {
        $dao = new \Alpha\Model\DataAccessObject\DataAccessObject();
        
        // 1. Delete existing settings for this store and code
        $deleteQuery = (new \Alpha\Model\DataAccessObject\QueryBuilder())
            ->delete(DB_PREFIX . 'setting')
            ->where('store_id = ?', [$storeId])
            ->where('code = ?', [$code]);
        $dao->execute($deleteQuery);

        // 2. Insert new settings
        foreach ($data as $key => $value) {
            $serialized = 0;
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $serialized = 1;
            }

            $sql = "INSERT INTO `" . DB_PREFIX . "setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES (?, ?, ?, ?, ?)";
            $dao->executeRawSQL($sql, [$storeId, $code, $key, (string)$value, $serialized]);
        }

            // 3. Invalidate memory cache
        $this->isLoaded = false;

        // 4. Clear formatted settings cache file if exists
        $cachePath = DIR_STORAGE . 'cache/store_settings_formatted.json';
        if (is_file($cachePath)) {
            @unlink($cachePath);
        }
    }
}
