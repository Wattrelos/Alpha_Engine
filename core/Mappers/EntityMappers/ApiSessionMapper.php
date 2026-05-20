<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\AbstractMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Entities\ApiSession;

/**
 * ApiSessionMapper - Alpha Engine
 * 
 * Gerencia a persistência de sessões de API.
 */
class ApiSessionMapper extends AbstractMapper
{
    protected string $table = 'api_session';

    /**
     * Localiza uma sessão de API pelo seu token de acesso.
     *
     * @param string $token
     * @return ApiSession|null
     */
    public function getByToken(string $token): ?ApiSession
    {
        return $this->findOneBy(['sessionToken' => $token]);
    }

    /**
     * Salva ou atualiza os dados da sessão de API.
     */
    public function save(InterfaceEntity $entity): ?int
    {
        return ($entity->getId() > 0) ? $this->dao->update($entity) : $this->dao->create($entity);
    }
}