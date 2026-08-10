<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Alpha\Support\LgpdSanitizer;
use Alpha\Model\Domain\Entities\User;

class ApiTransformerTest extends TestCase
{
    /**
     * Teste 1: Serialização de API / Array Sanitizer oculta e substitui chaves confidenciais.
     * Garante que password_hash, access_token, credit_card não fiquem expostos no JSON final.
     */
    public function testApiJsonResponseContainsNoConfidentialKeys(): void
    {
        $rawUserData = [
            'id'            => 42,
            'username'      => 'joao_desenvolvedor',
            'email'         => 'joao.dev@empresa.com.br',
            'cpf'           => '123.456.789-00',
            'password'      => 'SuperSenhaSecreta!123',
            'password_hash' => '$2y$10$e8V9...hash_bcrypt_confidencial',
            'access_token'  => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
            'credit_card'   => '4532 1111 2222 9999',
            'deleted_at'    => null
        ];

        $sanitizedData = LgpdSanitizer::sanitizeArray($rawUserData);
        $jsonResponse = json_encode($sanitizedData);

        $this->assertStringNotContainsString('SuperSenhaSecreta!123', $jsonResponse, "A senha em texto claro JAMAIS deve constar na resposta da API.");
        $this->assertEquals('[REDACTED]', $sanitizedData['password'], "A chave password deve estar redigida como [REDACTED].");
        $this->assertEquals('[REDACTED]', $sanitizedData['access_token'], "O token de acesso deve estar redigido como [REDACTED].");
        $this->assertEquals('[REDACTED]', $sanitizedData['credit_card'], "Número de cartão de crédito deve ser totalmente redigido.");
        $this->assertStringNotContainsString('123.456.789-00', $jsonResponse, "CPF deve ser mascarado antes de ser serializado na API.");
    }

    /**
     * Teste 2: Anonimização e Sanitização de Logs de Produção (Evitar Vazamento de PII nos logs da API).
     */
    public function testApiLogsDoNotLeakSensitivePiiData(): void
    {
        $rawLog = "Falha de autenticação do usuário carlos@dominio.com usando password='MinhaSenhaForte123' no IP 192.168.0.10.";
        
        $sanitizedLog = LgpdSanitizer::sanitizeLogMessage($rawLog);

        $this->assertStringNotContainsString('MinhaSenhaForte123', $sanitizedLog, "Log da API não pode conter a senha informada no evento.");
        $this->assertStringContainsString('password=[REDACTED]', $sanitizedLog, "Log da API deve substituir a senha por [REDACTED].");
    }
}
