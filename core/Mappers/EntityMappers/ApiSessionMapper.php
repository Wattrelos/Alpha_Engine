<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Entities\ApiSession;

/**
 * ApiSessionMapper - Alpha Engine
 * 
 * Gerencia a persistência de sessões de API, validando tokens e limpando lixo.
 */
class ApiSessionMapper extends BaseMapper
{
    protected string $tableName = 'api_session';

    /**
     * Alpha Engine: Autenticação de API baseada em usuário e chave.
     */
    public function login(string $username, string $key): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'api', 'a')
            ->leftJoin(DB_PREFIX . 'api_ip', 'ai', 'a.api_id = ai.api_id')
            ->where("a.username = ?", [$username])
            ->where("a.key = ?", [$key])
            ->select('a.*', 'ai.ip');

        $result = $this->dao->executeQuery($query);
        return $result[0] ?? [];
    }

    /**
     * Alpha Engine: Valida token e IP de sessão da API.
     */
    public function getApiByToken(string $token, string $ip): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'api', 'a')
            ->leftJoin(DB_PREFIX . 'api_session', 'as', 'a.api_id = as.api_id')
            ->leftJoin(DB_PREFIX . 'api_ip', 'ai', 'a.api_id = ai.api_id')
            ->where("a.status = ?", ['1'])
            ->where("as.session_id = ?", [$token])
            ->where("ai.ip = ?", [$ip])
            ->select('DISTINCT a.*', 'as.session_id', 'as.date_modified', 'ai.ip');

        $result = $this->dao->executeQuery($query);
        return $result[0] ?? [];
    }

    /**
     * Alpha Engine: Recupera sessões ativas da API baseada na validade.
     */
    public function getSessions(int $api_id): array
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("TIMESTAMPADD(HOUR, 1, `date_modified`) < NOW()", [])
            ->where("api_id = ?", [$api_id])
            ->select('*');

        return $this->dao->executeQuery($query);
    }

    /**
     * Alpha Engine: Mantém a sessão ativa atualizando o timestamp.
     */
    public function updateSession(string $api_session_id): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "UPDATE `" . $this->getFullTableName() . "` SET `date_modified` = NOW() WHERE `api_session_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([(int)$api_session_id]);
    }

    /**
     * Alpha Engine: Limpa permanentemente as sessões inativas (Garbage Collection).
     */
    public function cleanSessions(): void
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $sql = "DELETE FROM `" . $this->getFullTableName() . "` WHERE TIMESTAMPADD(HOUR, 1, `date_modified`) < NOW()";
        $conn->query($sql);
    }

    /**
     * Localiza uma sessão de API pelo seu token de acesso.
     *
     * @param string $token
     * @return ApiSession|null
     */
    public function getByToken(string $token): ?ApiSession
    {
        $query = (new QueryBuilder())
            ->from($this->getFullTableName())
            ->where("session_id = ?", [$token])
            ->select('api_session_id AS id');

        $results = $this->dao->executeQuery($query);
        if ($results) {
            $entity = new ApiSession();
            $entity->setId((int)$results[0]['id']);
            $hydrated = $this->dao->read($entity);
            return $hydrated ? $hydrated[0] : null;
        }
        return null;
    }

    /**
     * Salva ou atualiza os dados da sessão de API.
     */
    public function save(InterfaceEntity $entity): ?int
    {
        return ($entity->getId() > 0) ? $this->dao->update($entity) : $this->dao->create($entity);
    }
}