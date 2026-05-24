<?php
/**
 * Alpha Engine
 * Script utilitário para detectar Entidades Zumbis e anomalias de mapeamento (Atributos vs Colunas).
 */
require_once dirname(__DIR__, 2) . '/config.php';
require_once DIR_SYSTEM . 'helper/db_schema.php';

// Tenta carregar o autoloader para o ReflectionClass funcionar
if (file_exists(dirname(__DIR__, 2) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
}

$schema = oc_db_schema();
$schemaTables = [];
$schemaPrimaryKeys = [];
foreach ($schema as $table) {
    $schemaTables[$table['name']] = array_column($table['field'], 'name');
    $schemaPrimaryKeys[$table['name']] = $table['primary'] ?? [];
}

$entitiesDir = dirname(__DIR__, 2) . '/core/Model/Domain/Entities/';
if (!is_dir($entitiesDir)) {
    die("Diretório de entidades não encontrado em: {$entitiesDir}\n");
}

$files = glob($entitiesDir . '*.php');
$zombies = [];
$mismatches = [];

// Whitelist de arquivos que moram na pasta mas não são mapeamentos 1:1 de tabelas
$whitelist = [
    'BaseEntity', 
    'InterfaceEntity',
    'InjetarInterface' // Seu script utilitário interno
];

function camelToSnake(string $name): string {
    return strtolower(preg_replace('/(?<!^)([A-Z]|(?<=[a-zA-Z])[0-9])/', '_$1', $name));
}

foreach ($files as $file) {
    $className = pathinfo($file, PATHINFO_FILENAME);
    if (in_array($className, $whitelist)) continue;
    
    $tableName = camelToSnake($className);
    
    // Regra especial para palavra reservada no PHP
    if ($className === 'OrderReturn') {
        $tableName = 'return';
    }
    
    if (!isset($schemaTables[$tableName])) {
        $zombies[] = "❌ {$className}.php (Tabela esperada: '{$tableName}')";
        continue;
    }

    // Tabela existe, vamos analisar as colunas vs atributos
    $dbColumns = $schemaTables[$tableName];
    $phpColumns = [];

    $fqcn = 'Alpha\\Model\\Domain\\Entities\\' . $className;
    
    if (!class_exists($fqcn)) {
        require_once $file;
    }

    if (class_exists($fqcn)) {
        $reflection = new ReflectionClass($fqcn);
        
        foreach ($reflection->getProperties() as $prop) {
            if ($prop->isStatic()) continue;
            
            $propName = $prop->getName();
            $isCollection = false;
            $isManyToOne = false;
            $foreignKey = null;

            foreach ($prop->getAttributes() as $attr) {
                $attrName = $attr->getName();
                if (str_ends_with($attrName, 'OneToMany') || str_ends_with($attrName, 'ManyToMany')) {
                    $isCollection = true;
                }
                if (str_ends_with($attrName, 'ManyToOne') || str_ends_with($attrName, 'HasOne')) {
                    $isManyToOne = true;
                    $args = $attr->getArguments();
                    $foreignKey = $args['foreignKey'] ?? null;
                }
            }

            // Ignora coleções (Elas não possuem colunas reais na tabela de origem)
            if ($isCollection) continue;

            $colName = camelToSnake($propName);

            // Resolução inteligente de chaves estrangeiras (Padrão Alpha Engine)
            if ($isManyToOne) {
                $colName = $foreignKey ? camelToSnake($foreignKey) : $colName . '_id';
            }

            $phpColumns[] = $colName;
        }

        // O Reflection pode não pegar o 'id' se for private na BaseEntity, então garantimos a inclusão manual para checagem
        if (!in_array('id', $phpColumns)) {
            $phpColumns[] = 'id';
        }

        $missingInDb = array_diff($phpColumns, $dbColumns);
        $missingInPhp = array_diff($dbColumns, $phpColumns);

        // Ignorar o alerta de 'id' sobrando caso a tabela possua chave composta ou não tenha a coluna 'id' real no BD
        $primaryKeys = $schemaPrimaryKeys[$tableName] ?? [];
        if (!in_array('id', $dbColumns) || count($primaryKeys) > 1) {
            $missingInDb = array_filter($missingInDb, fn($col) => $col !== 'id');
        }

        if (!empty($missingInDb) || !empty($missingInPhp)) {
            $mismatches[$className] = [
                'table' => $tableName,
                'not_in_db' => $missingInDb,
                'not_in_php' => $missingInPhp
            ];
        }
    }
}

echo "🔍 Auditoria Profunda de Entidades e Mapeamento ORM\n";
echo str_repeat("-", 60) . "\n";

if (empty($zombies)) {
    echo "✅ Nenhuma entidade zumbi detectada!\n";
} else {
    echo "⚠️ Entidades Zumbis (Sem tabela):\n";
    foreach ($zombies as $zombie) {
        echo "   " . $zombie . "\n";
    }
}

echo "\n";

if (empty($mismatches)) {
    echo "✅ Nenhum atributo órfão detectado! 100% de sincronia entre PHP e Banco de Dados.\n";
} else {
    echo "⚠️ Anomalias de Mapeamento (Atributos vs Colunas):\n";
    foreach ($mismatches as $class => $data) {
        echo " 🔹 {$class} (Tabela: {$data['table']})\n";
        
        if (!empty($data['not_in_db'])) {
            echo "    ❌ Sobrando no PHP (Sem coluna correspondente no BD): " . implode(', ', $data['not_in_db']) . "\n";
        }
        
        if (!empty($data['not_in_php'])) {
            echo "    ⚠️ Faltando no PHP (Coluna esquecida): " . implode(', ', $data['not_in_php']) . "\n";
        }
        echo "\n";
    }
}

echo "💡 Ação Pós-Auditoria:\n";
echo "- Se o PHP aponta que está 'Sobrando', talvez você deva deletar a propriedade da Entidade (ou ela não será salva).\n";
echo "- Se o PHP aponta que está 'Faltando', significa que há colunas na tabela que o seu ORM não vai conseguir ler nem escrever.\n";