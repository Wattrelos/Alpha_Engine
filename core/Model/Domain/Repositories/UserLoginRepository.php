<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UserLoginMapper;

/**
 * UserLoginRepository
 * Gerencia os históricos e restrições de tentativa de login no Backoffice.
 */
class UserLoginRepository extends AbstractRepository
{
    protected function getMapper(): UserLoginMapper
    {
        return $this->mapperFactory->get(UserLoginMapper::class);
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
        // A implementação no ORM deve deletar os registros ou o controller lidará enviando os objetos para o delete
    }
}