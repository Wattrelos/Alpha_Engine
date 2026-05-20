<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * GdprMapper - Gerencia as solicitações de privacidade (GDPR).
 */
class GdprMapper extends BaseMapper
{
    protected string $tableName = 'gdpr';

    public function getGdprsByEmail(string $email): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'gdpr')
            ->where("email = ?", [$email])
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    public function addGdpr(string $code, string $email, string $action): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        if (!$conn instanceof \PDO) {
            throw new \RuntimeException("Erro Alpha Engine: PDO não disponível para GDPR.");
        }
        $sql = "INSERT INTO `" . DB_PREFIX . "gdpr` SET `code` = ?, `email` = ?, `action` = ?, `status` = 0, `date_added` = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$code, $email, $action]);
    }

    public function getGdprByCode(string $code): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'gdpr')
            ->where("code = ?", [$code])
            ->select('*');

        $results = $this->dao->executeQuery($query);
        return $results ? $results[0] : [];
    }

    public function editStatus(int $gdpr_id, int $status): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        if (!$conn instanceof \PDO) {
            throw new \RuntimeException("Conexão PDO não disponível para editar status GDPR.");
        }
        $sql = "UPDATE `" . DB_PREFIX . "gdpr` SET `status` = ? WHERE `id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([(int)$status, (int)$gdpr_id]);
    }
}