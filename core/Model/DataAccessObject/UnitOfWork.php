<?php

namespace Alpha\Model\DataAccessObject;

/**
 * UnitOfWork - Coordena a atomicidade das operações na Alpha Engine.
 */
class UnitOfWork {
    private \PDO $db;

    public function __construct() {
        $connection = ConnectionDB::getInstance()->getConnection();
        if (!$connection instanceof \PDO) {
            throw new \RuntimeException("Conexão PDO consolidada não disponível em UnitOfWork.");
        }
        $this->db = $connection;
    }

    /**
     * Executa uma operação dentro de uma transação atômica.
     * 
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function transaction(callable $callback): mixed
    {
        try {
            $this->start();
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Inicia uma transação se não houver uma ativa.
     */
    public function start(): void {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    /**
     * Confirma as alterações no banco de dados.
     */
    public function commit(): void {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    /**
     * Reverte as alterações em caso de falha.
     */
    public function rollback(): void {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}