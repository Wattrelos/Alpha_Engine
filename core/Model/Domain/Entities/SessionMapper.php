<?php

namespace Alpha\Mappers\System;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\Session;

/**
 * SessionMapper - Gerencia a persistência de sessões na tabela tbkk_session.
 * Implementa a lógica atômica de escrita e limpeza de dados expirados.
 */
class SessionMapper extends BaseMapper
{
    protected string $table = 'session';

    /**
     * Realiza a escrita atômica da sessão usando ON DUPLICATE KEY UPDATE.
     * Isso garante que não existam colisões de tokens e otimiza a performance de gravação.
     */
    public function write(string $token, string $data, int $expire): void
    {
        // Alpha Engine: Uso de Prepared Statements via DAO para segurança total
        $sql = "INSERT INTO " . DB_PREFIX . "session SET 
                session_token = :token, 
                data = :data, 
                expire = :expire 
                ON DUPLICATE KEY UPDATE 
                data = :data, 
                expire = :expire";

        $params = [
            'token'  => $token,
            'data'   => $data,
            'expire' => date('Y-m-d H:i:s', time() + $expire)
        ];

        $this->dao->execute($sql, $params);
    }

    /**
     * Remove fisicamente uma sessão baseada no seu token de transporte.
     */
    public function deleteByToken(string $token): void
    {
        $sql = "DELETE FROM " . DB_PREFIX . "session WHERE session_token = :token";
        $this->dao->execute($sql, ['token' => $token]);
    }

    /**
     * Garbage Collector: Remove registros cujo timestamp de expiração já passou.
     */
    public function deleteExpired(): void
    {
        $sql = "DELETE FROM " . DB_PREFIX . "session WHERE expire < :now";
        $this->dao->execute($sql, ['now' => date('Y-m-d H:i:s')]);
    }
}