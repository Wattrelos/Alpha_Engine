<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\UserAuthorize;

/**
 * UserAuthorizeRepository
 * Gerencia as autorizações de dispositivos do painel administrativo.
 */
class UserAuthorizeRepository extends AbstractRepository
{
    /**
     * Busca uma autorização de sessão administrativa com base no token.
     * 
     * @param string $token
     * @return UserAuthorize|null
     */
    public function findByToken(string $token): ?UserAuthorize
    {
        return $this->mapper->findOneBy(['token' => $token]);
    }

    /**
     * Encerra (exclui) uma sessão autorizada de administrador.
     */
    public function revokeAuthorization(int $id): bool
    {
        return $this->mapper->delete($id);
    }
}
