<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\UserGroup;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Mapper para a entidade UserGroup.
 * Isola a camada de persistência da tabela user_group e agsc_user_group_description.
 */
class UserGroupMapper extends BaseMapper
{
    protected string $tableName = 'user_group';
    protected string $table = 'user_group';
    protected string $entityClass = UserGroup::class;

    /**
     * Conta quantos usuários/funcionários estão vinculados a um grupo.
     */
    public function countUsersInGroup(int $userGroupId): int
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'user')
            ->where("user_group_id = ?", [$userGroupId])
            ->select('COUNT(*) AS total');

        $results = $this->dao->executeQuery($query);
        return $results ? (int)$results[0]['total'] : 0;
    }

    /**
     * Busca um grupo com a tradução do nome para o idioma especificado.
     */
    public function findWithLanguage(int $id, int $languageId): ?UserGroup
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName(), 'ug')
            ->leftJoin(DB_PREFIX . 'user_group_description', 'ugd', 'ug.id = ugd.user_group_id AND ugd.language_id = ' . (int)$languageId)
            ->where('ug.id = ?', [$id])
            ->select('ug.id', 'ug.permission', 'COALESCE(ugd.name, ug.name) AS name');

        $results = $this->dao->executeQuery($query);
        if (empty($results)) {
            return null;
        }

        /** @var UserGroup $entity */
        $entity = $this->dao->hydrate($this->entityClass, $results[0]);
        if (isset($results[0]['name'])) {
            $entity->setName($results[0]['name']);
        }
        $descriptions = $this->findDescriptions($id);
        $entity->setDescriptions($descriptions);

        return $entity;
    }

    /**
     * Busca todos os grupos com seus nomes traduzidos para o idioma especificado.
     */
    public function findAllWithLanguage(int $languageId): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName(), 'ug')
            ->leftJoin(DB_PREFIX . 'user_group_description', 'ugd', 'ug.id = ugd.user_group_id AND ugd.language_id = ' . (int)$languageId)
            ->orderBy('ug.id', 'ASC')
            ->select('ug.id', 'ug.permission', 'COALESCE(ugd.name, ug.name) AS name');

        $results = $this->dao->executeQuery($query);
        $entities = [];

        foreach ($results as $row) {
            /** @var UserGroup $entity */
            $entity = $this->dao->hydrate($this->entityClass, $row);
            if (isset($row['name'])) {
                $entity->setName($row['name']);
            }
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * Carrega todas as descrições/traduções de um grupo de usuários indexadas pelo language_id.
     */
    public function findDescriptions(int $userGroupId): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'user_group_description')
            ->where('user_group_id = ?', [$userGroupId])
            ->select('language_id', 'name');

        $results = $this->dao->executeQuery($query);
        $descriptions = [];

        if ($results) {
            foreach ($results as $row) {
                $descriptions[(int)$row['language_id']] = [
                    'name' => $row['name']
                ];
            }
        }

        return $descriptions;
    }

    /**
     * Salva/atualiza as traduções do grupo na tabela agsc_user_group_description.
     */
    public function saveDescriptions(int $userGroupId, array $namesByLanguage): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();

        foreach ($namesByLanguage as $languageId => $data) {
            $langId = (int)$languageId;
            $name = is_array($data) ? trim($data['name'] ?? '') : trim((string)$data);

            if ($langId <= 0 || empty($name)) {
                continue;
            }

            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "user_group_description` WHERE `user_group_id` = ? AND `language_id` = ?");
            $stmtCheck->execute([$userGroupId, $langId]);
            $exists = (int)$stmtCheck->fetchColumn() > 0;

            if ($exists) {
                $stmtUpdate = $conn->prepare("UPDATE `" . DB_PREFIX . "user_group_description` SET `name` = ? WHERE `user_group_id` = ? AND `language_id` = ?");
                $stmtUpdate->execute([$name, $userGroupId, $langId]);
            } else {
                $stmtInsert = $conn->prepare("INSERT INTO `" . DB_PREFIX . "user_group_description` (`user_group_id`, `language_id`, `name`) VALUES (?, ?, ?)");
                $stmtInsert->execute([$userGroupId, $langId, $name]);
            }
        }
    }

    /**
     * Exclui todas as descrições vinculadas a um grupo de usuários.
     */
    public function deleteDescriptions(int $userGroupId): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $stmt = $conn->prepare("DELETE FROM `" . DB_PREFIX . "user_group_description` WHERE `user_group_id` = ?");
        $stmt->execute([$userGroupId]);
    }
}