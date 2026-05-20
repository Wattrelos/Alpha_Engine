<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\ApiSession;

/**
 * ApiSessionRepository - Orquestra o ciclo de vida das sessões de API.
 */
class ApiSessionRepository extends AbstractRepository
{
    /**
     * Define o Mapper principal para segurança de API.
     */
    protected function getMapper()
    {
        return $this->mapperFactory->get('Security/ApiSession');
    }

    /**
     * Recupera uma sessão de API pelo token.
     */
    public function getByToken(string $token): ?ApiSession
    {
        return $this->findOneBy(['sessionToken' => $token]);
    }

    /**
     * Cria ou atualiza uma sessão de API.
     */
    public function saveSession(int $api_id, string $token, string $ip): void
    {
        $session = $this->getByToken($token) ?? new ApiSession();
        
        $session->setApiId($api_id)
                ->setSessionToken($token)
                ->setIp($ip);

        if (!$session->getId()) {
            $session->setDateAdded(date('Y-m-d H:i:s'));
        }
        
        $session->setDateModified(date('Y-m-d H:i:s'));

        $this->getMapper()->save($session);
    }

    /**
     * Remove sessões expiradas ou específicas.
     */
    public function deleteSession(string $token): void
    {
        $this->getMapper()->deleteByToken($token);
    }
}