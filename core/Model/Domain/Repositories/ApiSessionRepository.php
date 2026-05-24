<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\ApiSession;
use Alpha\Mappers\EntityMappers\ApiSessionMapper;

/**
 * ApiSessionRepository - Orquestra o ciclo de vida das sessões de API.
 */
class ApiSessionRepository extends AbstractRepository
{
    /**
     * Define o Mapper principal para segurança de API.
     */
    protected function getMapper(): ApiSessionMapper
    {
        return $this->mapperFactory->get(ApiSessionMapper::class);
    }

    /**
     * Delega a autenticação de API baseada em usuário e chave.
     */
    public function login(string $username, string $key): array
    {
        return $this->getMapper()->login($username, $key);
    }

    /**
     * Delega a validação de token e IP da sessão da API.
     */
    public function getApiByToken(string $token, string $ip): array
    {
        return $this->getMapper()->getApiByToken($token, $ip);
    }

    /**
     * Recupera sessões ativas da API baseada na validade.
     */
    public function getSessions(int $api_id): array
    {
        return $this->getMapper()->getSessions($api_id);
    }

    /**
     * Mantém a sessão ativa atualizando o timestamp.
     */
    public function updateSession(string $api_session_id): void
    {
        $this->getMapper()->updateSession($api_session_id);
    }

    /**
     * Limpa permanentemente as sessões inativas (Garbage Collection).
     */
    public function cleanSessions(): void
    {
        $this->getMapper()->cleanSessions();
    }

    /**
     * Recupera uma sessão de API pelo token.
     */
    public function getByToken(string $token): ?ApiSession
    {
        return $this->findOneBy(['tokenSession' => $token]);
    }

    /**
     * Cria ou atualiza uma sessão de API.
     */
    public function saveSession(int $api_id, string $token, string $ip): void
    {
        $session = $this->getByToken($token) ?? new ApiSession();
        
        $session->setApiId($api_id)
                ->setTokenSession($token)
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
