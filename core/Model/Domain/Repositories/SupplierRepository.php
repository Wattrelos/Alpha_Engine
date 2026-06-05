<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\SupplierMapper;
use Alpha\Mappers\EntityMappers\AddressesMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Entities\Supplier\Supplier;
use Alpha\Model\Domain\Entities\Supplier\Addresses;

/**
 * SupplierRepository - Autoridade de Domínio para Fornecedores.
 */
class SupplierRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = SupplierMapper::class;

    /**
     * Busca um fornecedor pelo ID e carrega seu endereço
     */
    public function find(int $id): ?InterfaceEntity
    {
        /** @var SupplierMapper $supplierMapper */
        $supplierMapper = $this->mapperFactory->get(SupplierMapper::class);
        /** @var Supplier|null $supplier */
        $supplier = $supplierMapper->findById($id);

        if ($supplier) {
            /** @var AddressesMapper $addressMapper */
            $addressMapper = $this->mapperFactory->get(AddressesMapper::class);
            $results = $addressMapper->search(['supplierId' => $id]);
            if (!empty($results)) {
                $supplier->setAddresses($results[0]);
            }
        }

        return $supplier;
    }

    public function findAll(): array
    {
        /** @var SupplierMapper $supplierMapper */
        $supplierMapper = $this->mapperFactory->get(SupplierMapper::class);
        return $supplierMapper->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        /** @var SupplierMapper $supplierMapper */
        $supplierMapper = $this->mapperFactory->get(SupplierMapper::class);
        return $supplierMapper->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->findBy($criteria, null, 1);
        return $results ? $results[0] : null;
    }

    /**
     * Salva ou atualiza o fornecedor e seu endereço agregado de forma transacional.
     */
    public function save(Supplier $supplier): ?int
    {
        /** @var SupplierMapper $supplierMapper */
        $supplierMapper = $this->mapperFactory->get(SupplierMapper::class);
        /** @var AddressesMapper $addressMapper */
        $addressMapper = $this->mapperFactory->get(AddressesMapper::class);

        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $managedTransaction = false;

        try {
            if (!$conn->inTransaction()) {
                $conn->beginTransaction();
                $managedTransaction = true;
            }

            // Salva fornecedor
            $supplierId = $supplierMapper->save($supplier);
            if (!$supplierId) {
                throw new \Exception("Erro ao salvar o fornecedor.");
            }
            $supplier->setId($supplierId);

            // Salva endereço se existir
            $address = $supplier->getAddresses();
            if ($address) {
                $address->setSupplierId($supplierId);
                $addressId = $addressMapper->save($address);
                if (!$addressId) {
                    throw new \Exception("Erro ao salvar o endereço do fornecedor.");
                }
                $address->setId($addressId);
            }

            if ($managedTransaction) {
                $conn->commit();
            }

            return $supplierId;
        } catch (\Throwable $e) {
            if ($managedTransaction && $conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Remove o fornecedor e seu endereço por integridade referencial.
     */
    public function delete(int $id): bool
    {
        /** @var SupplierMapper $supplierMapper */
        $supplierMapper = $this->mapperFactory->get(SupplierMapper::class);
        /** @var AddressesMapper $addressMapper */
        $addressMapper = $this->mapperFactory->get(AddressesMapper::class);

        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        $managedTransaction = false;

        try {
            if (!$conn->inTransaction()) {
                $conn->beginTransaction();
                $managedTransaction = true;
            }

            // Remove os endereços associados ao fornecedor
            $addresses = $addressMapper->search(['supplierId' => $id]);
            foreach ($addresses as $address) {
                $addressMapper->delete($address->getId());
            }

            // Remove o fornecedor
            $result = $supplierMapper->delete($id);

            if ($managedTransaction) {
                $conn->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            if ($managedTransaction && $conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Retorna os dados formatados para a listagem
     */
    public function getIndexData(array $filters = []): array
    {
        $page = (int)($filters['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = (int)($filters['limit'] ?? 15);

        $criteria = [];
        if (!empty($filters['filter_name'])) {
            $criteria['companyName'] = '%' . $filters['filter_name'] . '%';
        }
        if (!empty($filters['filter_tax_id'])) {
            $criteria['taxId'] = '%' . $filters['filter_tax_id'] . '%';
        }

        /** @var SupplierMapper $supplierMapper */
        $supplierMapper = $this->mapperFactory->get(SupplierMapper::class);
        $result = $supplierMapper->paginate($criteria, $page, $limit, ['companyName' => 'ASC']);

        return [
            'data'         => $result['data'],
            'total'        => $result['total'],
            'total_pages'  => $result['total_pages'],
            'current_page' => $page,
            'limit'        => $limit
        ];
    }
}
