<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Setting;

/**
 * SettingMapper - Gerenciador de persistência para a tabela de configurações.
 * 
 * Alpha Engine:
 * - Padroniza a conversão de JSON para configurações serializadas do OC4.
 */
class SettingMapper extends BaseMapper
{
    protected string $tableName = 'setting';
    protected string $entityClass = Setting::class;

    /**
     * Busca uma configuração individual por chave.
     */
    public function getByKey(string $key, int $storeId = 0): ?Setting
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = :store_id AND `key` = :key LIMIT 1";
        $result = $this->dao->executeQuery($sql, [
            'store_id' => $storeId,
            'key' => $key
        ]);

        return $result ? $this->hydrate($result[0]) : null;
    }

    /**
     * Busca todas as configurações de uma loja por código (grupo).
     */
    public function getSettingsByCode(string $code, int $storeId = 0): array
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = :store_id AND code = :code";
        $params = [
            'store_id' => $storeId,
            'code' => $code
        ];

        $results = $this->dao->executeQuery($sql, $params);
        $entities = [];

        foreach ($results as $result) {
            $entities[] = $this->hydrate($result);
        }

        return $entities;
    }

    /**
     * Hidrata a entidade tratando a serialização JSON nativa do OpenCart 4.
     */
    protected function hydrate(array $data): Setting
    {
        $setting = new Setting();
        $setting->setId((int)$data['setting_id'])
                ->setStoreId((int)$data['store_id'])
                ->setCode($data['code'])
                ->setKey($data['key'])
                ->setSerialized((bool)$data['serialized']);

        if ($setting->isSerialized()) {
            $setting->setValue(json_decode($data['value'], true));
        } else {
            $setting->setValue($data['value']);
        }

        return $setting;
    }

    /**
     * Deleta configurações de um grupo.
     */
    public function deleteByCode(string $code, int $storeId = 0): void
    {
        $sql = "DELETE FROM " . DB_PREFIX . "setting WHERE store_id = :store_id AND code = :code";
        $this->dao->execute($sql, ['store_id' => $storeId, 'code' => $code]);
    }

    /**
     * Insere uma configuração individual.
     */
    public function insertSetting(int $storeId, string $code, string $key, mixed $value, bool $serialized = false): void
    {
        $val = $serialized ? json_encode($value) : $value;
        $sql = "INSERT INTO " . DB_PREFIX . "setting SET store_id = :store_id, code = :code, `key` = :key, `value` = :value, serialized = :serialized";
        $this->dao->execute($sql, ['store_id' => $storeId, 'code' => $code, 'key' => $key, 'value' => $val, 'serialized' => (int)$serialized]);
    }
}