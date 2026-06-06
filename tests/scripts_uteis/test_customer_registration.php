<?php

require '/var/www/html/agsonhos/config.php';
require '/var/www/html/agsonhos/vendor/autoload.php';

use Containers\AppBootstrap;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\DataAccessObject\DataAccessObject;

try {
    echo "=== 1. Bootstrapping AppContainer ===\n";
    $bootstrap = AppBootstrap::boot();
    $container = $bootstrap->getContainer();

    /** @var RepositoryFactory $repositoryFactory */
    $repositoryFactory = $container->get(RepositoryFactory::class);

    /** @var CustomerRepository $customerRepo */
    $customerRepo = $repositoryFactory->get(CustomerRepository::class);

    echo "CustomerRepository resolved successfully.\n";

    echo "=== 2. Testing Registration with Invalid Data ===\n";
    $invalidData = [
        'firstname' => '', // Inválido (menor que 1)
        'lastname' => 'Silva',
        'email' => 'email-invalido', // Inválido
        'telephone' => '12', // Inválido (curto)
        'persontype' => 'F',
        'cpf_cnpj' => '11111111111', // CPF Inválido (dígitos repetidos)
        'password' => '123', // Inválido (menor que config_password_length)
        'confirm' => '1234', // Inválido (não coincide)
        'agree' => 0 // Inválido (não aceitou os termos)
    ];

    $resultInvalid = $customerRepo->registerCustomer($invalidData);
    echo "Validation Result for Invalid Data:\n";
    print_r($resultInvalid['errors']);

    if (!empty($resultInvalid['errors'])) {
        echo "Assertion PASSED: Validation errors caught correctly.\n";
    } else {
        echo "Assertion FAILED: Expected validation errors but none were returned!\n";
    }

    echo "=== 3. Testing Registration with Valid Data ===\n";
    $randomEmail = 'test_reg_' . rand(10000, 99999) . '@testdomain.com';
    $validData = [
        'firstname' => 'João',
        'lastname' => 'Silva',
        'email' => $randomEmail,
        'telephone' => '11999999999',
        'persontype' => 'F',
        // CPF válido gerado por algoritmo
        'cpf_cnpj' => '44414981018',
        'password' => 'SecurePassword123!',
        'confirm' => 'SecurePassword123!',
        'agree' => 1
    ];

    $resultValid = $customerRepo->registerCustomer($validData);

    if (empty($resultValid['errors'])) {
        $customerId = $resultValid['customer_id'];
        echo "Assertion PASSED: Customer registered successfully with ID: $customerId\n";

        echo "=== 4. Verifying Hydrated Customer Entity ===\n";
        $customer = $customerRepo->find($customerId);
        if ($customer) {
            echo "Firstname: " . $customer->getFirstname() . "\n";
            echo "Lastname: " . $customer->getLastname() . "\n";
            echo "Email: " . $customer->getEmail() . "\n";
            echo "Telephone: " . $customer->getTelephone() . "\n";
            echo "CpfCnpj: " . $customer->getCpfCnpj() . "\n";
            echo "PersonType: " . $customer->getPersontype() . "\n";
            echo "StoreId: " . $customer->getStoreId() . "\n";
            echo "LanguageId: " . $customer->getLanguageId() . "\n";

            if (
                $customer->getFirstname() === 'João' &&
                $customer->getLastname() === 'Silva' &&
                $customer->getEmail() === $randomEmail &&
                $customer->getTelephone() === '11999999999' &&
                $customer->getCpfCnpj() === '44414981018' &&
                $customer->getPersontype() === 'F' &&
                $customer->getStoreId() === 1 &&
                $customer->getLanguageId() === 2
            ) {
                echo "Assertion PASSED: Hydration values match exactly.\n";
            } else {
                echo "Assertion FAILED: Hydration values do not match!\n";
            }
        } else {
            echo "Assertion FAILED: Registered customer could not be found in DB!\n";
        }

        echo "=== 5. Cleaning up registered customer ===\n";
        $dao = new DataAccessObject();
        $dao->executeRawSQL("DELETE FROM " . DB_PREFIX . "customer WHERE id = ?", [$customerId]);
        echo "Test customer deleted from DB.\n";
    } else {
        echo "Assertion FAILED: Registration failed with errors:\n";
        print_r($resultValid['errors']);
    }

    echo "=== Test Completed! ===\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
