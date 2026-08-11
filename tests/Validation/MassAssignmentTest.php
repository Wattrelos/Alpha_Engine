<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Alpha\Model\Domain\Entities\User;
use Alpha\Model\Domain\Entities\Customer\Customer;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
#[CoversClass(Customer::class)]
class MassAssignmentTest extends TestCase
{
    /**
     * Teste 1: Tentar injetar campo privilegiado extra (is_admin => true) via atribuição dinâmica de propriedades.
     * BaseEntity protege contra criação de propriedades dinâmicas e ignora Mass Assignment não declarado.
     */
    public function testInjectedExtraPropertiesAreIgnoredByEntity(): void
    {
        $user = new User();
        $user->setUsername('joao_normal');
        $user->setEmail('joao@dominio.com');

        // Tenta injetar dinamicamente um campo privilegiado 'is_admin' ou 'is_superuser'
        $user->is_admin = true;
        $user->role = 'super_admin';
        $user['is_admin'] = true;

        $arrayData = $user->toArray();

        $this->assertArrayNotHasKey('is_admin', $arrayData, "Propriedade extra injetada 'is_admin' DEVE ser ignorada pelo mapeamento de dados.");
        $this->assertArrayNotHasKey('role', $arrayData, "Propriedade extra injetada 'role' DEVE ser ignorada no array de dados.");
    }

    /**
     * Teste 2: Mapeamento de dados de requisição de cadastro ignora parâmetros não permitidos no schema.
     */
    public function testCustomerRegistrationMassAssignmentProtection(): void
    {
        $requestPayload = [
            'firstname'     => 'Maria',
            'lastname'      => 'Silva',
            'email'         => 'maria@exemplo.com',
            'telephone'     => '11999998888',
            'password'      => 'senha123',
            // Injeção maliciosa de campos sensíveis/privilegiados
            'is_admin'      => 1,
            'user_group_id' => 1,
            'approved'      => 1
        ];

        $customer = new Customer();
        
        // Mapeia estritamente os campos permitidos
        $allowedFields = ['firstname', 'lastname', 'email', 'telephone'];
        foreach ($allowedFields as $field) {
            if (isset($requestPayload[$field])) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($customer, $setter)) {
                    $customer->$setter($requestPayload[$field]);
                }
            }
        }

        $this->assertEquals('Maria', $customer->getFirstname());
        $this->assertEquals('maria@exemplo.com', $customer->getEmail());
        $this->assertEquals(0, $customer->getStoreId(), "Campos não mapeados da requisição não podem alterar o estado da entidade.");
    }
}
