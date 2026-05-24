<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\ApiIp;

/**
 * ApiIpRepository
 * Gerencia as regras de restrição de IP (Whitelist) da API.
 */
class ApiIpRepository extends AbstractRepository
{
    /**
     * Verifica se um endereço IP específico está autorizado para a API fornecida.
     *
     * @param int $apiId
     * @param string $ip
     * @return bool
     */
    public function isIpAllowed(int $apiId, string $ip): bool
    {
        $result = $this->mapper->findOneBy(['apiId' => $apiId, 'ip' => $ip]);
        return $result instanceof ApiIp;
    }
}