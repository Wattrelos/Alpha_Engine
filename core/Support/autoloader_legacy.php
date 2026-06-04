<?php

namespace Alpha\Support;

/**
 * Autoloader Legado de Compatibilidade da Alpha Engine.
 * 
 * Este arquivo é carregado pelo autoloader do Composer e se encarrega de mapear
 * os namespaces clássicosdo código legado que seguem convenções de arquivo em minúsculo
 * (como snake_case para arquivos em system/library/ ou controllers antigos).
 */

spl_autoload_register(function (string $class): void {
    // Mapeamento dos namespaces legados para seus respectivos diretórios
    $paths = [
        'Opencart\\System'    => defined('DIR_SYSTEM') ? DIR_SYSTEM : dirname(__DIR__, 2) . '/system/',
        'Opencart\\Extension' => defined('DIR_EXTENSION') ? DIR_EXTENSION : dirname(__DIR__, 2) . '/extension/',
        'Opencart\\Catalog'   => defined('DIR_APPLICATION') ? DIR_APPLICATION : dirname(__DIR__, 2) . '/public_html/catalog/',
    ];

    foreach ($paths as $nsPrefix => $directory) {
        if (strpos($class, $nsPrefix) === 0) {
            // Conversão de camelCase para snake_case e lowercase, idêntico à regrado código legado original
            $relativeClass = substr($class, strlen($nsPrefix));
            $convertedPath = trim(str_replace('\\', '/', strtolower(preg_replace('~([a-z])([A-Z]|[0-9])~', '\1_\2', $relativeClass))), '/');
            $file = $directory . $convertedPath . '.php';

            if (is_file($file)) {
                include_once $file;
                return;
            }
        }
    }
});

