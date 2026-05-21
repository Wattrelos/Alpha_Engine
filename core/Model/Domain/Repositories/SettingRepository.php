<?php

namespace Alpha\Model\Domain\Repositories;

use Opencart\System\Engine\Registry;
use Alpha\Mappers\EntityMappers\SettingMapper;
use Alpha\Mappers\MapperFactory;

/**
 * Class SettingRepository
 * 
 * Centraliza o carregamento das configurações da loja utilizando Cache em Memória.
 * O banco de dados só é consultado 1 vez por requisição (fim de múltiplos SELECTs).
 */
class SettingRepository
{
    private SettingMapper $settingMapper;
    private array $data = [];
    private bool $isLoaded = false;

    public function __construct(MapperFactory $mapperFactory, Registry $registry)
    {
        $this->settingMapper = $mapperFactory->get(SettingMapper::class);
    }

    /**
     * Carrega as configurações via Mapper apenas se não estiverem na memória.
     */
    private function loadAll(): void
    {
        if (!$this->isLoaded) {
            $this->data = $this->settingMapper->findAll();
            $this->isLoaded = true;
        }
    }

    public function getSettings(int $storeId = 0): array
    {
        $this->loadAll();
        $result = [];

        foreach ($this->data as $row) {
            if ((int)$row['store_id'] === 0 || (int)$row['store_id'] === $storeId) {
                $result[] = $row;
            }
        }

        // Emula o `ORDER BY store_id ASC` para garantir sobreposição correta
        usort($result, function($a, $b) {
            return (int)$a['store_id'] <=> (int)$b['store_id'];
        });

        return $result;
    }

    public function getSetting(string $code, int $storeId = 0): array
    {
        $this->loadAll();
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
        $this->loadAll();
        foreach ($this->data as $row) {
            if ((int)$row['store_id'] === $storeId && $row['key'] === $key) {
                return (string)$row['value'];
            }
        }
        return '';
    }
}