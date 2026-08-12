<?php
/**
 * Alpha Engine - Gerador Automático de Documentação de Estrutura de Pastas (Apenas Diretórios)
 * Execução: php tests/scripts_uteis/Gerador-pastas.php
 */

declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/../../'));

if (!ROOT_PATH) {
    die("❌ Erro: Não foi possível determinar a raiz do projeto.\n");
}

// 1. Processa o .gitignore para criar regras de exclusão
$ignoredPatterns = ['.', '..', '.git']; 
$gitignoreFile = ROOT_PATH . '/.gitignore';

if (file_exists($gitignoreFile)) {
    $lines = file($gitignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#')) continue;
        
        $pattern = rtrim($line, '/');
        if (!empty($pattern)) {
            $ignoredPatterns[] = $pattern;
        }
    }
}

function shouldIgnore(string $item, array $patterns): bool {
    foreach ($patterns as $pattern) {
        if ($item === $pattern) return true;
        $regex = '/^' . str_replace(['*', '/'], ['.*', '\/'], preg_quote($pattern, '/')) . '$/';
        if (preg_match($regex, $item)) return true;
    }
    return false;
}

// 2. Função Recursiva modificada para coletar APENAS pastas
function scanDirectoriesOnly(string $dir, array $ignoredPatterns): array {
    $structure = [];
    $items = scandir($dir);

    foreach ($items as $item) {
        if (shouldIgnore($item, $ignoredPatterns)) {
            continue;
        }

        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
        
        // A mágica acontece aqui: só entra se for um diretório
        if (is_dir($fullPath)) {
            $structure[$item] = scanDirectoriesOnly($fullPath, $ignoredPatterns);
        }
    }

    return $structure;
}

// 3. Renderizador PlantUML
function renderPlantUMLFolders(array $structure, string $prefix = ''): string {
    $output = '';
    $total = count($structure);
    $count = 0;

    foreach ($structure as $key => $value) {
        $count++;
        $isLast = ($count === $total);
        $pointer = $isLast ? '└── ' : '├── ';
        $nextPrefix = $prefix . ($isLast ? '    ' : '│   ');

        $output .= $prefix . $pointer . $key . "/\n";
        $output .= renderPlantUMLFolders($value, $nextPrefix);
    }

    return $output;
}

// 4. Renderizador Mermaid (Graph TD)
function renderMermaidFolders(array $structure, string $parentId = 'root', int &$nodeIdCounter = 0): string {
    $output = '';

    foreach ($structure as $key => $value) {
        $nodeIdCounter++;
        $currentId = 'dir_' . $nodeIdCounter;

        $output .= "    {$parentId} --> {$currentId}[\"📂 {$key}/\"]\n";
        $output .= renderMermaidFolders($value, $currentId, $nodeIdCounter);
    }

    return $output;
}

// --- Execução ---

echo "🔍 Mapeando APENAS as pastas do projeto a partir de: " . ROOT_PATH . "\n";
$folderStructure = scanDirectoriesOnly(ROOT_PATH, $ignoredPatterns);

// Geração PlantUML
$pumlOutput = "@startfiles\n.\n" . renderPlantUMLFolders($folderStructure) . "@endfiles\n";

// Geração Mermaid
$mermaidOutput = "```mermaid\ngraph TD\n    root[\"🚀 Alpha Project Root\"]\n" . renderMermaidFolders($folderStructure) . "```\n";

// Exibe na tela
echo "\n=========================================\n";
echo "📂 PLANTUML (APENAS PASTAS):\n";
echo "=========================================\n";
echo $pumlOutput;

echo "\n=========================================\n";
echo "📂 MERMAID GRAPH TD (APENAS PASTAS):\n";
echo "=========================================\n";
echo $mermaidOutput;

// Salva os arquivos de texto limpos
file_put_contents(ROOT_PATH . '/tests/scripts_uteis/pastas_estrutura.puml', $pumlOutput);
file_put_contents(ROOT_PATH . '/tests/scripts_uteis/pastas_estrutura.mmd', $mermaidOutput);

echo "\n💾 Arquivos salvos com sucesso em /tests/scripts_uteis/!\n";
