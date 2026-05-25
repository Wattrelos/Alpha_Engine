<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\Address;
use Alpha\Mappers\EntityMappers\AddressMapper;
use Alpha\Model\Domain\InterfaceEntity;

use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\ZoneRepository;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;

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
        return $this->getMapper()->search(['customer_id' => $customerId]);
    }

    /**
     * Obtém o endereço padrão de um determinado cliente (usado como preenchimento ágil no Checkout).
     *
     * @param int $customerId
     * @return Address|null
     */
    public function getDefaultAddress(int $customerId): ?Address
    {
        $addresses = $this->getMapper()->search([
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

    /**
     * Salva um endereço no banco de dados e gerencia a lógica de endereço padrão.
     */
    public function save(array $data, int $customerId): int
    {
        $addressId = (int)($data['id'] ?? $data['address_id'] ?? 0);
        $address = $addressId > 0 ? $this->find($addressId) : new Address();
        
        if (!$address) {
            $address = new Address();
        }

        if ($addressId > 0 && $address->getCustomerId() !== $customerId) {
            throw new \RuntimeException("Acesso negado ao editar endereço.");
        }

        \Alpha\Model\DataTransferObject\EntityMapper::fillEntity($address, $data);
        $address->setCustomerId($customerId);

        $isDefault = !empty($data['default']);
        $address->setDefault($isDefault);

        $savedId = $this->getMapper()->save($address);

        if ($isDefault) {
            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            $stmt = $conn->prepare("UPDATE `" . DB_PREFIX . "address` SET `default` = 0 WHERE customer_id = ? AND id != ?");
            $stmt->execute([$customerId, $savedId]);

            $customerRepo = $this->registry->get('alpha_repository_factory')->get(CustomerRepository::class);
            $customer = $customerRepo->find($customerId);
            if ($customer) {
                $customer->setAddressId($savedId);
                $customerRepo->updateProfile($customer);
            }
        }

        return $savedId;
    }

    /**
     * Deleta um endereço verificando pertencimento.
     */
    public function delete(int $addressId, int $customerId): void
    {
        $address = $this->find($addressId);
        if ($address && $address->getCustomerId() === $customerId) {
            $this->getMapper()->delete($addressId);
        }
    }

    /**
     * Valida os dados de cadastro de endereço e custom fields.
     */
    public function validate(array $data): array
    {
        $errors = [];
        $language = $this->registry->get('language');
        $language->load('account/address');

        if (!oc_validate_length($data['firstname'] ?? '', 1, 32)) {
            $errors['firstname'] = $language->get('error_firstname');
        }
        if (!oc_validate_length($data['lastname'] ?? '', 1, 32)) {
            $errors['lastname'] = $language->get('error_lastname');
        }
        if (!oc_validate_length($data['address_1'] ?? '', 3, 128)) {
            $errors['address_1'] = $language->get('error_address_1');
        }
        if (!oc_validate_length($data['city'] ?? '', 2, 128)) {
            $errors['city'] = $language->get('error_city');
        }

        $countryRepo = $this->registry->get('alpha_repository_factory')->get(CountryRepository::class);
        $country = $countryRepo->find((int)($data['country_id'] ?? 0));

        if ($country && $country->getPostcodeRequired() && !oc_validate_length($data['postcode'] ?? '', 2, 10)) {
            $errors['postcode'] = $language->get('error_postcode');
        }
        if (!$country) {
            $errors['country'] = $language->get('error_country');
        }

        $zoneRepo = $this->registry->get('alpha_repository_factory')->get(ZoneRepository::class);
        $zoneTotal = $zoneRepo->getTotalZonesByCountryId((int)($data['country_id'] ?? 0));

        if ($zoneTotal && empty($data['zone_id'])) {
            $errors['zone'] = $language->get('error_zone');
        }

        $customFieldRepo = $this->registry->get('alpha_repository_factory')->get(CustomFieldRepository::class);
        $customer = $this->registry->get('customer');
        $groupId = $customer->isLogged() ? $customer->getGroupId() : (int)$this->registry->get('config')->get('config_customer_group_id');
        
        $custom_fields = $customFieldRepo->getCustomFields($groupId);
        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'address') {
                if ($custom_field['required'] && empty($data['custom_field'][$custom_field['custom_field_id']])) {
                    $errors['custom_field_' . $custom_field['custom_field_id']] = sprintf($language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !oc_validate_regex($data['custom_field'][$custom_field['custom_field_id']] ?? '', $custom_field['validation'])) {
                    $errors['custom_field_' . $custom_field['custom_field_id']] = sprintf($language->get('error_regex'), $custom_field['name']);
                }
            }
        }

        return $errors;
    }

    /**
     * Valida as regras de negócio para deletar um endereço.
     */
    public function validateDelete(int $customerId, int $addressId): array
    {
        $errors = [];
        $language = $this->registry->get('language');
        $language->load('account/address');

        $address = $this->find($addressId);

        if (!$address || $address->getCustomerId() !== $customerId) {
            $errors['warning'] = $language->get('error_address');
            return $errors;
        }

        $addresses = $this->findByCustomerId($customerId);
        if (count($addresses) == 1) {
            $errors['warning'] = $language->get('error_delete');
        }

        $customer = $this->registry->get('customer');
        if ($customer->getAddressId() == $addressId) {
            $errors['warning'] = $language->get('error_default');
        }

        return $errors;
    }

    // BaseRepositoryInterface bindings
    public function findAll(): array {
        return $this->getMapper()->findAll();
    }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { 
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset); 
    }
    public function findOneBy(array $criteria): ?InterfaceEntity { 
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}