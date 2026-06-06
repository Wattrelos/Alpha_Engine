<?php

require '/var/www/html/agsonhos/config.php';
require '/var/www/html/agsonhos/vendor/autoload.php';

use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;

try {
    echo "=== 1. Bootstrapping AppContainer ===\n";
    $bootstrap = AppBootstrap::boot();
    $container = $bootstrap->getContainer();

    /** @var RepositoryFactory $repositoryFactory */
    $repositoryFactory = $container->get(RepositoryFactory::class);

    /** @var CustomerAddressesRepository $addressesRepo */
    $addressesRepo = $repositoryFactory->get(CustomerAddressesRepository::class);

    echo "CustomerAddressesRepository resolved successfully.\n";

    echo "=== 2. Creating a test address ===\n";
    $testCustomerId = 2001; // customer id from the guides

    // Save address
    $addressData = [
        'postcode'     => '01001-000',
        'street'    => 'Praça da Sé',
        'number'       => '111',
        'complement'    => 'Apto 12',
        'neighborhood' => 'Sé',
        'city'         => 'São Paulo',
        'zone_id'      => 'SP',
        'country_id'   => 76,
        'default'      => true
    ];

    $savedId = $addressesRepo->save($addressData, $testCustomerId);
    echo "Address saved with ID: $savedId\n";

    echo "=== 3. Listing addresses for customer ===\n";
    $addresses = $addressesRepo->getAddresses($testCustomerId);
    print_r($addresses);

    if (count($addresses) > 0) {
        echo "Successfully retrieved " . count($addresses) . " address(es).\n";

        // Assertions
        $savedAddress = $addresses[0];
        if (
            $savedAddress['postcode'] === '01001-000' &&
            $savedAddress['street'] === 'Praça da Sé' &&
            $savedAddress['number'] === '111' &&
            $savedAddress['city'] === 'São Paulo' &&
            $savedAddress['zone'] === 'São Paulo'
        ) {
            echo "Assertion PASSED: Address fields match saved data.\n";
        } else {
            echo "Assertion FAILED: Address fields do not match!\n";
        }
    } else {
        echo "Assertion FAILED: No addresses found for customer!\n";
    }

    echo "=== 4. Cleaning up test address ===\n";
    $addressesRepo->delete($savedId, $testCustomerId);
    echo "Address deleted.\n";

    $addressesAfterDelete = $addressesRepo->getAddresses($testCustomerId);
    echo "Addresses count after delete: " . count($addressesAfterDelete) . "\n";

    echo "=== Test Completed! ===\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
