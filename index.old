<?php
// Modo de Depuração Extrema (Remover em Produção)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Version
define('VERSION', '4.1.0.3');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Tenta carregar as classes não nativas do Opencart (pasta core/):
// 2. Carregar o Autoloader do Composer para resolver o namespace "Alpha" (PSR-4)
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
}
// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Framework
require_once(DIR_SYSTEM . 'framework.php');
