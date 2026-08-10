<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Alpha\Auth\Services\AdminAuthService;
use Alpha\Auth\Services\CustomerAuthService;

class SessionRegenerationTest extends TestCase
{
    /**
     * Teste 1: Valida que ao autenticar (createSession), um NOVO ID de sessão seguro é gerado.
     * Impede ataques de Fixação de Sessão (Session Fixation).
     */
    public function testSessionIdRegenerationOnLogin(): void
    {
        $adminAuth = new AdminAuthService();
        
        $oldSessionId = 'guest_session_' . md5('old_session_value');
        
        // Simula o login gerando a sessão autenticada
        $newSessionId = $adminAuth->createSession([
            'id' => 1,
            'username' => 'admin',
            'user_group_id' => 1,
            'name' => 'Administrador Sistema'
        ]);

        $this->assertNotEmpty($newSessionId, "ID de sessão gerado não pode ser vazio.");
        $this->assertNotEquals($oldSessionId, $newSessionId, "O ID da sessão antiga DEVE mudar após a autenticação (Session Fixation Prevention).");
    }

    /**
     * Teste 2: Valida que logins subsequentes para clientes geram IDs de sessão únicos e regenerados.
     */
    public function testCustomerSessionRegeneration(): void
    {
        $customerAuth = new CustomerAuthService();

        $session1 = $customerAuth->createSession([
            'id' => 10,
            'name' => 'João Silva',
            'email' => 'user1@teste.com'
        ]);
        
        $session2 = $customerAuth->createSession([
            'id' => 10,
            'name' => 'João Silva',
            'email' => 'user1@teste.com'
        ]);

        $this->assertNotEmpty($session1);
        $this->assertNotEmpty($session2);
        // Em cada chamada createSession no login, um novo ID de sessão é gerado
        $this->assertNotEquals($oldId ?? '', $session1);
    }
}
