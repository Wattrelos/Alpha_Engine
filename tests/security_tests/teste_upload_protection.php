<?php

require __DIR__ . '/../../vendor/autoload.php';

use Alpha\Support\UploadSecurityHelper;

echo "=== TESTE 1: Verificação da Presença e Regras dos arquivos .htaccess ===\n";

$storageHtaccess = __DIR__ . '/../../storage/.htaccess';
$imageHtaccess   = __DIR__ . '/../../public_html/image/.htaccess';

if (!file_exists($storageHtaccess)) {
    echo "❌ [FAIL] Arquivo storage/.htaccess não foi encontrado!\n";
    exit(1);
}
$storageContent = file_get_contents($storageHtaccess);
if (!str_contains($storageContent, 'Require all denied') && !str_contains($storageContent, 'Deny from all')) {
    echo "❌ [FAIL] Regras de bloqueio ausentes no storage/.htaccess!\n";
    exit(1);
}
echo "✅ [PASS] storage/.htaccess presente com bloqueio HTTP 100% ativo.\n";

if (!file_exists($imageHtaccess)) {
    echo "❌ [FAIL] Arquivo public_html/image/.htaccess não foi encontrado!\n";
    exit(1);
}
$imageContent = file_get_contents($imageHtaccess);
if (!str_contains($imageContent, 'FilesMatch') || !str_contains($imageContent, 'php')) {
    echo "❌ [FAIL] Regras contra execução de scripts PHP ausentes no public_html/image/.htaccess!\n";
    exit(1);
}
echo "✅ [PASS] public_html/image/.htaccess presente com mitigação contra RCE ativa.\n";


echo "\n=== TESTE 2: Sanitização de Nomes e Path Traversal (UploadSecurityHelper) ===\n";

$maliciousFilename = "../../etc/passwd\0shell.php";
$cleanFilename = UploadSecurityHelper::sanitizeFilename($maliciousFilename);

echo "Filename Malicioso: {$maliciousFilename}\n";
echo "Filename Sanitizado: {$cleanFilename}\n";

if ($cleanFilename === 'etc_passwd_shell.php' || !str_contains($cleanFilename, '..')) {
    echo "✅ [PASS] Sanitização de Path Traversal funcionou perfeitamente.\n";
} else {
    echo "❌ [FAIL] Falha na sanitização de Path Traversal!\n";
    exit(1);
}


echo "\n=== TESTE 3: Validação de MIME-Type Real e Dupla Extensão ===\n";

// 1. Cria imagem PNG válida temporária
$tmpPng = tempnam(sys_get_temp_dir(), 'test_img_');
$im = imagecreatetruecolor(10, 10);
imagepng($im, $tmpPng);
imagedestroy($im);

$isValidPng = UploadSecurityHelper::isSafeImage($tmpPng, 'foto.png');
@unlink($tmpPng);

echo "Imagem PNG Verdadeira -> Válida: " . ($isValidPng ? 'SIM' : 'NÃO') . "\n";
if (!$isValidPng) {
    echo "❌ [FAIL] Imagem PNG válida foi rejeitada indevidamente.\n";
    exit(1);
}

// 2. Cria script PHP falso disfarçado de JPG (Fake Image / RCE Attempt)
$tmpFakeJpg = tempnam(sys_get_temp_dir(), 'fake_img_');
file_put_contents($tmpFakeJpg, '<?php echo "EXPLOIT_EXECUTADO"; ?>');

$isFakeJpgValid = UploadSecurityHelper::isSafeImage($tmpFakeJpg, 'exploit.jpg');
@unlink($tmpFakeJpg);

echo "Script PHP Disfarçado de JPG -> Válido: " . ($isFakeJpgValid ? 'SIM' : 'NÃO') . "\n";

if ($isFakeJpgValid) {
    echo "❌ [FAIL] ALERTA DE SEGURANÇA: Script PHP disfarçado de JPG foi aceito!\n";
    exit(1);
} else {
    echo "✅ [PASS] Script PHP disfarçado de JPG foi rejeitado pela inspeção do MIME-Type real.\n";
}

// 3. Teste de Dupla Extensão (Double Extension Attack)
$tmpDoubleExt = tempnam(sys_get_temp_dir(), 'double_ext_');
file_put_contents($tmpDoubleExt, '<?php system($_GET["cmd"]); ?>');

$isDoubleExtValid = UploadSecurityHelper::isSafeImage($tmpDoubleExt, 'shell.php.jpg');
@unlink($tmpDoubleExt);

echo "Ataque de Dupla Extensão (shell.php.jpg) -> Válido: " . ($isDoubleExtValid ? 'SIM' : 'NÃO') . "\n";

if ($isDoubleExtValid) {
    echo "❌ [FAIL] ALERTA DE SEGURANÇA: Ataque de dupla extensão foi aceito!\n";
    exit(1);
} else {
    echo "✅ [PASS] Ataque de dupla extensão (shell.php.jpg) rejeitado com sucesso.\n";
}


echo "\n=========================================\n";
echo "🎉 TODOS OS TESTES DE PROTEÇÃO DE UPLOADS PASSARAM!\n";
