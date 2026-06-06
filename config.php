<?php
// Determina o caminho raiz do projeto de forma dinâmica
$root = str_replace('\\', '/', realpath(__DIR__)) . '/';

// HTTP
define('HTTP_SERVER', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/');

// DIR
define('DIR_ROOT', $root);
define('DIR_APPLICATION', DIR_ROOT . 'catalog/');
define('DIR_EXTENSION',   DIR_ROOT . 'extension/');
define('DIR_IMAGE',       DIR_ROOT . 'public_html/');
define('DIR_SYSTEM',      DIR_ROOT . 'system/');
define('DIR_STORAGE',     DIR_ROOT . 'storage/'); // Certifique-se que esta pasta existe

// Validação básica da pasta storage
if (!is_dir(DIR_STORAGE) || !is_writable(DIR_STORAGE)) {
    die('Erro: A pasta storage não foi encontrada ou não possui permissão de escrita em ' . DIR_STORAGE);
}

define('DIR_LANGUAGE',    DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE',    DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG',      DIR_SYSTEM . 'config/');
define('DIR_CACHE',       DIR_STORAGE . 'cache/');    // Caminho: storage/cache/
define('DIR_DOWNLOAD',    DIR_STORAGE . 'download/'); // Caminho: storage/download/
define('DIR_LOGS',        DIR_STORAGE . 'logs/');     // Caminho: storage/logs/
define('DIR_SESSION',     DIR_STORAGE . 'session/');  // Caminho: storage/session/
define('DIR_UPLOAD',      DIR_STORAGE . 'upload/');   // Caminho: storage/upload/

// DB
define('DB_DRIVER', 'mysqli');
// define('DB_HOSTNAME', 'srv1722.hstgr.io'); // Ou o IP do seu servidor de banco
define('DB_HOSTNAME', '127.0.0.1'); // Ou o IP do seu servidor de banco
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '42010052');
define('DB_DATABASE', 'AlphaAgsonhos');
define('DB_PORT', '3306');
define('DB_PREFIX', 'agsc_');
define('DB_SSL_KEY', '');
define('DB_SSL_CERT', '');
define('DB_SSL_CA', '');

// Custom configuration
define('HIDE_ZERO_STOCK', true);
define('ENTITIES_PATH', 'Alpha.Model.Domain.Entities');
define('ACTIONS_PATH', 'Alpha.Controller.Actions');
