<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper; // Alterado de AbstractMapper para BaseMapper
use Alpha\Model\DataAccessObject\QueryBuilder; // Adicionado
use Alpha\Model\DataAccessObject\ConnectionDB; // Adicionado para acesso direto ao PDO em operações específicas
use Alpha\Model\Domain\Entities\Session;

/**
 * SessionMapper - Alpha Engine
 * 
 * Gerencia a persistência de sessões utilizando a nova estrutura de surrogate key.
 * Isola a lógica de SQL e mapeia o token único (session_token) para a entidade Session.
 */
class SessionMapper extends BaseMapper // Alterado de AbstractMapper para BaseMapper
{
    protected string $tableName = 'session'; // Alterado de $table para $tableName para consistência com BaseMapper
    protected string $entityClass = Session::class; // Adicionado para que findOneBy funcione corretamente

    /**
     * Busca os dados brutos de uma sessão ativa pelo token.
     *
     * @param string $token Hash da sessão.
     * @param string $now Data atual para verificação de expiração.
     * @return string|null
     */
    public function getActiveSessionData(string $token, string $now): ?string
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'session')
            ->where("session_token = ?", [$token])
            ->where("expire_at > ?", [$now])
            ->select('data')
            ->limit(1);

        // Utiliza o DataAccessObject para executar a consulta
        $results = $this->dao->executeQuery($query);
        return $results[0]['data'] ?? null;
    }

    /**
     * Persiste os dados da sessão via token.
     * Utilizado pelo driver de sessão para operações rápidas de I/O.
     */
    public function saveSession(string $token, string $data, string $expireDate, string $userAgent, string $ip): void
    {
        // Alpha Engine: Embora o QueryBuilder construa a query, para operações atômicas 
        // como ON DUPLICATE KEY UPDATE, mantemos a execução via DAO para garantir logs e segurança.
        $sql = "INSERT INTO " . $this->getFullTableName() . " 
                (`session_token`, `data`, `expire_at`, `user_agent`, `ip`) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE `data` = VALUES(`data`), `expire_at` = VALUES(`expire_at`), `user_agent` = VALUES(`user_agent`), `ip` = VALUES(`ip`)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token, $data, $expireDate, $userAgent, $ip]);
    }

    /**
     * Remove uma sessão pelo seu token único (session_token).
     */
    public function deleteByToken(string $token): void
    {
        $query = (new QueryBuilder())
            ->delete($this->getFullTableName())
            ->where("session_token = ?", [$token]);

        $this->dao->execute($query);
    }

    /**
     * Atalho para buscar uma entidade de sessão pelo seu token.
     */
    public function getSession(string $token): ?Session
    {
        return $this->findOneBy(['sessionToken' => $token]);
    }

    /**
     * Alpha Engine: Implementação padronizada para busca de registro único.
     */
    public function findOneBy(array $criteria): ?Session
    {
        $results = $this->search($criteria);
        return $results ? $results[0] : null;
    }

    /**
     * Executa a limpeza de sessões expiradas (Garbage Collection).
     * 
     * @return int Total de registros removidos.
     */
    public function deleteExpired(): int
    {
        $query = (new QueryBuilder())
            ->delete($this->getFullTableName())
            ->where("expire_at < ?", [gmdate('Y-m-d H:i:s')]);

        return (int)$this->dao->execute($query);
    }

    /**
     * Atualiza o customer_id vinculado a um token.
     */
    public function updateCustomerId(string $token, int $customerId): void
    {
        $query = (new QueryBuilder())
            ->update($this->getFullTableName())
            ->set("customer_id", $customerId)
            ->where("session_token = ?", [$token]);

        $this->dao->execute($query);
    }
}