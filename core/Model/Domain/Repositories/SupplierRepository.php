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

            // Busca os contatos associados via pivot
            $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
            $stmtContacts = $conn->prepare("
                SELECT c.*, scm.manufacturer_id, m.name AS manufacturer_name
                FROM `" . DB_PREFIX . "supplier_contact_manufacturer` scm
                JOIN `" . DB_PREFIX . "contact` c ON scm.contact_id = c.id
                LEFT JOIN `" . DB_PREFIX . "manufacturer` m ON scm.manufacturer_id = m.id
                WHERE scm.supplier_id = ?
            ");
            $stmtContacts->execute([$id]);
            $contacts = $stmtContacts->fetchAll(\PDO::FETCH_ASSOC);
            $supplier->setContacts($contacts);
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

            // Salva contatos se existirem
            $existingContactIds = [];
            $stmtExisting = $conn->prepare("SELECT contact_id FROM `" . DB_PREFIX . "supplier_contact_manufacturer` WHERE supplier_id = ?");
            $stmtExisting->execute([$supplierId]);
            $existingContactIds = $stmtExisting->fetchAll(\PDO::FETCH_COLUMN);

            // Limpa associação anterior
            $conn->prepare("DELETE FROM `" . DB_PREFIX . "supplier_contact_manufacturer` WHERE supplier_id = ?")->execute([$supplierId]);

            $keptContactIds = [];
            $contacts = $supplier->getContacts();
            foreach ($contacts as $contactData) {
                $contactId = isset($contactData['id']) ? (int)$contactData['id'] : 0;
                $name = trim($contactData['name'] ?? '');
                $email = trim($contactData['email'] ?? '');
                $phone = trim($contactData['phone'] ?? '');
                $position = trim($contactData['position'] ?? '');
                $isActive = isset($contactData['is_active']) ? (int)$contactData['is_active'] : 1;
                $manufacturerId = isset($contactData['manufacturer_id']) ? (int)$contactData['manufacturer_id'] : 0;

                if (empty($name)) {
                    continue;
                }

                if ($contactId > 0) {
                    // Update
                    $stmtUpd = $conn->prepare("
                        UPDATE `" . DB_PREFIX . "contact` 
                        SET name = ?, email = ?, phone = ?, position = ?, is_active = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmtUpd->execute([$name, $email, $phone, $position, $isActive, $contactId]);
                    $keptContactIds[] = $contactId;
                } else {
                    // Insert
                    $stmtIns = $conn->prepare("
                        INSERT INTO `" . DB_PREFIX . "contact` (
                            name, email, phone, position, is_active, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmtIns->execute([$name, $email, $phone, $position, $isActive]);
                    $contactId = (int)$conn->lastInsertId();
                    $keptContactIds[] = $contactId;
                }

                // Relacionamento (somente se houver fabricante selecionado)
                if ($manufacturerId > 0) {
                    $stmtRel = $conn->prepare("
                        INSERT INTO `" . DB_PREFIX . "supplier_contact_manufacturer` (
                            supplier_id, contact_id, manufacturer_id, created_at
                        ) VALUES (?, ?, ?, NOW())
                    ");
                    $stmtRel->execute([$supplierId, $contactId, $manufacturerId]);
                }
            }

            // Remove contatos órfãos
            $orphans = array_diff($existingContactIds, $keptContactIds);
            if (!empty($orphans)) {
                $placeholders = implode(',', array_fill(0, count($orphans), '?'));
                $stmtDelOrphans = $conn->prepare("DELETE FROM `" . DB_PREFIX . "contact` WHERE id IN ($placeholders)");
                $stmtDelOrphans->execute(array_values($orphans));
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

            // Busca os contatos associados ao fornecedor antes de deletá-lo
            $stmtContacts = $conn->prepare("SELECT contact_id FROM `" . DB_PREFIX . "supplier_contact_manufacturer` WHERE supplier_id = ?");
            $stmtContacts->execute([$id]);
            $contactIds = $stmtContacts->fetchAll(\PDO::FETCH_COLUMN);

            // Remove os endereços associados ao fornecedor
            $addresses = $addressMapper->search(['supplierId' => $id]);
            foreach ($addresses as $address) {
                $addressMapper->delete($address->getId());
            }

            // Remove o fornecedor
            $result = $supplierMapper->delete($id);

            // Remove os contatos da tabela contact
            if (!empty($contactIds)) {
                $placeholders = implode(',', array_fill(0, count($contactIds), '?'));
                $stmtDelContacts = $conn->prepare("DELETE FROM `" . DB_PREFIX . "contact` WHERE id IN ($placeholders)");
                $stmtDelContacts->execute($contactIds);
            }

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
