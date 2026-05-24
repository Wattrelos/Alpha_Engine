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
        // Alpha Engine Failsafe: Intercepta sessões corrompidas (> 5MB) antes de estourar a memória
        $checkQuery = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("session_token = ?", [$token])
            ->select('LENGTH(data) AS size')
            ->limit(1);
            
        $check = $this->dao->executeQuery($checkQuery);
        if ($check && (int)$check[0]['size'] > 5000000) {
            $this->deleteByToken($token);
            return null;
        }

        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
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
    public function saveSession(string $token, string $data, string $expireDate): void
    {
        // Alpha Engine: Embora o QueryBuilder construa a query, para operações atômicas 
        // como ON DUPLICATE KEY UPDATE, mantemos a execução via DAO para garantir logs e segurança.
        $sql = "INSERT INTO " . $this->getFullTableName() . " 
                (`session_token`, `data`, `expire_at`, `user_agent`, `ip`) 
                VALUES (?, ?, ?, '', '') 
                ON DUPLICATE KEY UPDATE `data` = VALUES(`data`), `expire_at` = VALUES(`expire_at`)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token, $data, $expireDate]);
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
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("session_token = ?", [$token])
            ->select('id', 'LENGTH(data) AS size')
            ->limit(1);

        $results = $this->dao->executeQuery($query);

        if (!$results) return null;

        // Alpha Engine Failsafe: Evita "Memory Exhausted" no PDO bloqueando sessões corrompidas.
        // Se o tamanho do blob ultrapassar ~5MB, a sessão é considerada lixo e destruída.
        if ((int)$results[0]['size'] > 5000000) {
            $this->deleteByToken($token);
            return null;
        }

        $sessions = $this->dao->readByIds($this->entityClass, [(int)$results[0]['id']]);
        return $sessions ? $sessions[0] : null;
    }

    /**
     * Alpha Engine: Implementação padronizada para busca de registro único.
     */
    public function findOneBy(array $criteria): ?Session
    {
        // Blindagem Alpha: Previne Memory Leak forçando busca controlada por LIMIT 1
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->select('id', 'LENGTH(data) AS size')
            ->limit(1);

        foreach ($criteria as $key => $value) {
            $column = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $key));
            $query->where("`$column` = ?", [$value]);
        }

        $results = $this->dao->executeQuery($query);

        if (!$results) return null;

        // Alpha Engine Failsafe: Evita carregar sessões monstruosas no Identity Map
        if ((int)$results[0]['size'] > 5000000) {
            $this->dao->execute((new QueryBuilder())->delete($this->getFullTableName())->where("id = ?", [$results[0]['id']]));
            return null;
        }

        $sessions = $this->dao->readByIds($this->entityClass, [(int)$results[0]['id']]);
        return $sessions ? $sessions[0] : null;
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