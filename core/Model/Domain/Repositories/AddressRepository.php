<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\Address;
use Alpha\Mappers\EntityMappers\AddressMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * AddressRepository
 * Centraliza buscas seguras referentes aos endereços de clientes.
 */
class AddressRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): AddressMapper
    {
        return $this->mapperFactory->get(AddressMapper::class);
    }

    public function find(int $id): ?Address
    {
        return $this->getMapper()->findById($id);
    }

    /**
     * Busca a carteira de endereços completa de um cliente.
     *
     * @param int $customerId
     * @return Address[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->getMapper()->findBy(['customer_id' => $customerId]);
    }

    /**
     * Obtém o endereço padrão de um determinado cliente (usado como preenchimento ágil no Checkout).
     *
     * @param int $customerId
     * @return Address|null
     */
    public function getDefaultAddress(int $customerId): ?Address
    {
        $addresses = $this->getMapper()->findBy([
            'customer_id' => $customerId,
            'default'     => true
        ]);

        // Retorna o primeiro endereço marcado como default, ou null se não houver
        return $addresses[0] ?? null;
    }

    /**
     * [LEGACY DTO] Retorna o endereço formatado como Array Plano para compatibilidade com o Checkout.
     */
    public function getAddress(int $addressId): array
    {
        $address = $this->find($addressId);
        return $address ? $this->toLegacyDTO($address) : [];
    }

    /**
     * [LEGACY DTO] Retorna todos os endereços do cliente formatados.
     */
    public function getAddresses(int $customerId): array
    {
        return array_map(fn($a) => $this->toLegacyDTO($a), $this->findByCustomerId($customerId));
    }

    private function toLegacyDTO(Address $address): array
    {
        $country = $address->getCountry();
        $zone = $address->getZone();

        return [
            'address_id'     => $address->getId(),
            'customer_id'    => $address->getCustomerId(),
            'firstname'      => $address->getFirstname(),
            'lastname'       => $address->getLastname(),
            'company'        => $address->getCompany(),
            'address_1'      => $address->getAddress1(),
            'number'         => $address->getNumber(),
            'address_2'      => $address->getAddress2(),
            'neighborhood'   => $address->getNeighborhood(),
            'postcode'       => $address->getPostcode(),
            'city'           => $address->getCity(),
            'zone_id'        => $address->getZoneId(),
            'zone'           => $zone ? $zone->getName() : '',
            'zone_code'      => $zone ? $zone->getCode() : '',
            'country_id'     => $address->getCountryId(),
            'country'        => $country ? $country->getName() : '',
            'iso_code_2'     => $country ? $country->getIsoCode2() : '',
            'iso_code_3'     => $country ? $country->getIsoCode3() : '',
            'address_format' => $country ? $country->getAddressFormat() : '',
            'custom_field'   => $address->getCustomFieldArray(), // Resolução nativa via Domínio!
            'default'        => $address->isDefault()
        ];
    }

    // BaseRepositoryInterface bindings
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { 
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset); 
    }
    public function findOneBy(array $criteria): ?InterfaceEntity { 
        return $this->getMapper()->findOneBy($criteria); 
    }
}