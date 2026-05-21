<?php
/**
 * Alpha Engine
 * Script para consolidar Mappers e organizar os namespaces definitivos.
 */

$baseMappersDir = dirname(__DIR__, 2) . '/core/Mappers/';
$entityMappersDir = $baseMappersDir . 'EntityMappers/';
$domainObserversDir = dirname(__DIR__, 2) . '/core/Model/Domain/Observers/';

if (!is_dir($entityMappersDir)) mkdir($entityMappersDir, 0755, true);
if (!is_dir($domainObserversDir)) mkdir($domainObserversDir, 0755, true);

// Arquivos de infraestrutura/base que ficam na raiz de Mappers
$coreClasses = [
    'AbstractMapper.php',
    'BaseMapper.php',
    'CollectionToArrayConverter.php',
    'MapperFactory.php',
    'MapperInterface.php'
];

// Mover Observers que ficaram perdidos na pasta Mappers
$observers = [
    'OrderObserverInterface.php' => [
        'dest' => $domainObserversDir . 'OrderObserverInterface.php',
        'namespace' => 'Alpha\Model\Domain\Observers'
    ],
    'OrderEmailObserver.php' => [
        'dest' => $domainObserversDir . 'OrderEmailObserver.php',
        'namespace' => 'Alpha\Model\Domain\Observers'
    ]
];

foreach (scandir($baseMappersDir) as $file) {
    if ($file === '.' || $file === '..' || is_dir($baseMappersDir . $file)) continue;

    $filePath = $baseMappersDir . $file;
    $content = file_get_contents($filePath);

    // 1. Observers
    if (isset($observers[$file])) {
        $destPath = $observers[$file]['dest'];
        $newNamespace = $observers[$file]['namespace'];
        $content = preg_replace('/namespace\s+[^;]+;/', "namespace $newNamespace;", $content);
        file_put_contents($destPath, $content);
        unlink($filePath);
        echo "✅ Movido: {$file} -> Model/Domain/Observers/\n";
        continue;
    }

    // 2. Mappers Base (Mantidos)
    if (in_array($file, $coreClasses)) {
        echo "ℹ️ Mantido na Raiz (Core Class): {$file}\n";
        continue;
    }

    // 3. Mappers de Entidade (Consolidação)
    if (str_ends_with($file, 'Mapper.php')) {
        $destPath = $entityMappersDir . $file;
        
        // Ajusta o namespace atual para o definitivo
        $content = preg_replace('/namespace\s+Alpha\\\\(Model\\\\)?Mappers;/', "namespace Alpha\\Mappers\\EntityMappers;", $content);
        
        // Ajusta possíveis imports de outros Mappers que também foram movidos (Ex: CategoryMapper chamando SeoUrlMapper)
        $content = preg_replace('/use Alpha\\\\Mappers\\\\([a-zA-Z0-9]+Mapper);/', "use Alpha\\Mappers\\EntityMappers\\$1;", $content);

        file_put_contents($destPath, $content);
        unlink($filePath);
        echo "✅ Consolidado: {$file} -> EntityMappers/{$file}\n";
    }
}

echo "\n🚀 Limpeza e reestruturação dos Mappers concluída!\n";