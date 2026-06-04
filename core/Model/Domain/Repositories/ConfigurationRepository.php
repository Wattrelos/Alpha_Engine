<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\MapperFactory;

/**
 * ConfigurationRepository
 * Orquestra o carregamento de configurações de banco (Settings) e de arquivos físicos.
 */
class ConfigurationRepository extends AbstractRepository
{
    /**
     * Alpha Engine: Carrega um arquivo de configuração (.php) e injeta suas chaves 
     * diretamente no objeto nativo Config, abolindo o loader.php
     *
     * @param string $filename Nome do arquivo (ex: 'checkout', 'paypal')
     * @throws \Exception
     */
    public function loadFile(string $filename): void
    {
        $file = DIR_CONFIG . $filename . '.php';

        if (file_exists($file)) {
            $_ = [];
            require($file);

            if ($this->config) {
                foreach ($_ as $key => $value) {
                    $this->config->set($key, $value);
                }
            }
        } else {
            throw new \Exception(sprintf('Alpha Engine Erro: Arquivo de configuração %s não encontrado.', $file));
        }
    }

    /**
     * Exemplo de atalho para acessar o SettingRepository já existente
     */
    public function getDatabaseSetting(string $key, int $storeId = 1): string
    {
        return RepositoryFactory::getInstance()
            ->get(SettingRepository::class)
            ->getValue($key, $storeId);
    }
}
