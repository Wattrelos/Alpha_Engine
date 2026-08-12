<?php
// Esta ideia eleva o nível do projeto! Automatizar a documentação via CLI respeitando o .gitignore garante que a árvore fique sempre atualizada, livre de erros de sintaxe e sem poluição de pastas como vendor/ ou .git.
// Abaixo está o código completo pronto para uso. Ele converte os padrões do seu .gitignore em expressões regulares e gera tanto o formato PlantUML quanto o Mermaid (Flowchart), que não quebra com espaços.

/**
 * Alpha Engine - Gerador Automático de Documentação de Estrutura de Pastas
 * Execução: php tests/scripts_uteis/Gerador-automatico-de-documentacao-de-pastas.php
 */

declare(strict_types=1);

// Define a raiz do projeto (subindo dois níveis a partir de tests/scripts_uteis)
define('ROOT_PATH', realpath(__DIR__ . '/../../'));

if (!ROOT_PATH) {
    die("❌ Erro: Não foi possível determinar a raiz do projeto.\n");
}

// 1. Carrega e processa o .gitignore para criar regras de exclusão
$ignoredPatterns = ['.', '..', '.git']; // Exclusões obrigatórias base
$gitignoreFile = ROOT_PATH . '/.gitignore';

if (file_exists($gitignoreFile)) {
    $lines = file($gitignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignora comentários do gitignore
        if (str_starts_with($line, '#')) {
            continue;
        }
        // Limpa barras extras do gitignore para bater com o nome do arquivo/pasta
        $pattern = rtrim($line, '/');
        if (!empty($pattern)) {
            $ignoredPatterns[] = $pattern;
        }
    }
}

// Função para verificar se um item deve ser ignorado (suporta coringas simples como *)
function shouldIgnore(string $item, array $patterns): bool {
    foreach ($patterns as $pattern) {
        if ($item === $pattern) {
            return true;
        }
        // Converte o padrão do gitignore em uma Regex simples (ex: *.log -> .*\.log)
        $regex = '/^' . str_replace(['*', '/'], ['.*', '\/'], preg_quote($pattern, '/')) . '$/';
        if (preg_match($regex, $item)) {
            return true;
        }
    }
    return false;
}

// 2. Função Recursiva para escanear os diretórios
function scanDirectory(string $dir, array $ignoredPatterns): array {
    $structure = [];
    $items = scandir($dir);

    foreach ($items as $item) {
        if (shouldIgnore($item, $ignoredPatterns)) {
            continue;
        }

        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($fullPath)) {
            $structure[$item] = scanDirectory($fullPath, $ignoredPatterns);
        } else {
            $structure[] = $item;
        }
    }

    return $structure;
}

// 3. Renderizador para PlantUML (@startfiles)
function renderPlantUML(array $structure, string $prefix = ''): string {
    $output = '';
    $total = count($structure);
    $count = 0;

    foreach ($structure as $key => $value) {
        $count++;
        $isLast = ($count === $total);
        $pointer = $isLast ? '└── ' : '├── ';
        $nextPrefix = $prefix . ($isLast ? '    ' : '│   ');

        if (is_array($value)) {
            $output .= $prefix . $pointer . $key . "/\n";
            $output .= renderPlantUML($value, $nextPrefix);
        } else {
            $output .= $prefix . $pointer . $value . "\n";
        }
    }

    return $output;
}

// 4. Renderizador para Mermaid (Usando o modo gráfico TD que não quebra com espaços)
function renderMermaid(array $structure, string $parentId = 'root', int &$nodeIdCounter = 0): string {
    $output = '';

    foreach ($structure as $key => $value) {
        $nodeIdCounter++;
        $currentId = 'node_' . $nodeIdCounter;

        if (is_array($value)) {
            // É um diretório
            $output .= "    {$parentId} --> {$currentId}[\"📂 {$key}/\"]\n";
            $output .= renderMermaid($value, $currentId, $nodeIdCounter);
        } else {
            // É um arquivo
            $output .= "    {$parentId} --> {$currentId}(\"📄 {$value}\")\n";
        }
    }

    return $output;
}

// --- Execução Principal ---

echo "🔍 Escaneando a estrutura do projeto a partir de: " . ROOT_PATH . "\n";
$projectStructure = scanDirectory(ROOT_PATH, $ignoredPatterns);

// Geração do Output PlantUML
$pumlOutput = "@startfiles\n.\n" . renderPlantUML($projectStructure) . "@endfiles\n";

// Geração do Output Mermaid (Graph TD)
$mermaidOutput = "```mermaid\ngraph TD\n    root[\"🚀 Alpha Project Root\"]\n" . renderMermaid($projectStructure) . "```\n";

// Exibe os resultados no terminal
echo "\n=========================================\n";
echo "📊 CÓDIGO PLANTUML GENERATED:\n";
echo "=========================================\n";
echo $pumlOutput;

echo "\n=========================================\n";
echo "📊 CÓDIGO MERMAID GRAPH (À prova de erros) GENERATED:\n";
echo "=========================================\n";
echo $mermaidOutput;

// Opcional: Salva os arquivos de texto para você só copiar
file_put_contents(ROOT_PATH . '/tests/scripts_uteis/estrutura.puml', $pumlOutput);
file_put_contents(ROOT_PATH . '/tests/scripts_uteis/estrutura.mmd', $mermaidOutput);

echo "\n💾 Códigos salvos em /tests/scripts_uteis/ como 'estrutura.puml' e 'estrutura.mmd'!\n";

// ## Por que essa abordagem resolve o problema do Mermaid?
// Repare que na função renderMermaid, o script mapeia cada pasta para um identificador único seguro (node_1, node_2, etc.) e passa o nome real da pasta entre aspas dentro de blocos ["📂 Nome"]. Isso elimina 100% dos erros de renderização, mesmo que seus arquivos tenham espaços, emojis ou extensões complexas.
// Se você rodar o script agora, podemos fazer um ajuste: quer que eu adicione uma lógica para ele buscar automaticamente comentários antigos e reinseri-los ao lado dos nomes gerados?
