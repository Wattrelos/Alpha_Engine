<?php
require __DIR__ . '/../vendor/autoload.php';

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

$compiler = new Compiler();
$compiler->setOutputStyle(OutputStyle::EXPANDED);

// Caminhos para resolução de imports
$compiler->addImportPath(__DIR__ . '/../public_html/css');
$compiler->addImportPath(__DIR__ . '/../public_html/css/custom');
$compiler->addImportPath(__DIR__ . '/../public_html/css/base');

$customDir = __DIR__ . '/../public_html/css/custom';

try {
    // Procura todos os arquivos .scss no diretório custom/
    $scssFiles = glob($customDir . '/*.scss');
    
    if (empty($scssFiles)) {
        echo "Nenhum arquivo SCSS encontrado para compilar em $customDir.\n";
        exit(0);
    }
    
    foreach ($scssFiles as $src) {
        $filename = basename($src);
        
        // Ignora arquivos parciais que começam com underscore (se houver)
        if (str_starts_with($filename, '_')) {
            continue;
        }
        
        $dest = $customDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.css';
        
        echo "Compilando: $filename -> " . basename($dest) . "\n";
        
        $scssContent = file_get_contents($src);
        $result = $compiler->compileString($scssContent, $src);
        $cssResult = $result->getCss();
        
        file_put_contents($dest, $cssResult);
    }
    
    echo "Todas as compilações foram concluídas com sucesso!\n";
} catch (\Exception $e) {
    fwrite(STDERR, "Erro na compilação do SCSS: " . $e->getMessage() . "\n");
    exit(1);
}
