<?php

// Script de limpeza do cache Twig executado pelo processo do servidor web (www-data)
$cacheDir = __DIR__ . '/../storage/cache/twig_slim';

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

$storeSettingsCache = __DIR__ . '/../storage/cache/store_settings_formatted.json';
if (file_exists($storeSettingsCache)) {
    @unlink($storeSettingsCache);
    $removed++;
}

header('Content-Type: text/plain; charset=utf-8');
echo "SUCCESS: Cache do Twig e das configurações expurgado com sucesso! ({$removed} arquivos/diretórios removidos)\n";
