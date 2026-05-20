<?php

namespace Alpha\Mappers\Security;

use Alpha\Mappers\BaseMapper;

/**
 * ApiSessionMapper - Gerencia a persistência de sessões de API.
 * Implementa a lógica de Surrogate Keys e busca por token.
 */
class ApiSessionMapper extends BaseMapper
{
    protected string $table = 'api_session';

    /**
     * Remove sessões baseadas no token de transporte.
     */
    public function deleteByToken(string $token): void
    {
        $sql = "DELETE FROM " . DB_PREFIX . "api_session WHERE session_token = :token";
        $this->dao->execute($sql, ['token' => $token]);
    }
}