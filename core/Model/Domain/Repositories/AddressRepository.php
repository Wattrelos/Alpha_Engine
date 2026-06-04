<?php

declare(strict_types=1);

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\Address;
use Alpha\Mappers\EntityMappers\AddressMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\ZoneRepository;

/**
 * AddressRepository
 *
 * Autoridade de domínio para o livro de endereços dos clientes.
 * Centraliza busca, persistência e validação de regras de negócio para endereços.
 *
 * Nota: funções legadasdo código legado (oc_validate_length, oc_validate_regex,
 * CustomFieldRepository) foram removidas e substituídas por implementações
 * puras em PHP 8+.
 */
class AddressRepository extends AbstractRepository implements BaseRepositoryInterface
{
    // ─────────────────────────────────────────────────────────────────────────
    // Consultas
    // ─────────────────────────────────────────────────────────────────────────

    protected function getMapper(): AddressMapper
    {
        return $this->mapperFactory->get(AddressMapper::class);
    }

    public function find(int $id): ?Address
    {
        return $this->getMapper()->findById($id);
    }

    /**
     * Busca todos os endereços de um cliente.
     *
     * @return Address[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->getMapper()->search(['customer_id' => $customerId]);
    }

    /**
     * Retorna o endereço marcado como padrão do cliente, ou null se não houver.
     */
    public function getDefaultAddress(int $customerId): ?Address
    {
        $addresses = $this->getMapper()->search([
            'customer_id' => $customerId,
            'default'     => true,
        ]);

        return $addresses[0] ?? null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DTOs planos (compatibilidade com Checkout e templates Twig)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retorna um endereço como array plano (compatível com templates Twig e Checkout).
     */
    public function getAddress(int $addressId): array
    {
        $address = $this->find($addressId);
        return $address ? $this->toDTO($address) : [];
    }

    /**
     * Retorna todos os endereços do cliente como array plano.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAddresses(int $customerId): array
    {
        return array_map(fn(Address $a) => $this->toDTO($a), $this->findByCustomerId($customerId));
    }

    /**
     * Converte uma entidade Address em array plano com todos os campos necessários
     * para os templates Twig e para a camada de Checkout.
     *
     * @return array<string, mixed>
     */
    private function toDTO(Address $address): array
    {
        $country = $address->getCountry();
        $zone    = $address->getZone();

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
            'zone'           => $zone    ? $zone->getName()          : '',
            'zone_code'      => $zone    ? $zone->getCode()          : '',
            'country_id'     => $address->getCountryId(),
            'country'        => $country ? $country->getName()       : '',
            'iso_code_2'     => $country ? $country->getIsoCode2()   : '',
            'iso_code_3'     => $country ? $country->getIsoCode3()   : '',
            'address_format' => $country ? $country->getAddressFormat() : '',
            'custom_field'   => $address->getCustomFieldArray(),
            'default'        => $address->isDefault(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Persistência
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cria ou atualiza um endereço para o cliente.
     *
     * Normaliza as chaves `address_1`/`address_2` para `address1`/`address2`
     * antes de repassar ao EntityHydrator, pois o hydrator resolve setters
     * pelo nome camelCase do campo (setAddress1 → 'address1'), mas os formulários
     * e sessões do Checkout usam a nomenclatura com underscore numérico.
     *
     * Se `$data['address_id']` ou `$data['id']` for fornecido, faz UPDATE;
     * caso contrário, INSERT.
     *
     * @throws \RuntimeException se `address_id` pertencer a outro cliente.
     */
    public function save(array $data, int $customerId): int
    {
        // ── Normalização de chaves (snake_case legado → camelCase da entidade) ──
        if (isset($data['address_1']) && !isset($data['address1'])) {
            $data['address1'] = $data['address_1'];
        }
        if (isset($data['address_2']) && !isset($data['address2'])) {
            $data['address2'] = $data['address_2'];
        }

        $addressId = (int)($data['id'] ?? $data['address_id'] ?? 0);
        $address   = $addressId > 0 ? $this->find($addressId) : null;

        if ($address === null) {
            $address = new Address();
        } elseif ($address->getCustomerId() !== $customerId) {
            throw new \RuntimeException('Acesso negado: o endereço não pertence ao cliente informado.');
        }

        \Alpha\Support\EntityHydrator::fillEntity($address, $data);
        $address->setCustomerId($customerId);

        $isDefault = !empty($data['default']);
        $address->setDefault($isDefault);

        $savedId = $this->getMapper()->save($address);

        // ── Gerência de endereço padrão ──────────────────────────────────────
        // Desmarca os demais endereços e atualiza o ponteiro no perfil do cliente.
        if ($isDefault) {
            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            $stmt = $conn->prepare(
                'UPDATE `' . DB_PREFIX . 'address` SET `default` = 0 WHERE customer_id = ? AND id != ?'
            );
            $stmt->execute([$customerId, $savedId]);

            /** @var CustomerRepository $customerRepo */
            $customerRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(CustomerRepository::class);
            $customer = $customerRepo->find($customerId);
            if ($customer) {
                $customer->setAddressId($savedId);
                $customerRepo->updateProfile($customer);
            }
        }

        return $savedId;
    }

    /**
     * Exclui um endereço verificando o pertencimento ao cliente.
     * Não lança exceção se o endereço não existir ou não pertencer ao cliente —
     * use validateDelete() antes para obter mensagens de erro detalhadas.
     */
    public function delete(int $addressId, int $customerId): void
    {
        $address = $this->find($addressId);
        if ($address && $address->getCustomerId() === $customerId) {
            $this->getMapper()->delete($addressId);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validação
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Valida os dados do formulário de endereço.
     *
     * Retorna um array associativo campo → mensagem de erro.
     * Array vazio significa dados válidos.
     *
     * Substitui as funções legadas oc_validate_length() e oc_validate_regex()
     * por implementações puras em PHP 8+.
     *
     * @param  array<string, mixed> $data
     * @return array<string, string>
     */
    public function validate(array $data): array
    {
        $errors = [];

        // ── Dados pessoais ────────────────────────────────────────────────────
        if (!$this->validateLength($data['firstname'] ?? '', 1, 32)) {
            $errors['firstname'] = 'O nome deve ter entre 1 e 32 caracteres.';
        }
        if (!$this->validateLength($data['lastname'] ?? '', 1, 32)) {
            $errors['lastname'] = 'O sobrenome deve ter entre 1 e 32 caracteres.';
        }

        // ── Endereço ─────────────────────────────────────────────────────────
        if (!$this->validateLength($data['address_1'] ?? $data['address1'] ?? '', 3, 128)) {
            $errors['address_1'] = 'O logradouro deve ter entre 3 e 128 caracteres.';
        }
        if (!$this->validateLength($data['city'] ?? '', 2, 128)) {
            $errors['city'] = 'A cidade deve ter entre 2 e 128 caracteres.';
        }

        // ── País e CEP ────────────────────────────────────────────────────────
        $countryId   = (int)($data['country_id'] ?? 0);
        $countryRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(CountryRepository::class);
        $country     = $countryRepo->find($countryId);

        if (!$country) {
            $errors['country_id'] = 'Selecione um país válido.';
        } elseif ($country->getPostcodeRequired()) {
            $postcode = preg_replace('/\D/', '', $data['postcode'] ?? '');
            if (!$this->validateLength($postcode, 8, 8)) {
                $errors['postcode'] = 'Informe um CEP válido com 8 dígitos.';
            }
        }

        // ── Estado / Zona ─────────────────────────────────────────────────────
        if ($countryId > 0) {
            $zoneRepo  = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(ZoneRepository::class);
            $zoneTotal = $zoneRepo->getTotalZonesByCountryId($countryId);

            if ($zoneTotal > 0 && empty($data['zone_id'])) {
                $errors['zone_id'] = 'Selecione um estado válido.';
            }
        }

        return $errors;
    }

    /**
     * Valida as regras de negócio para excluir um endereço.
     *
     * Retorna array vazio se a exclusão for permitida.
     * Retorna array com chave 'warning' se não for.
     *
     * @return array<string, string>
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
        $customerRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(CustomerRepository::class);
        $customer = $customerRepo->find($customerId);
        if ($customer && (int)$customer->getAddressId() === $addressId) {
            return ['warning' => 'Não é possível excluir o endereço padrão. Defina outro como padrão primeiro.'];
        }

        return [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BaseRepositoryInterface — bindings obrigatórios
    // ─────────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifica se o comprimento de uma string (em caracteres multibyte) está
     * dentro do intervalo [min, max].
     *
     * Substitui oc_validate_length()do código legado.
     */
    private function validateLength(string $value, int $min, int $max): bool
    {
        $len = mb_strlen(trim($value), 'UTF-8');
        return $len >= $min && $len <= $max;
    }
}
