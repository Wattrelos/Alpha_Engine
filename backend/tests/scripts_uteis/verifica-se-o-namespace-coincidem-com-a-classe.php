<?php

// Configurações do mapeamento PSR-4 do seu projeto
// Exemplo: se suas classes começam com "App\" e estão na pasta "src/"
$config = [
    'root_namespace' => 'Alpha\\',
    'root_dir'       => dirname(__DIR__, 2) . '/core/'
];

$errors = [];

// Função que varre as pastas recursivamente
$directory = new RecursiveDirectoryIterator($config['root_dir']);
$iterator = new RecursiveIteratorIterator($directory);

foreach ($iterator as $file) {
    // Filtrar apenas arquivos .php
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);

        // 1. EXTRAIR O NAMESPACE VIA REGEX
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $actualNamespace = trim($matches[1]);

            // 2. CALCULAR O NAMESPACE ESPERADO BASEADO NO CAMINHO DO ARQUIVO
            // Transforma o caminho absoluto em relativo à pasta raiz (src/)
            $relativeDir = dirname(str_replace($config['root_dir'], '', $filePath));

            if ($relativeDir === '.') {
                $expectedNamespace = rtrim($config['root_namespace'], '\\');
            } else {
                // Converte as barras do sistema operacional (\ ou /) em barras de namespace (\)
                $namespacePath = str_replace(['/', '\\'], '\\', $relativeDir);
                $expectedNamespace = rtrim($config['root_namespace'], '\\') . '\\' . $namespacePath;
            }

            // 3. COMPARAR NAMESPACES
            if ($actualNamespace !== $expectedNamespace) {
                $errors[] = [
                    'file' => $filePath,
                    'type' => 'Namespace Incorreto',
                    'message' => "Encontrado: '{$actualNamespace}' | Esperado: '{$expectedNamespace}'"
                ];
            }
        }
    }
}

// EXIBIÇÃO DOS RESULTADOS NO TERMINAL
if (empty($errors)) {
    echo "\033[32m✔ Sucesso! Todos os namespaces estão de acordo com as pastas físicas.\033[0m\n";
    exit(0);
} else {
    echo "\033[31m❌ Foram encontrados erros de estrutura:\033[0m\n\n";
    foreach ($errors as $error) {
        echo "Arquivo: {$error['file']}\n";
        echo "Erro:    {$error['message']}\n";
        echo str_repeat('-', 50) . "\n";
    }
    exit(1);
}
