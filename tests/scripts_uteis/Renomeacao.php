<?php
/*
* Verificar se os nomes de arquivos *.php seguem o padrão PascalCase, se não, renomeá-los
*/
// $targetDir = __DIR__ . '/catalog'; // Ajuste aqui para a pasta inicial
// $targetDir = __DIR__ . '/image'; // Ajuste aqui para a pasta inicial
// $targetDir = __DIR__ . '/system'; // Ajuste aqui para a pasta inicial
// $targetDir = __DIR__ . '/extension'; // Ajuste aqui para a pasta inicial
$targetDir = __DIR__ . '/kezu4uvcyqd1oevh'; // Ajuste aqui para a pasta inicial

if (!is_dir($targetDir)) {
    die("Diretório não encontrado: $targetDir\n");
}

$directory = new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS);
// O CHILD_FIRST é vital para não perder a referência das pastas filhas
$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);

echo "Iniciando renomeação para PascalCase...\n";

foreach ($iterator as $item) {
    if ($item->isDir()) {
        $oldPath = $item->getRealPath();
        $oldName = $item->getFilename();
        
        // Transforma a primeira letra em Maiúscula (ex: model -> Model)        
        $newName = strtolower($oldName); // Alterado para caixa baixa conforme a regra de negócio

        
        if ($oldName !== $newName) {
            // CORREÇÃO AQUI: DIRECTORY_SEPARATOR
            $newPath = $item->getPath() . DIRECTORY_SEPARATOR . $newName;
            
            if (rename($oldPath, $newPath)) {
                echo "Sucesso: $oldName -> $newName\n";
            } else {
                echo "Erro ao renomear: $oldPath\n";
            }
        }
    }
}
