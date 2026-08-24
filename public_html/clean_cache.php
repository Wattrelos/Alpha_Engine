<?php

declare(strict_types=1);

/**
 * Alpha Engine - Script de Limpeza Completa de Cache
 *
 * Expurgos realizados:
 * 1. Cache de templates Twig (twig_slim e twig_setup)
 * 2. Cache de dados e queries em disco (alpha_cache_*.cache, *.json, *.tmp)
 * 3. Cache de proxies de entidades (alpha_proxies)
 * 4. Cache de miniaturas de imagens redimensionadas (image/cache)
 * 5. OPcache do PHP (se habilitado)
 * 6. Cache do Redis (chaves de aplicação, preservando sessões)
 */

$backendDir = dirname(__DIR__) . '/backend';
$autoloadFile = $backendDir . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require_once $autoloadFile;
}

$storageCacheDir = $backendDir . '/storage/cache';
$twigSlimDir = $storageCacheDir . '/twig_slim';
$twigSetupDir = $storageCacheDir . '/twig_setup';
$proxiesDir = $storageCacheDir . '/alpha_proxies';
$imageCacheDir = __DIR__ . '/image/cache';

/**
 * Remove recursivamente todos os arquivos e subpastas de um diretório, mantendo o diretório raiz.
 */
function cleanDirectoryContents(string $dir): int {
    if (!is_dir($dir)) {
        return 0;
    }

    $count = 0;
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $path = $item->getRealPath();
            if ($path === false) {
                continue;
            }
            if ($item->isDir()) {
                if (@rmdir($path)) {
                    $count++;
                }
            } else {
                if (@unlink($path)) {
                    $count++;
                }
            }
        }
    } catch (\Throwable $e) {
        // Fallback defensivo usando glob recursivo
        $files = glob($dir . '/*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $count += cleanDirectoryContents($file);
                    @rmdir($file);
                } elseif (is_file($file)) {
                    if (@unlink($file)) {
                        $count++;
                    }
                }
            }
        }
    }

    return $count;
}

/**
 * Garante que o diretório exista com permissões de escrita (0777).
 */
function ensureDirectory(string $dir): void {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    if (is_dir($dir)) {
        @chmod($dir, 0777);
    }
}

// 1. Limpeza do Cache Twig
$removedTwig = cleanDirectoryContents($twigSlimDir) + cleanDirectoryContents($twigSetupDir);
ensureDirectory($twigSlimDir);
ensureDirectory($twigSetupDir);

// 2. Limpeza dos Proxies de Entidades
$removedProxies = cleanDirectoryContents($proxiesDir);
ensureDirectory($proxiesDir);

// 3. Limpeza de Arquivos de Cache Soltos em storage/cache/ (*.cache, *.json, *.tmp)
$removedCoreCache = 0;
if (is_dir($storageCacheDir)) {
    $looseFiles = array_merge(
        glob($storageCacheDir . '/*.cache') ?: [],
        glob($storageCacheDir . '/*.json') ?: [],
        glob($storageCacheDir . '/*.tmp') ?: []
    );

    foreach ($looseFiles as $file) {
        if (is_file($file) && @unlink($file)) {
            $removedCoreCache++;
        }
    }
}

// 4. Limpeza de Cache de Imagens Redimensionadas
$removedImages = cleanDirectoryContents($imageCacheDir);
ensureDirectory($imageCacheDir);

// 5. Reset do OPcache
$opcacheReset = false;
if (function_exists('opcache_reset')) {
    $opcacheReset = @opcache_reset();
}

// 6. Limpeza de Cache no Redis (preservando sessões)
$redisCacheCleaned = 0;
$redisStatus = 'Não configurado';

$envFile = $backendDir . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile) ?: '';
    $redisHost = '';
    $redisPort = 6379;
    $redisPassword = '';
    $redisEnabled = true;

    if (preg_match('/^REDIS_HOST=(.*)$/m', $envContent, $m)) {
        $redisHost = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/^REDIS_PORT=(.*)$/m', $envContent, $m)) {
        $redisPort = (int)trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/^REDIS_PASSWORD=(.*)$/m', $envContent, $m)) {
        $redisPassword = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/^REDIS_ENABLED=(.*)$/m', $envContent, $m)) {
        $redisEnabled = filter_var(trim($m[1], " \t\n\r\0\x0B\"'"), FILTER_VALIDATE_BOOLEAN);
    }

    if ($redisEnabled && !empty($redisHost) && class_exists('\Predis\Client')) {
        try {
            $redis = new \Predis\Client([
                'host' => $redisHost,
                'port' => $redisPort,
                'password' => !empty($redisPassword) ? $redisPassword : null,
                'timeout' => 1.0
            ]);
            $redis->connect();

            // Remove chaves de cache (mantém sessao: e sessao:admin:)
            $cacheKeys = array_merge(
                $redis->keys('alpha_cache:*') ?: [],
                $redis->keys('cache:*') ?: []
            );

            foreach ($cacheKeys as $key) {
                if (!str_starts_with($key, 'sessao:')) {
                    $redis->del($key);
                    $redisCacheCleaned++;
                }
            }

            $redisStatus = "Conectado ({$redisCacheCleaned} chaves purgadas, sessões preservadas)";
        } catch (\Throwable $e) {
            $redisStatus = 'Erro de conexão: ' . $e->getMessage();
        }
    }
}

$totalRemoved = $removedTwig + $removedCoreCache + $removedProxies + $removedImages;

if (!headers_sent()) {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "====================================================\n";
echo "       ALPHA ENGINE - LIMPEZA DE CACHE             \n";
echo "====================================================\n";
echo "✓ Templates Twig expurgados:       {$removedTwig} arquivos\n";
echo "✓ Cache de Dados/Queries expurgado: {$removedCoreCache} arquivos\n";
echo "✓ Proxies de Entidades expurgados:  {$removedProxies} arquivos\n";
echo "✓ Miniaturas de Imagem expurgadas:  {$removedImages} arquivos\n";
echo "✓ OPcache Resetado:                 " . ($opcacheReset ? 'Sim' : 'N/A') . "\n";
echo "✓ Cache Redis:                      {$redisStatus}\n";
echo "----------------------------------------------------\n";
echo "STATUS: SUCESSO! Total de {$totalRemoved} itens limpos.\n";
echo "====================================================\n";

