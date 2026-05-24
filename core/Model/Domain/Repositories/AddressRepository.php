<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\Address;

/**
 * AddressRepository
 * Centraliza buscas seguras referentes aos endereços de clientes.
 */
class AddressRepository extends AbstractRepository
{
    /**
     * Busca a carteira de endereços completa de um cliente.
     *
     * @param int $customerId
     * @return Address[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->mapper->search(['customerId' => $customerId]);
    }

    /**
     * Obtém o endereço padrão de um determinado cliente (usado como preenchimento ágil no Checkout).
     *
     * @param int $customerId
     * @return Address|null
     */
    public function getDefaultAddress(int $customerId): ?Address
    {
        $addresses = $this->mapper->search([
            'customerId' => $customerId,
            'default'    => true
        ]);

        // Retorna o primeiro endereço marcado como default, ou null se não houver
        return $addresses[0] ?? null;
    }
}