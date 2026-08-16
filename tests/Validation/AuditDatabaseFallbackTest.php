<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Alpha\Services\Audit\AuditLoggerService;
use Alpha\Events\QueueService;

#[CoversClass(AuditLoggerService::class)]
class AuditDatabaseFallbackTest extends TestCase
{
    private AuditLoggerService $auditLogger;

    protected function setUp(): void
    {
        if (!defined('APPLICATION')) {
            define('APPLICATION', 'admin');
        }
        $configPath = __DIR__ . '/../../backend/config.php';
        if (file_exists($configPath)) {
            require_once $configPath;
        }

        // Mock do QueueService que simula falha/ausencia de RabbitMQ
        $mockQueue = $this->createMock(QueueService::class);
        $mockQueue->method('publish')->willThrowException(new \RuntimeException("RabbitMQ offline"));

        $this->auditLogger = new AuditLoggerService($mockQueue);
    }

    /**
     * Teste 1: Quando RabbitMQ falha, o evento DEVE ser salvo com sucesso na tabela MySQL tbkk_audit_logs.
     */
    public function testFallbackInsertsEventIntoDatabaseOnRabbitMqFailure(): void
    {
        $uniqueEvent = 'TEST_FALLBACK_' . time();
        $payload = [
            'action'   => 'password_reset_request',
            'password' => 'secret_pass_123',
            'cpf'      => '123.456.789-00',
            'email'    => 'usuario.teste@dominio.com'
        ];

        $result = $this->auditLogger->logEvent(
            $uniqueEvent,
            $payload,
            'teste_admin',
            '192.168.1.100',
            1
        );

        $this->assertTrue($result, "O fallback de auditoria em banco de dados deve retornar true em caso de sucesso.");

        // Consulta a tabela MySQL para confirmar a gravação
        $logs = $this->auditLogger->getAuditLogs(1, 10);
        $this->assertNotEmpty($logs, "Os logs de auditoria no MySQL não devem estar vazios.");

        $foundEvent = null;
        foreach ($logs as $log) {
            if (($log['event'] ?? '') === $uniqueEvent) {
                $foundEvent = $log;
                break;
            }
        }

        $this->assertNotNull($foundEvent, "O evento único gerado '{$uniqueEvent}' deve ser encontrado na tabela MySQL tbkk_audit_logs.");
        $this->assertEquals('teste_admin', $foundEvent['username']);
        $this->assertEquals('192.168.1.100', $foundEvent['ip']);

        // 2. Valida se o payload no banco passou por higienizacao LGPD
        $savedPayload = $foundEvent['payload'];
        $this->assertIsArray($savedPayload, "O payload gravado no banco deve ser um array JSON decodificado.");
        $this->assertEquals('[REDACTED]', $savedPayload['password'], "A senha no payload de auditoria DEVE ser redigida para [REDACTED].");
        $this->assertEquals('123.***.***-00', $savedPayload['cpf'], "O CPF no payload de auditoria DEVE ser mascarado de acordo com a LGPD.");
        $this->assertEquals('u***@dominio.com', $savedPayload['email'], "O e-mail no payload DEVE ter a parte local mascarada.");
    }
}
