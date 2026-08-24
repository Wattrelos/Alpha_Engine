<?php

declare(strict_types=1);

$storageCacheDir = __DIR__ . '/storage/cache';
$twigSlimDir = $storageCacheDir . '/twig_slim';
$twigSetupDir = $storageCacheDir . '/twig_setup';

function deleteFolder(string $dir): int {
    if (!is_dir($dir)) return 0;
    $count = 0;
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = $fileinfo->isDir() ? 'rmdir' : 'unlink';
            if (@$todo($fileinfo->getRealPath())) {
                $count++;
            }
        }
    } catch (\Throwable $e) {
        // Ignora
    }
    return $count;
}

$removedTwig = deleteFolder($twigSlimDir) + deleteFolder($twigSetupDir);
if (!is_dir($twigSlimDir)) @mkdir($twigSlimDir, 0777, true);
if (!is_dir($twigSetupDir)) @mkdir($twigSetupDir, 0777, true);
@chmod($twigSlimDir, 0777);
@chmod($twigSetupDir, 0777);

$removedFiles = 0;
$looseFiles = array_merge(
    glob($storageCacheDir . '/*.cache') ?: [],
    glob($storageCacheDir . '/*.json') ?: []
);
foreach ($looseFiles as $file) {
    if (is_file($file) && @unlink($file)) {
        $removedFiles++;
    }
}

echo "Cache do Twig e do Core limpos com sucesso! ({$removedTwig} itens de templates, {$removedFiles} arquivos de cache core)\n";

