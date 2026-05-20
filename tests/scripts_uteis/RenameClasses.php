<?php

// Caminho para a raiz do seu projeto ou pasta contendo as classes
$folder = __DIR__ . '/';

// Função para converter snake_case para PascalCase
function snakeToPascal($string) {
    // Remove a extensão .php antes de converter
    $string = str_replace('.php', '', $string);
    // Converte snake_case para PascalCase
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
}

// Inicializa o iterador recursivo
$directory = new RecursiveDirectoryIterator($folder);
$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $oldPath = $file->getPathname();
        $oldName = $file->getFilename();
        
        // Gera o novo nome
        $newName = snakeToPascal($oldName) . '.php';
        $newPath = $file->getPath() . DIRECTORY_SEPARATOR . $newName;

        if ($oldPath !== $newPath) {
            echo "Renomeando: $oldName -> $newName\n";
            
            // 1. Renomeia o arquivo físico
            rename($oldPath, $newPath);

            // 2. Atualiza o conteúdo da classe dentro do arquivo
            $content = file_get_contents($newPath);
            $oldClassName = str_replace('.php', '', $oldName);
            $newClassName = str_replace('.php', '', $newName);
            
            // Substitui 'class old_name' por 'class NewName'
            $newContent = str_replace("class " . $oldClassName, "class " . $newClassName, $content);
            
            // Salva o arquivo com o novo conteú
            file_put_contents($newPath, $newContent);
        }
    }
}

echo "Processo concluído com sucesso.\n";
?>
