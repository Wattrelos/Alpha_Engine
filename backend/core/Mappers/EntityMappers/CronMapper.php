<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\DataAccessObject\ConnectionDB;

/**
 * CronMapper - Gerencia as operações de banco de dados para tarefas agendadas (Cron).
 */
class CronMapper extends BaseMapper
{
    protected string $tableName = 'cron';

    public function editCron(int $cron_id): void
    {
        $conn = ConnectionDB::getInstance()->getConnection();
        $sql = "UPDATE `" . $this->getFullTableName() . "` SET `date_modified` = NOW() WHERE `cron_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$cron_id]);
    }

    public function editStatus(int $cron_id, bool $status): void
    {
        $conn = ConnectionDB::getInstance()->getConnection();
        $sql = "UPDATE `" . $this->getFullTableName() . "` SET `status` = ? WHERE `cron_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([(int)$status, $cron_id]);
    }

    public function getCron(int $cron_id): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`cron_id` = ?", [$cron_id])
            ->select('DISTINCT *');

        $result = $this->dao->executeQuery($query);
        return $result[0] ?? [];
    }

    public function getCronByCode(string $code): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("`code` = ?", [$code])
            ->select('DISTINCT *')
            ->limit(1);

        $result = $this->dao->executeQuery($query);
        return $result[0] ?? [];
    }

    public function getCrons(): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->orderBy("`date_modified`", "DESC")
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    public function getTotalCrons(): int
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName());

        return $this->dao->executeCount($query);
    }
}