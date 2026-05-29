<?php

define('VERSION', '4.1.0.3');

// Configuration

// 1. Carregar as configurações para definir DIR_SYSTEM, DIR_APPLICATION, etc.
require_once(__DIR__ . '/../config.php');

// 2. Carregar o Autoloader do Composer para resolver o namespace "Alpha" (PSR-4)
if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once(__DIR__ . '/../vendor/autoload.php');
}

// require __DIR__ . '/../tests/TestReadJson.php';
// require __DIR__ . '/../tests/TestEntityByIdJson.php';
require __DIR__ . '/../tests/TestRepositoryJson.php';



// A função deve dar echo nos dados
