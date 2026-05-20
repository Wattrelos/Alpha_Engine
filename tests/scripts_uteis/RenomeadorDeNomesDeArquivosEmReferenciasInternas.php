<?php
// Documentação:
// Com base nas instruções contidas no seu arquivo, desenvolvi o script completo para realizar o mapeamento de arquivos e pastas e a subsequente atualização das referências internas nos arquivos PHP do seu projeto.

//  script está dividido em duas fases principais, conforme solicitado: a criação dos dicionários de mapeamento e a aplicação das substituições baseada na comparação de strings em caixa baixa com os nomes reais (PascalCase) encontrados no sistema de arquivos.
// Destaques da implementação:
// Mapeamento Recursivo: O script utiliza RecursiveDirectoryIterator para garantir que todos os subníveis das pastas afetadas sejam processados.
// Dicionários Persistentes: Como solicitado, o script gera os arquivos DicionarioNomesDeClasses.php e DicionarioNomesDeCaminhos.php que podem ser usados para auditoria.
// Preservação de Separadores: Na fase de caminhos, o script detecta se a referência original usava barra normal (/) ou barra invertida (\) e preserva esse formato, o que é crucial para compatibilidade entre caminhos de sistema de arquivos e Namespaces do PHP/OpenCart.
// Segurança nas Substituições: A ordenação do dicionário de caminhos por comprimento (strlen) evita que um caminho curto (como system) seja substituído antes de um caminho longo (como system/engine/controller), prevenindo corrupção de strings.

/*
* Nome do script: Renomeador de nomes de arquivos em referencias internas.
* O que o script faz?: Percorre todas as pastas do projeto, mapeia nomes reais e atualiza referências internas.
*/

$rootDir = __DIR__;
$affectedFolders = ['catalog', 'extension', 'image', 'kezu4uvcyqd1oevh', 'system'];

$fileDictionaryFile = $rootDir . '/DicionarioNomesDeClasses.php';
$pathDictionaryFile = $rootDir . '/DicionarioNomesDeCaminhos.php';

// --- Passo 1: Construção dos Dicionários ---

$fileMap = [];
$pathMap = [];

echo "Iniciando Passo 1: Mapeamento de arquivos e diretórios...\n";

$directory = new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $item) {
    $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $item->getRealPath());
    $normalizedPath = str_replace('\\', '/', $relativePath);
    
    if ($item->isDir()) {
        // Mapeia o caminho do diretório (minúsculo => Real)
        $pathMap[strtolower($normalizedPath)] = $normalizedPath;
    } elseif ($item->isFile() && $item->getExtension() === 'php') {
        // Mapeia o nome do arquivo (minúsculo => Real)
        $filename = $item->getFilename();
        $fileMap[strtolower($filename)] = $filename;
        
        // Também mapeia o caminho completo do arquivo para referências de inclusão
        $pathMap[strtolower($normalizedPath)] = $normalizedPath;
    }
}

// Salva os dicionários em arquivos PHP para consulta
file_put_contents($fileDictionaryFile, "<?php\nreturn " . var_export($fileMap, true) . ";");
file_put_contents($pathDictionaryFile, "<?php\nreturn " . var_export($pathMap, true) . ";");

echo "Dicionários gerados com sucesso.\n";

// --- Passo 2: Atualização das Referências Internas ---

echo "Iniciando Passo 2: Atualização de referências nos arquivos...\n";

// Carrega os dicionários
$fileMap = require $fileDictionaryFile;
$pathMap = require $pathDictionaryFile;

// Ordena os caminhos por tamanho decrescente para evitar substituições parciais incorretas
uksort($pathMap, function($a, $b) {
    return strlen($b) - strlen($a);
});

foreach ($affectedFolders as $folder) {
    $targetPath = $rootDir . DIRECTORY_SEPARATOR . $folder;
    if (!is_dir($targetPath)) continue;

    $directory = new RecursiveDirectoryIterator($targetPath);
    $iterator = new RecursiveIteratorIterator($directory);

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();
            
            // Pula os próprios arquivos de dicionário se estiverem na rota
            if ($filePath === $fileDictionaryFile || $filePath === $pathDictionaryFile || $filePath === __FILE__) {
                continue;
            }

            $content = file_get_contents($filePath);
            $originalContent = $content;

            // 2.a: Procura e substitui nomes de arquivos .php
            // Regex para capturar algo que termine em .php dentro de aspas ou delimitadores
            $content = preg_replace_callback('/\b([a-z0-9_]+\.php)\b/i', function($matches) use ($fileMap) {
                $lower = strtolower($matches[1]);
                return isset($fileMap[$lower]) ? $fileMap[$lower] : $matches[1];
            }, $content);

            // 2.b: Procura e substitui caminhos de pastas/arquivos
            // Esta parte foca em strings que parecem caminhos (usam / ou \)
            // Vamos iterar pelo dicionário de caminhos para encontrar ocorrências
            foreach ($pathMap as $lowerPath => $realPath) {
                // Escapa os caracteres para regex
                $searchPath = str_replace('/', '[\\\\\/]', preg_quote($lowerPath));
                
                // Tenta encontrar o caminho em strings, respeitando delimitadores de aspas ou barras de namespace
                $pattern = '/(?<=[\'"]|\\\\|^)' . $searchPath . '(?=[\'"]|\\\\|$)/i';
                
                $content = preg_replace_callback($pattern, function($matches) use ($realPath) {
                    // Se o original usava backslash, mantém backslash, se usava slash, mantém slash
                    if (str_contains($matches[0], '\\')) {
                        return str_replace('/', '\\', $realPath);
                    }
                    return $realPath;
                }, $content);
            }

            // Se houve alteração, salva o arquivo
            if ($content !== $originalContent) {
                file_put_contents($filePath, $content);
                echo "Atualizado: " . str_replace($rootDir, '', $filePath) . "\n";
            }
        }
    }
}

echo "Processo de renomeação de referências concluído.\n";