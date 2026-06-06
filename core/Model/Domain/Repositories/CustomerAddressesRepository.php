<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerAddressesMapper;
use Alpha\Model\Domain\Entities\Customer\CustomerAddresses;
use Alpha\Model\Domain\Entities\Geo\City;
use Alpha\Model\Domain\Entities\Geo\Zone;
use Alpha\Model\Domain\Entities\Geo\Country;
use Alpha\Model\Domain\InterfaceEntity;

class CustomerAddressesRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = CustomerAddressesMapper::class;

    protected function getMapper(): CustomerAddressesMapper
    {
        return $this->mapperFactory->get(CustomerAddressesMapper::class);
    }

    public function find(int $id): ?CustomerAddresses
    {
        return $this->getMapper()->findById($id);
    }

    /**
     * Busca todos os endereços de um cliente.
     *
     * @return CustomerAddresses[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->getMapper()->search(['customerId' => $customerId]);
    }

    /**
     * Retorna todos os endereços do cliente como array plano.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAddresses(int $customerId): array
    {
        $addresses = $this->findByCustomerId($customerId);
        return array_map(fn(CustomerAddresses $a) => $this->toDTO($a), $addresses);
    }

    /**
     * Converte uma entidade CustomerAddresses em array plano com todos os campos necessários.
     *
     * @return array<string, mixed>
     */
    private function toDTO(CustomerAddresses $address): array
    {
        $country = $address->getCountry();
        $zone    = $address->getZone();
        $city    = $address->getCity();

        // Verifica se é o endereço padrão do cliente
        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->container->get(CustomerRepository::class);
        $customer = $customerRepo->find($address->getCustomerId());
        $isDefault = $customer && (int)$customer->getAddressId() === (int)$address->getId();

        return [
            'address_id'   => $address->getId(),
            'customer_id'  => $address->getCustomerId(),
            'address_1'    => $address->getStreet(),
            'street'       => $address->getStreet(),
            'number'       => $address->getNumber(),
            'address_2'    => $address->getComplement(),
            'complement'   => $address->getComplement(),
            'neighborhood' => $address->getDistrict(),
            'postcode'     => $address->getPostalCode(),
            'city'         => $city ? $city->getName() : '',
            'city_id'      => $city ? $city->getId() : 0,
            'zone_id'      => $zone ? $zone->getId() : 0,
            'zone'         => $zone ? $zone->getName() : '',
            'zone_code'    => $zone ? $zone->getIsoCode() : '',
            'country_id'   => $country ? $country->getId() : 0,
            'country'      => $country ? $country->getName() : '',
            'default'      => $isDefault,
        ];
    }

    /**
     * Cria ou atualiza um endereço para o cliente.
     */
    public function save(array $data, int $customerId): int
    {
        $addressId = (int)($data['id'] ?? $data['address_id'] ?? 0);
        $address   = $addressId > 0 ? $this->find($addressId) : null;

        if ($address === null) {
            $address = new CustomerAddresses();
        } elseif ($address->getCustomerId() !== $customerId) {
            throw new \RuntimeException('Acesso negado: o endereço não pertence ao cliente informado.');
        }

        $address->setCustomerId($customerId);
        $address->setPostalCode($data['postcode'] ?? '');
        $address->setStreet($data['street'] ?? $data['address_1'] ?? '');
        $address->setNumber($data['number'] ?? '');
        $address->setComplement($data['complement'] ?? $data['address_2'] ?? null);
        $address->setDistrict($data['neighborhood'] ?? $data['district'] ?? null);

        // 1. Resolver Country (Default: 76 para Brasil)
        $countryId = (int)($data['country_id'] ?? 76);
        /** @var \Alpha\Mappers\EntityMappers\GeoCountryMapper $countryMapper */
        $countryMapper = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\GeoCountryMapper::class);
        $country = $countryMapper->findById($countryId);
        $address->setCountry($country);

        // 2. Resolver Zone
        $zoneId = 0;
        /** @var \Alpha\Mappers\EntityMappers\GeoZoneMapper $zoneMapper */
        $zoneMapper = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\GeoZoneMapper::class);
        if (isset($data['zone_id']) && is_numeric($data['zone_id']) && (int)$data['zone_id'] > 0) {
            $zoneId = (int)$data['zone_id'];
        } elseif (!empty($data['zone_id'])) {
            $code = strtoupper(trim((string)$data['zone_id']));
            if (!str_contains($code, '-')) {
                $code = 'BR-' . $code;
            }
            $zones = $zoneMapper->search(['isoCode' => $code]);
            if (!empty($zones)) {
                $zoneId = $zones[0]->getId();
            }
        }
        $zone = $zoneMapper->findById($zoneId);
        $address->setZone($zone);

        // 3. Resolver City
        $cityName = trim($data['city'] ?? '');
        if ($cityName !== '' && $zoneId > 0) {
            $city = $this->getOrCreateCity($cityName, $zoneId);
            $address->setCity($city);
        } else {
            $address->setCity(null);
        }

        // 4. Salvar endereço
        $savedId = $this->getMapper()->save($address);
        if ($savedId === null || $savedId <= 0) {
            throw new \RuntimeException("Alpha Engine: Falha ao salvar o endereço do cliente no banco de dados.");
        }

        // 5. Gerenciar endereço padrão
        $isDefault = !empty($data['default']);
        if ($isDefault) {
            /** @var CustomerRepository $customerRepo */
            $customerRepo = $this->container->get(CustomerRepository::class);
            $customer = $customerRepo->find($customerId);
            if ($customer) {
                $customer->setAddressId($savedId);
                $customerRepo->save($customer);
            }
        }

        return $savedId;
    }

    /**
     * Exclui um endereço.
     */
    public function delete(int $addressId, int $customerId): void
    {
        $address = $this->find($addressId);
        if ($address && $address->getCustomerId() === $customerId) {
            $this->getMapper()->delete($addressId);
        }
    }

    /**
     * Valida as regras de negócio para excluir um endereço.
     */
    public function validateDelete(int $customerId, int $addressId): array
    {
        $address = $this->find($addressId);

        if (!$address || $address->getCustomerId() !== $customerId) {
            return ['warning' => 'Endereço não encontrado ou não pertence à sua conta.'];
        }

        $addresses = $this->findByCustomerId($customerId);
        if (count($addresses) === 1) {
            return ['warning' => 'Não é possível excluir o único endereço cadastrado.'];
        }

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->container->get(CustomerRepository::class);
        $customer = $customerRepo->find($customerId);
        if ($customer && (int)$customer->getAddressId() === $addressId) {
            return ['warning' => 'Não é possível excluir o endereço padrão. Defina outro como padrão primeiro.'];
        }

        return [];
    }

    /**
     * Busca ou cria a cidade na tabela agsc_geo_cities.
     */
    private function getOrCreateCity(string $cityName, int $zoneId): ?City
    {
        /** @var \Alpha\Mappers\EntityMappers\GeoCityMapper $cityMapper */
        $cityMapper = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\GeoCityMapper::class);
        $cities = $cityMapper->search([
            'name' => $cityName,
            'zoneId' => $zoneId
        ]);

        if (!empty($cities)) {
            return $cities[0];
        }

        $cityId = crc32(strtolower($cityName) . '_' . $zoneId);
        
        $city = new City();
        $city->setId($cityId);
        $city->setName($cityName);
        
        /** @var \Alpha\Mappers\EntityMappers\GeoZoneMapper $zoneMapper */
        $zoneMapper = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\GeoZoneMapper::class);
        $zone = $zoneMapper->findById($zoneId);
        $city->setZone($zone);
        $city->setIsServed(true);

        $cityMapper->save($city);

        return $city;
    }

    // BaseRepositoryInterface bindings
    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}
