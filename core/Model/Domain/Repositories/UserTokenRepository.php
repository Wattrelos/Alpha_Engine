<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\UserToken;

/**
 * UserTokenRepository
 * Gerencia os tokens temporários para recuperação de senhas do painel de administração.
 */
class UserTokenRepository extends AbstractRepository
{
    /**
     * Busca um token específico pelo código gerado.
     * 
     * @param string $code
     * @return UserToken|null
     */
    public function findByCode(string $code): ?UserToken
    {
        return $this->mapper->findOneBy(['code' => $code]);
    }

    /**
     * Limpa todos os tokens existentes para um usuário administrador.
     * 
     * @param int $userId
     * @return void
     */
    public function clearTokensForUser(int $userId): void
    {
        $tokens = $this->mapper->search(['userId' => $userId]);
        
        foreach ($tokens as $token) {
            $this->mapper->delete($token->getId());
        }
    }
}
