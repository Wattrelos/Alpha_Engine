<?php
require_once __DIR__ . '/../vendor/autoload.php';
define('APPLICATION', 'admin');
require_once __DIR__ . '/../config.php';

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

$bootstrap = AppBootstrap::boot();
$conn = ConnectionDB::getInstance()->getConnection();

try {
    echo "=== STARTING SUPPLIER CONTACTS INTEGRATION TEST ===\n\n";

    echo "=== 1. Checking Database Setup ===\n";
    // Check if geo records exist
    $countryId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "geo_countries` LIMIT 1")->fetchColumn();
    $zoneId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "geo_zones` LIMIT 1")->fetchColumn();
    $cityId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "geo_cities` LIMIT 1")->fetchColumn();

    if (!$countryId || !$zoneId || !$cityId) {
        throw new \Exception("Geolocation data (country, zone, city) must exist in database for this test.");
    }
    echo "Found country ID: $countryId, zone ID: $zoneId, city ID: $cityId.\n";

    // Find or create test manufacturer
    $manufacturerId = (int)$conn->query("SELECT id FROM `" . DB_PREFIX . "manufacturer` LIMIT 1")->fetchColumn();
    $createdTestManufacturer = false;
    if (!$manufacturerId) {
        $conn->prepare("INSERT INTO `" . DB_PREFIX . "manufacturer` (name, image, sort_order) VALUES ('Test Manufacturer', '', 0)")->execute();
        $manufacturerId = (int)$conn->lastInsertId();
        $createdTestManufacturer = true;
        echo "Created temporary test manufacturer ID: $manufacturerId\n";
    } else {
        echo "Found manufacturer ID: $manufacturerId\n";
    }

    echo "\n=== 2. Creating Supplier with Contacts ===\n";
    $supplier = new Supplier();
    $supplier->setCompanyName('Test Supplier Contacts Ltd.');
    $supplier->setTradeName('Test Supplier Contacts');
    $supplier->setTaxId('99999999999999'); // 14 digits CNPJ
    $supplier->setEmail('test_contacts@supplier.com');
    $supplier->setPhone('11999999999');
    $supplier->setIsActive(true);
    $supplier->setCreatedAt(date('Y-m-d H:i:s'));
    $supplier->setUpdatedAt(date('Y-m-d H:i:s'));

    // Create address
    $address = new Addresses();
    $address->setPostalCode('01001000');
    $address->setStreet('Rua dos Testes');
    $address->setNumber('999');
    $address->setDistrict('Centro');

    $mapperFactory = MapperFactory::getInstance();
    $country = $mapperFactory->get(GeoCountryMapper::class)->findById($countryId);
    $zone = $mapperFactory->get(GeoZoneMapper::class)->findById($zoneId);
    $city = $mapperFactory->get(GeoCityMapper::class)->findById($cityId);

    $address->setCountry($country);
    $address->setZone($zone);
    $address->setCity($city);

    $supplier->setAddresses($address);

    // Add 2 contacts
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
    
    if (!$supplierId) {
        throw new \Exception("Failed to save Supplier.");
    }
    echo "Supplier saved with ID: $supplierId\n";

    echo "\n=== 3. Loading Saved Supplier & Validating Contacts ===\n";
    /** @var Supplier|null $loadedSupplier */
    $loadedSupplier = $supplierRepository->find($supplierId);
    if (!$loadedSupplier) {
        throw new \Exception("Failed to load saved Supplier.");
    }

    $loadedContacts = $loadedSupplier->getContacts();
    echo "Loaded " . count($loadedContacts) . " contacts.\n";

    if (count($loadedContacts) !== 2) {
        throw new \Exception("Expected 2 contacts, got " . count($loadedContacts));
    }

    foreach ($loadedContacts as $c) {
        echo " - Contact: {$c['name']} ({$c['position']}), email: {$c['email']}, phone: {$c['phone']}, manufacturer_id: {$c['manufacturer_id']}\n";
        if (!in_array($c['name'], ['John Doe', 'Jane Smith'])) {
            throw new \Exception("Unexpected contact name: " . $c['name']);
        }
        if ($c['manufacturer_id'] != $manufacturerId) {
            throw new \Exception("Expected manufacturer_id $manufacturerId, got " . $c['manufacturer_id']);
        }
    }
    echo "Assertion PASSED: Supplier and contacts loaded and validated correctly.\n";

    echo "\n=== 4. Updating Supplier (Modify one, Delete one, Add one) ===\n";
    // We modify John Doe (index 0 or search by name)
    // We delete Jane Smith
    // We add Bob Johnson
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
            // Jane Smith is NOT added to $newContacts, so she should be deleted (orphan cleanup)
        }
    }

    // Add Bob Johnson
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
    echo "Supplier updated.\n";

    echo "\n=== 5. Validating Update & Orphan Cleanup ===\n";
    /** @var Supplier|null $loadedSupplier2 */
    $loadedSupplier2 = $supplierRepository->find($supplierId);
    if (!$loadedSupplier2) {
        throw new \Exception("Failed to reload Supplier after update.");
    }

    $loadedContacts2 = $loadedSupplier2->getContacts();
    echo "Reloaded " . count($loadedContacts2) . " contacts.\n";

    if (count($loadedContacts2) !== 2) {
        throw new \Exception("Expected 2 contacts after update, got " . count($loadedContacts2));
    }

    $modifiedJohnFound = false;
    $bobJohnsonFound = false;
    $bobJohnsonId = 0;

    foreach ($loadedContacts2 as $c) {
        echo " - Contact: {$c['name']} ({$c['position']})\n";
        if ($c['name'] === 'John Doe Modified') {
            $modifiedJohnFound = true;
            if ($c['id'] != $johnDoeId) {
                throw new \Exception("Modified contact ID mismatch.");
            }
        } elseif ($c['name'] === 'Bob Johnson') {
            $bobJohnsonFound = true;
            $bobJohnsonId = (int)$c['id'];
        }
    }

    if (!$modifiedJohnFound) {
        throw new \Exception("Modified contact John Doe Modified was not found.");
    }
    if (!$bobJohnsonFound) {
        throw new \Exception("New contact Bob Johnson was not found.");
    }

    // Verify Jane Smith is completely deleted from agsc_contact
    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "contact` WHERE id = ?");
    $stmtCheck->execute([$janeSmithId]);
    $janeSmithCount = (int)$stmtCheck->fetchColumn();
    if ($janeSmithCount !== 0) {
        throw new \Exception("Jane Smith contact was not deleted from database (orphan cleanup failed).");
    }
    echo "Assertion PASSED: Jane Smith contact was successfully cleaned up.\n";

    // Verify Jane Smith relationship is deleted from pivot
    $stmtCheckPivot = $conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "supplier_contact_manufacturer` WHERE contact_id = ?");
    $stmtCheckPivot->execute([$janeSmithId]);
    $janeSmithPivotCount = (int)$stmtCheckPivot->fetchColumn();
    if ($janeSmithPivotCount !== 0) {
        throw new \Exception("Jane Smith pivot relationship was not deleted.");
    }
    echo "Assertion PASSED: Jane Smith pivot relationship cleaned up.\n";

    echo "\n=== 6. Deleting Supplier & Validating Cascading Contacts Deletion ===\n";
    $deleteResult = $supplierRepository->delete($supplierId);
    if (!$deleteResult) {
        throw new \Exception("Failed to delete Supplier.");
    }
    echo "Supplier deleted.\n";

    // Verify Supplier is gone
    $stmtCheckSupplier = $conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "suppliers` WHERE id = ?");
    $stmtCheckSupplier->execute([$supplierId]);
    if ((int)$stmtCheckSupplier->fetchColumn() !== 0) {
        throw new \Exception("Supplier still exists in database.");
    }

    // Verify John Doe and Bob Johnson are gone
    $stmtCheckContacts = $conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "contact` WHERE id IN (?, ?)");
    $stmtCheckContacts->execute([$johnDoeId, $bobJohnsonId]);
    $contactsCount = (int)$stmtCheckContacts->fetchColumn();
    if ($contactsCount !== 0) {
        throw new \Exception("Contacts were not deleted when the Supplier was deleted.");
    }
    echo "Assertion PASSED: Associated contacts were deleted when supplier was deleted.\n";

    // Verify pivot relationship is gone
    $stmtCheckPivotAll = $conn->prepare("SELECT COUNT(*) FROM `" . DB_PREFIX . "supplier_contact_manufacturer` WHERE supplier_id = ?");
    $stmtCheckPivotAll->execute([$supplierId]);
    if ((int)$stmtCheckPivotAll->fetchColumn() !== 0) {
        throw new \Exception("Supplier contact pivot records still exist.");
    }
    echo "Assertion PASSED: Supplier contact pivot records were successfully cleaned up.\n";

    echo "\n=== 7. Cleaning Up Test Manufacturer ===\n";
    if ($createdTestManufacturer) {
        $conn->prepare("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE id = ?")->execute([$manufacturerId]);
        echo "Temporary manufacturer deleted.\n";
    }

    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
} catch (\Throwable $e) {
    echo "\n❌ TEST FAILED!\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
