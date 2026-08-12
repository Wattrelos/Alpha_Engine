<?php
// Determina o caminho raiz do projeto de forma dinâmica
$root = str_replace('\\', '/', realpath(__DIR__)) . '/';

// HTTP
define('HTTP_SERVER', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/');

define('DIR_ROOT', $root);

// Carrega variáveis de ambiente do arquivo .env se ainda não tiverem sido carregadas
if (class_exists('Dotenv\Dotenv') && file_exists(DIR_ROOT . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(DIR_ROOT);
    $dotenv->safeLoad();
}

define('DIR_APPLICATION', DIR_ROOT . 'catalog/');
define('DIR_EXTENSION',   DIR_ROOT . 'extension/');
define('DIR_IMAGE',       (realpath(DIR_ROOT . '../public_html/image') ?: (DIR_ROOT . '../public_html/image')) . '/');
define('DIR_SYSTEM',      DIR_ROOT . 'system/');
define('DIR_STORAGE',     DIR_ROOT . 'storage/');

// Auto-criação e validação inteligente das pastas de armazenamento
$storageDirs = [
    DIR_STORAGE,
    DIR_STORAGE . 'cache/',
    DIR_STORAGE . 'cache/twig_slim/',
    DIR_STORAGE . 'cache/twig_setup/',
    DIR_STORAGE . 'cache/alpha_proxies/',
    DIR_STORAGE . 'download/',
    DIR_STORAGE . 'logs/',
    DIR_STORAGE . 'session/',
    DIR_STORAGE . 'upload/',
];

$failedStorageDir = null;
foreach ($storageDirs as $sDir) {
    if (!is_dir($sDir)) {
        @mkdir($sDir, 0777, true);
    }
    if (is_dir($sDir) && !is_writable($sDir)) {
        @chmod($sDir, 0777);
    }
    if (!is_dir($sDir) || !is_writable($sDir)) {
        $failedStorageDir = $sDir;
        break;
    }
}

if ($failedStorageDir !== null) {
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(503);
    echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissão de Armazenamento | Alpha Engine</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0c0f17; color: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: rgba(22, 27, 38, 0.9); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 40px; max-width: 580px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.6); text-align: center; }
        .icon { font-size: 3rem; margin-bottom: 16px; }
        h1 { font-size: 1.4rem; color: #ef4444; margin-bottom: 12px; font-weight: 700; }
        p { color: #9ca3af; font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px; }
        .path-box { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; border-radius: 8px; padding: 10px 14px; font-family: monospace; font-size: 0.85rem; margin-bottom: 20px; word-break: break-all; }
        .code-box { background: #000; border: 1px solid #333; border-radius: 8px; padding: 14px 18px; font-family: monospace; font-size: 0.9rem; color: #10b981; text-align: center; margin-bottom: 20px; font-weight: bold; }
        .btn-reload { background: #ff6b00; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.2s; }
        .btn-reload:hover { background: #e05d00; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📁</div>
        <h1>Permissão de Armazenamento Ausente</h1>
        <p>A pasta de armazenamento <code>storage/</code> não possui permissão de gravação pelo servidor web no caminho abaixo:</p>
        <div class="path-box">' . htmlspecialchars($failedStorageDir) . '</div>
        <p>Execute o comando abaixo no terminal da raiz do projeto para conceder permissão de gravação:</p>
        <div class="code-box">chmod -R 775 storage/</div>
        <p style="font-size: 0.8rem; color: #6b7280;">Após aplicar a permissão no sistema operacional, recarregue esta página.</p>
        <a href="" onclick="window.location.reload(); return false;" class="btn-reload">Recarregar Página</a>
    </div>
</body>
</html>';
    exit;
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
define('DB_DRIVER',   $_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'mysqli');
define('DB_HOSTNAME', $_ENV['DB_HOSTNAME'] ?? getenv('DB_HOSTNAME') ?: '127.0.0.1');
define('DB_PORT',     $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '');
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
define('DB_PREFIX',   $_ENV['DB_PREFIX'] ?? getenv('DB_PREFIX') ?: 'agsc_');
define('DB_SSL_KEY', '');
define('DB_SSL_CERT', '');
define('DB_SSL_CA', '');

// Admin / Dashboard Masked Directory Configuration
define('ADMIN_DIR',  $_ENV['ADMIN_DIR'] ?? getenv('ADMIN_DIR') ?: 'LPDHED2dC7Gjrg2b');
define('ADMIN_PATH', '/' . ADMIN_DIR);

// Custom configuration
define('HIDE_ZERO_STOCK', true);
define('ENTITIES_PATH', 'Alpha.Model.Domain.Entities');
define('ACTIONS_PATH', 'Alpha.Controller.Actions');
