<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\Customer\CustomerToken;

/**
 * CustomerTokenRepository
 * Gerencia os tokens temporários para recuperação de senhas e ativação de contas de clientes.
 */
class CustomerTokenRepository extends AbstractRepository
{
    /**
     * Busca um token específico pelo código gerado.
     * 
     * @param string $code
     * @return CustomerToken|null
     */
    public function findByCode(string $code): ?CustomerToken
    {
        $results = $this->mapper->search(['code' => $code]);
        return $results[0] ?? null;
    }

    /**
     * Limpa todos os tokens existentes para um cliente (útil após alterar a senha com sucesso).
     * 
     * @param int $customerId
     * @return void
     */
    public function clearTokensForCustomer(int $customerId): void
    {
        $tokens = $this->mapper->search(['customerId' => $customerId]);
        
        foreach ($tokens as $token) {
            $this->mapper->delete($token->getId());
        }
    }
}
