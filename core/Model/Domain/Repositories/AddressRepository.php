<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\AddressMapper;

/**
 * Class AddressRepository
 * 
 * Camada de domínio para a gestão de endereços de clientes.
 * Encapsula a validação de propriedade (ID do cliente) e a 
 * resolução de formatos postais (AddressFormat) de cada região.
 */
class AddressRepository extends AbstractRepository {

    public function __construct(AddressMapper $mapper) {
        parent::__construct($mapper);
    }

    /**
     * Retorna um endereço específico validando rigorosamente a posse do cliente.
     * Previne vulnerabilidades de Insecure Direct Object Reference (IDOR).
     */
    public function getAddress(int $addressId, int $customerId): ?array {
        /** @var AddressMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->getAddress($addressId, $customerId);
    }

    /**
     * Retorna todos os endereços vinculados a um cliente de forma segura.
     */
    public function getAddresses(int $customerId): array {
        /** @var AddressMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->getAddresses($customerId);
    }

    /**
     * Substitui as tags do formato de endereço pelos dados reais do cliente.
     * Resolve o layout postal de acordo com as regras extraídas do AddressFormat.
     */
    public function formatAddress(array $addressData): string {
        // Fallback robusto caso o país não possua formato cadastrado
        $format = $addressData['address_format'] ?: "{firstname} {lastname}\n{company}\n{address_1}\n{address_2}\n{city} {postcode}\n{zone}\n{country}";

        $find = [
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        ];

        $replace = [
            'firstname' => $addressData['firstname'] ?? '',
            'lastname'  => $addressData['lastname'] ?? '',
            'company'   => $addressData['company'] ?? '',
            'address_1' => $addressData['address_1'] ?? '',
            'address_2' => $addressData['address_2'] ?? '',
            'city'      => $addressData['city'] ?? '',
            'postcode'  => $addressData['postcode'] ?? '',
            'zone'      => $addressData['zone'] ?? '',
            'zone_code' => $addressData['zone_code'] ?? '',
            'country'   => $addressData['country'] ?? ''
        ];

        $formatted = str_replace($find, $replace, $format);
        
        // Regex para limpar linhas vazias deixadas por campos ausentes (ex: company, address_2)
        $formatted = preg_replace('/^[ \t]*[\r\n]+/m', '', $formatted);
        
        return trim($formatted);
    }
}