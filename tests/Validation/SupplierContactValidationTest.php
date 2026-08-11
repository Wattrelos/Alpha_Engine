<?php

declare(strict_types=1);

namespace Tests\Validation;

require_once __DIR__ . '/../../config.php';

use PHPUnit\Framework\TestCase;
use Containers\AppBootstrap;
use Alpha\Model\DataAccessObject\ConnectionDB;
use Alpha\Model\Domain\Entities\Supplier\Supplier;
use Alpha\Model\Domain\Entities\Supplier\Addresses;
use Alpha\Model\Domain\Repositories\SupplierRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Mappers\MapperFactory;
use Alpha\Mappers\EntityMappers\GeoCountryMapper;
use Alpha\Mappers\EntityMappers\GeoZoneMapper;
use Alpha\Mappers\EntityMappers\GeoCityMapper;

class SupplierContactValidationTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }
        AppBootstrap::boot();
        $this->conn = ConnectionDB::getInstance()->getConnection();
    }

    public function testSupplierContactsCrudAndOrphanCleanup(): void
    {
        $countryId = (int)$this->conn->query("SELECT id FROM `" . DB_PREFIX . "geo_countries` LIMIT 1")->fetchColumn();
        if (!$countryId) {
            $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "geo_countries` (name, iso_code_2, iso_code_3, status) VALUES ('Brasil', 'BR', 'BRA', 1)")->execute();
            $countryId = (int)$this->conn->lastInsertId();
        }

        $zoneId = (int)$this->conn->query("SELECT id FROM `" . DB_PREFIX . "geo_zones` LIMIT 1")->fetchColumn();
        if (!$zoneId) {
            $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "geo_zones` (country_id, name, code, status) VALUES (?, 'São Paulo', 'SP', 1)")->execute([$countryId]);
            $zoneId = (int)$this->conn->lastInsertId();
        }

        $cityId = (int)$this->conn->query("SELECT id FROM `" . DB_PREFIX . "geo_cities` LIMIT 1")->fetchColumn();
        if (!$cityId) {
            $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "geo_cities` (id, zone_id, name, is_served) VALUES (1, ?, 'São Paulo', 1)")->execute([$zoneId]);
            $cityId = 1;
        }

        $this->assertGreaterThan(0, $countryId);
        $this->assertGreaterThan(0, $zoneId);
        $this->assertGreaterThan(0, $cityId);

        // Find or create test manufacturer
        $manufacturerTable = DB_PREFIX . "manufacturer";
        $manufacturerId = (int)$this->conn->query("SELECT id FROM `" . $manufacturerTable . "` LIMIT 1")->fetchColumn();
        $createdTestManufacturer = false;

        if ($manufacturerId <= 0) {
            $this->conn->prepare("INSERT INTO `" . $manufacturerTable . "` (name, image) VALUES ('Test Manufacturer', '')")->execute();
            $manufacturerId = (int)$this->conn->lastInsertId();
            $createdTestManufacturer = true;
        }

        // Also ensure brand table has record with same ID if brand table exists
        try {
            $brandId = (int)$this->conn->query("SELECT id FROM `" . DB_PREFIX . "brand` WHERE id = {$manufacturerId} LIMIT 1")->fetchColumn();
            if (!$brandId) {
                $this->conn->prepare("INSERT INTO `" . DB_PREFIX . "brand` (id, name) VALUES (?, 'Test Manufacturer')")->execute([$manufacturerId]);
            }
        } catch (\Throwable $e) {
            // Ignore if brand table doesn't exist
        }

        // Create Supplier
        $supplier = new Supplier();
        $supplier->setCompanyName('Test Supplier Contacts Ltd.');
        $supplier->setTradeName('Test Supplier Contacts');
        $supplier->setTaxId('99999999999999');
        $supplier->setEmail('test_contacts@supplier.com');
        $supplier->setPhone('11999999999');
        $supplier->setIsActive(true);
        $supplier->setCreatedAt(date('Y-m-d H:i:s'));
        $supplier->setUpdatedAt(date('Y-m-d H:i:s'));

        $address = new Addresses();
        $address->setPostalCode('01001000');
        $address->setStreet('Rua dos Testes');
        $address->setNumber('999');
        $address->setDistrict('Centro');

        $mapperFactory = MapperFactory::getInstance();
        $address->setCountry($mapperFactory->get(GeoCountryMapper::class)->findById($countryId));
        $address->setZone($mapperFactory->get(GeoZoneMapper::class)->findById($zoneId));
        $address->setCity($mapperFactory->get(GeoCityMapper::class)->findById($cityId));

        $supplier->setAddresses($address);

        $contacts = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@supplier.com',
                'phone' => '11988887777',
                'position' => 'Sales Representative',
                'is_active' => 1,
                'manufacturer_id' => $manufacturerId
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@supplier.com',
                'phone' => '11977776666',
                'position' => 'Support Engineer',
                'is_active' => 1,
                'manufacturer_id' => $manufacturerId
            ]
        ];
        $supplier->setContacts($contacts);

        /** @var SupplierRepository $supplierRepository */
        $supplierRepository = RepositoryFactory::getInstance()->get(SupplierRepository::class);
        $supplierId = $supplierRepository->save($supplier);
        $this->assertGreaterThan(0, $supplierId);

        // Load & validate
        $loadedSupplier = $supplierRepository->find($supplierId);
        $this->assertNotNull($loadedSupplier);
        $loadedContacts = $loadedSupplier->getContacts();
        $this->assertCount(2, $loadedContacts);

        // Update contacts: Modify John Doe, delete Jane Smith, add Bob Johnson
        $newContacts = [];
        $johnDoeId = 0;
        $janeSmithId = 0;

        foreach ($loadedContacts as $c) {
            if ($c['name'] === 'John Doe') {
                $johnDoeId = (int)$c['id'];
                $c['name'] = 'John Doe Modified';
                $c['position'] = 'Senior Sales';
                $newContacts[] = $c;
            } elseif ($c['name'] === 'Jane Smith') {
                $janeSmithId = (int)$c['id'];
            }
        }

        $newContacts[] = [
            'name' => 'Bob Johnson',
            'email' => 'bob@supplier.com',
            'phone' => '11966665555',
            'position' => 'Manager',
            'is_active' => 1,
            'manufacturer_id' => $manufacturerId
        ];

        $loadedSupplier->setContacts($newContacts);
        $supplierRepository->save($loadedSupplier);

        // Reload & verify Jane Smith is removed
        $loadedSupplier2 = $supplierRepository->find($supplierId);
        $loadedContacts2 = $loadedSupplier2->getContacts();
        $this->assertCount(2, $loadedContacts2);

        $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "contact` WHERE id = ?");
        $stmtCheck->execute([$janeSmithId]);
        $this->assertEquals(0, (int)$stmtCheck->fetchColumn(), "Contato Jane Smith deve ser excluído por orphan cleanup.");

        // Delete supplier and check cascade deletion
        $supplierRepository->delete($supplierId);

        $stmtCheckSupplier = $this->conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "suppliers` WHERE id = ?");
        $stmtCheckSupplier->execute([$supplierId]);
        $this->assertEquals(0, (int)$stmtCheckSupplier->fetchColumn());

        if ($createdTestManufacturer) {
            $this->conn->prepare("DELETE FROM `" . $manufacturerTable . "` WHERE id = ?")->execute([$manufacturerId]);
        }
    }
}
