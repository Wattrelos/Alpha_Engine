<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\CustomerAuthorize;

/**
 * CustomerAuthorizeRepository
 * Gerencia a persistência e busca de autorizações de dispositivos para clientes ("Manter conectado").
 */
class CustomerAuthorizeRepository extends AbstractRepository
{
    /**
     * Busca uma autorização de sessão com base no token do cookie/cabeçalho.
     * 
     * @param string $token
     * @return CustomerAuthorize|null
     */
    public function findByToken(string $token): ?CustomerAuthorize
    {
        return $this->mapper->findOneBy(['token' => $token]);
    }

    /**
     * Encerra (exclui) uma sessão autorizada.
     */
    public function revokeAuthorization(int $id): bool
    {
        return $this->mapper->delete($id);
    }
}