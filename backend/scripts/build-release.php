<?php

declare(strict_types=1);

/**
 * Script de empacotamento de Release do meusite (Alpha Engine)
 * Gera um arquivo ZIP pronto para produção no XAMPP/servidores PHP sem necessidade de Composer/Git.
 */

$rootDir = realpath(__DIR__ . '/..');
$distDir = $rootDir . '/dist';
$buildDir = $distDir . '/build_temp';
$zipFile = $distDir . '/meusite-release.zip';

echo "=== [Alpha Engine] Gerador de Pacote de Release (.zip) ===\n";
echo "Diretório do projeto: {$rootDir}\n";

// 1. Limpeza de builds anteriores
if (is_dir($buildDir)) {
    echo "--> Limpando pasta temporária anterior...\n";
    exec("rm -rf " . escapeshellarg($buildDir));
}
if (file_exists($zipFile)) {
    echo "--> Removendo ZIP anterior...\n";
    unlink($zipFile);
}

@mkdir($distDir, 0755, true);
@mkdir($buildDir, 0755, true);

// 2. Lista de diretórios e arquivos essenciais para runtime
$dirsToCopy = [
    'core',
    'Containers',
    'Config',
    'public_html',
    'resources',
    'Locales',
];

$filesToCopy = [
    'config.php',
    'clean_twig_cache.php',
    'error.html',
    'php.ini',
    'composer.json',
    '.env.example',
    'README.md',
    'LICENSE.md',
    'AUTHORS.md',
];

echo "--> Copiando código-fonte e recursos de runtime...\n";
foreach ($dirsToCopy as $dir) {
    $sourcePath = ($dir === 'public_html') ? $rootDir . '/../public_html' : $rootDir . '/' . $dir;
    if (is_dir($sourcePath)) {
        exec("cp -r " . escapeshellarg($sourcePath) . " " . escapeshellarg($buildDir . '/' . $dir));
    }
}

foreach ($filesToCopy as $file) {
    if (file_exists($rootDir . '/' . $file)) {
        copy($rootDir . '/' . $file, $buildDir . '/' . $file);
    }
}

// Se não existir .env no build, cria a partir de .env.example
if (file_exists($buildDir . '/.env.example') && !file_exists($buildDir . '/.env')) {
    copy($buildDir . '/.env.example', $buildDir . '/.env');
}

// 3. Estrutura de armazenamento writable
$storageDirs = [
    'storage/cache',
    'storage/logs',
    'storage/session',
    'storage/upload',
    'LogsPersonalizados',
];

echo "--> Criando estruturas de armazenamento/cache temporário...\n";
foreach ($storageDirs as $sDir) {
    $fullPath = $buildDir . '/' . $sDir;
    @mkdir($fullPath, 0777, true);
    file_put_contents($fullPath . '/.gitkeep', '');
}

// 4. Instalação limpa de dependências de PRODUÇÃO via Composer (sem dev)
echo "--> Instalando dependências de PRODUÇÃO (sem ferramentas de dev)...\n";
$composerCmd = sprintf(
    'cd %s && composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction 2>&1',
    escapeshellarg($buildDir)
);
$output = [];
$returnCode = 0;
exec($composerCmd, $output, $returnCode);

if ($returnCode !== 0) {
    echo "❌ ERRO ao executar composer install:\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}

// Remove o composer.json e composer.lock do ZIP de release final para evitar exposição desnecessária
@unlink($buildDir . '/composer.json');
@unlink($buildDir . '/composer.lock');

// 5. Compactação em ZIP
echo "--> Gerando arquivo ZIP em: {$zipFile}...\n";
$zipCmd = sprintf(
    'cd %s && zip -r -q %s .',
    escapeshellarg($buildDir),
    escapeshellarg($zipFile)
);
exec($zipCmd, $output, $returnCode);

if ($returnCode !== 0) {
    echo "❌ ERRO ao criar o arquivo ZIP.\n";
    exit(1);
}

// 6. Limpeza do diretório temporário
exec("rm -rf " . escapeshellarg($buildDir));

$zipSizeBytes = filesize($zipFile);
$zipSizeMb = round($zipSizeBytes / (1024 * 1024), 2);

echo "=========================================================\n";
echo "✅ Pacote de Release gerado com SUCESSO!\n";
echo "📍 Arquivo: {$zipFile}\n";
echo "📦 Tamanho: {$zipSizeMb} MB ({$zipSizeBytes} bytes)\n";
echo "=========================================================\n";
