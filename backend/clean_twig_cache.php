<?php

$cacheDir = __DIR__ . '/storage/cache/twig_slim';

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
echo "Cache do Twig limpo! {$removed} arquivos/pastas removidos de storage/cache/twig_slim/\n";
