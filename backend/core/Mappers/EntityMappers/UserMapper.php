<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\User;

/**
 * Mapper para a entidade User.
 * Isola a camada de persistência da tabela user.
 */
class UserMapper extends BaseMapper
{
    protected string $tableName = 'user';
    protected string $entityClass = User::class;

    /**
     * Registra uma tentativa de login para auditoria e bloqueio de brute-force.
     */
    public function addLoginAttempt(string $username, string $ip): void
    {
        $query = "INSERT INTO `" . DB_PREFIX . "user_login` SET `username` = ?, `ip` = ?, `date_added` = NOW(), `date_modified` = NOW()";
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute([$username, $ip]);
    }

    /**
     * Conta o número de tentativas de login em um período (ex: 1 hora).
     */
    public function getLoginAttempts(string $username): int
    {
        $query = (new \Alpha\Model\DataAccessObject\QueryBuilder())
            ->from(DB_PREFIX . 'user_login')
            ->where("LCASE(username) = ?", [strtolower($username)])
            ->where("date_added > ?", [date('Y-m-d H:i:s', strtotime('-1 hour'))])
            ->select('COUNT(*) AS total');

        $results = $this->dao->executeQuery($query);
        return $results ? (int)$results[0]['total'] : 0;
    }

    /**
     * Limpa o histórico de tentativas após um login bem-sucedido.
     */
    public function deleteLoginAttempts(string $username): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare("DELETE FROM `" . DB_PREFIX . "user_login` WHERE LCASE(username) = ?");
        $stmt->execute([strtolower($username)]);
    }
}