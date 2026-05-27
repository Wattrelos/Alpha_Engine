<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UserLoginMapper;

/**
 * UserLoginRepository
 * Gerencia os históricos e restrições de tentativa de login no Backoffice.
 */
class UserLoginRepository extends AbstractRepository
{
    private UserLoginMapper $mapper;

    public function __construct(UserLoginMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    protected function getMapper(): UserLoginMapper
    {
        return $this->mapper;
    }

    /**
     * Retorna a quantidade total de logins recentes para prevenir ataques de força bruta.
     *
     * @param int $userId
     * @return int
     */
    public function countRecentLogins(int $userId): int
    {
        $logins = $this->getMapper()->search(['userId' => $userId]);
        return count($logins);
    }

    /**
     * Limpa o histórico de login após um acesso bem-sucedido ou expiração do limite de tempo.
     *
     * @param int $userId
     */
    public function clearLoginAttempts(int $userId): void
    {
        // Alpha Engine: Dívida técnica resolvida. O Repositório assume a deleção de histórico via Mapper.
        $logins = $this->getMapper()->search(['userId' => $userId]);
        foreach ($logins as $login) {
            $this->getMapper()->delete($login);
        }
    }
}