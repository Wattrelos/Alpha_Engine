<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;

/**
 * HeaderMapper - Gerenciador de persistência e configuração para o Header.
 * 
 * Alpha Engine:
 * - Isola o acesso às configurações de sistema e extensões de Analytics.
 */
class HeaderMapper extends BaseMapper
{
    protected string $tableName = 'setting';

    /**
     * Obtém as configurações de Analytics ativas.
     */
    public function getAnalyticsExtensions(): array
    {
        $extensionMapper = new ExtensionMapper();
        return $extensionMapper->getExtensionsByType('analytics');
    }
}