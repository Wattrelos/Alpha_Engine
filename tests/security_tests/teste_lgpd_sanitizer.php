<?php

require __DIR__ . '/../../vendor/autoload.php';

use Alpha\Support\LgpdSanitizer;

echo "=== TESTE 1: Mascaramento de Mensagem de Log com PII (LgpdSanitizer) ===\n";

$rawLogMessage = "Usuário joao.silva@dominio.com tentou acesso com CPF 123.456.789-00, CNPJ 12.345.678/0001-99 e Cartão 1234 5678 9012 3456 com password='senhaSuperSecreta123'.";

$sanitizedMessage = LgpdSanitizer::sanitizeLogMessage($rawLogMessage);

echo "Log Bruto:      {$rawLogMessage}\n";
echo "Log Sanitizado: {$sanitizedMessage}\n";

$hasNoRawCpf  = !str_contains($sanitizedMessage, '123.456.789-00') && str_contains($sanitizedMessage, '123.***.***-00');
$hasNoRawCnpj = !str_contains($sanitizedMessage, '12.345.678/0001-99');
$hasNoRawCc   = !str_contains($sanitizedMessage, '1234 5678 9012 3456') && str_contains($sanitizedMessage, '****-****-****-3456');
$hasNoRawPass = !str_contains($sanitizedMessage, 'senhaSuperSecreta123') && str_contains($sanitizedMessage, 'password=[REDACTED]');
$hasMaskedEmail = !str_contains($sanitizedMessage, 'joao.silva@') && str_contains($sanitizedMessage, 'j***@dominio.com');

if ($hasNoRawCpf && $hasNoRawCnpj && $hasNoRawCc && $hasNoRawPass && $hasMaskedEmail) {
    echo "✅ [PASS] Todos os PIIs (CPF, CNPJ, Cartão, Senha, E-mail) foram anonimizados corretamente.\n";
} else {
    echo "❌ [FAIL] Falha na anonimização de PII na mensagem de log!\n";
    exit(1);
}


echo "\n=== TESTE 2: Higienização de Arrays de Dados (Payloads JSON/POST) ===\n";

$rawPayload = [
    'user' => 'admin_teste',
    'email' => 'maria.souza@empresa.com.br',
    'cpf' => '987.654.321-11',
    'password' => 'MinhaSenha123!',
    'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
    'details' => [
        'credit_card' => '4532 1111 2222 9999',
        'ip' => '192.168.1.1'
    ]
];

$sanitizedArray = LgpdSanitizer::sanitizeArray($rawPayload);

echo "Array Sanitizado: " . json_encode($sanitizedArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

if (
    $sanitizedArray['password'] === '[REDACTED]' &&
    $sanitizedArray['access_token'] === '[REDACTED]' &&
    $sanitizedArray['details']['credit_card'] === '[REDACTED]' &&
    str_contains($sanitizedArray['email'], 'm***@empresa.com.br') &&
    str_contains($sanitizedArray['cpf'], '987.***.***-11')
) {
    echo "✅ [PASS] Array de dados higienizado com redação de chaves sensíveis e mascaramento de PII.\n";
} else {
    echo "❌ [FAIL] Falha na sanitização de array de dados!\n";
    exit(1);
}


echo "\n=========================================\n";
echo "🎉 TODOS OS TESTES DE LGPD E SANITIZAÇÃO PASSARAM!\n";
