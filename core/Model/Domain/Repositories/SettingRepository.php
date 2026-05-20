<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\SettingMapper;
use Alpha\Model\Domain\Entities\Setting;

/**
 * SettingRepository - Camada de acesso simplificada para as configurações.
 * 
 * Alpha Engine:
 * - Oferece métodos de conveniência para buscar valores únicos ou grupos.
 */
class SettingRepository extends AbstractRepository
{
    /**
     * Obtém o valor de uma configuração específica.
     */
    public function getValue(string $key, int $storeId = 0): mixed
    {
        /** @var SettingMapper $mapper */
        $mapper = $this->mapper->get(SettingMapper::class);
        
        $setting = $mapper->getByKey($key, $storeId);
        
        return $setting ? $setting->getValue() : null;
    }

    /**
     * Retorna um array associativo de todas as configurações de um código (ex: 'config').
     */
    public function getSettingsByCode(string $code, int $storeId = 0): array
    {
        /** @var SettingMapper $mapper */
        $mapper = $this->mapper->get(SettingMapper::class);
        $settings = $mapper->getSettingsByCode($code, $storeId);
        
        $data = [];
        foreach ($settings as $setting) {
            /** @var Setting $setting */
            $data[$setting->getKey()] = $setting->getValue();
        }

        return $data;
    }

}