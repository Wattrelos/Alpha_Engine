<?php

// Script de limpeza do cache Twig e de imagens executado pelo servidor web
$cacheDir = __DIR__ . '/../backend/storage/cache/twig_slim';
$imageCacheDir = __DIR__ . '/image/cache';

function deleteFolder(string $dir): int {
    if (!is_dir($dir)) return 0;
    $count = 0;
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
    return $count;
}

$removed = deleteFolder($cacheDir);
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}
@chmod($cacheDir, 0777);

// Limpeza de cache de imagens redimensionadas
$removedImages = deleteFolder($imageCacheDir);
if (!is_dir($imageCacheDir)) {
    @mkdir($imageCacheDir, 0777, true);
}
@chmod($imageCacheDir, 0777);

$storeSettingsCache = __DIR__ . '/../backend/storage/cache/store_settings_formatted.json';
if (file_exists($storeSettingsCache)) {
    @unlink($storeSettingsCache);
    $removed++;
}

header('Content-Type: text/plain; charset=utf-8');
echo "SUCCESS: Cache do Twig, configurações e miniaturas de imagem expurgados com sucesso! ({$removed} arquivos de cache, {$removedImages} miniaturas removidas)\n";
