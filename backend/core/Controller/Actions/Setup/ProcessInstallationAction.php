<?php

namespace Alpha\Controller\Actions\Setup;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Alpha\Support\EnvironmentManager;
use PDO;
use Throwable;

/**
 * ProcessInstallationAction - Executa o Pipeline Completo de Provisionamento (Tenant Provisioning).
 */
class ProcessInstallationAction
{
    private EnvironmentManager $envManager;

    public function __construct(?EnvironmentManager $envManager = null)
    {
        $this->envManager = $envManager ?? new EnvironmentManager();
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if ($this->envManager->isInstalled()) {
            return $this->jsonResponse($response, false, 'O sistema já está instalado.', 403);
        }

        $params = (array)$request->getParsedBody();

        // Extração de parâmetros
        $host = trim($params['db_host'] ?? '127.0.0.1');
        $port = trim($params['db_port'] ?? '3306');
        $user = trim($params['db_user'] ?? 'root');
        $pass = (string)($params['db_pass'] ?? '');
        $database = trim($params['db_name'] ?? 'MyDatabase');
        
        $prefix = trim($params['db_prefix'] ?? 'agsc_');
        $prefix = preg_replace('/[^a-zA-Z0-9_]/', '', $prefix);
        if (empty($prefix)) {
            $prefix = 'agsc_';
        }
        if (!str_ends_with($prefix, '_')) {
            $prefix .= '_';
        }

        $adminDir = trim($params['admin_dir'] ?? '');
        $adminDir = preg_replace('/[^a-zA-Z0-9_-]/', '', $adminDir);
        if (empty($adminDir)) {
            $adminDir = 'adm_' . EnvironmentManager::generateRandomKey(12);
        }

        $storeName = trim($params['store_name'] ?? 'My Store');
        $storeEmail = trim($params['store_email'] ?? '');

        $adminFirstname = trim($params['admin_firstname'] ?? 'Administrador');
        $adminLastname = trim($params['admin_lastname'] ?? 'On-Premise');
        $adminUser = trim($params['admin_user'] ?? 'admin');
        $adminEmail = trim($params['admin_email'] ?? 'admin@mystore.com');
        $adminPass = (string)($params['admin_pass'] ?? '');
        $adminPassConfirm = (string)($params['admin_pass_confirm'] ?? '');

        // Validações
        if (empty($host) || empty($user) || empty($database)) {
            return $this->jsonResponse($response, false, 'Preencha todos os campos da conexão com o banco de dados.');
        }

        if (empty($storeName) || empty($storeEmail)) {
            return $this->jsonResponse($response, false, 'Preencha o Nome e E-mail da Loja.');
        }

        if (empty($adminUser) || empty($adminEmail) || empty($adminPass)) {
            return $this->jsonResponse($response, false, 'Preencha todos os dados do Administrador.');
        }

        if ($adminPass !== $adminPassConfirm) {
            return $this->jsonResponse($response, false, 'A senha do Administrador e a confirmação não coincidem.');
        }

        if (strlen($adminPass) < 6) {
            return $this->jsonResponse($response, false, 'A senha do Administrador deve ter no mínimo 6 caracteres.');
        }

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse($response, false, 'O e-mail do Administrador é inválido.');
        }

        try {
            // 1. Conexão ao Servidor MySQL
            $dsnWithoutDb = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsnWithoutDb, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 10,
            ]);

            // 2. Criação da Base de Dados (se não existir)
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$database}`");

            // 3. Importação do Esquema DDL e Seeds (install.sql)
            $schemaFile = realpath(__DIR__ . '/../../../../resources/schema/install.sql');
            if (!file_exists($schemaFile)) {
                return $this->jsonResponse($response, false, 'Arquivo de esquema de instalação (install.sql) não encontrado.');
            }

            $sqlContent = file_get_contents($schemaFile);
            if ($prefix !== 'agsc_') {
                $sqlContent = str_replace('agsc_', $prefix, $sqlContent);
            }

            $pdo->exec($sqlContent);

            // 4. Gravação de Configurações da Loja (store_id = 1)
            $stmtConfig = $pdo->prepare("INSERT INTO `{$prefix}setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES 
                (1, 'config', 'config_name', ?, 0), 
                (1, 'config', 'config_email', ?, 0),
                (1, 'config', 'config_db_prefix', ?, 0),
                (1, 'config', 'config_admin_dir', ?, 0)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            $stmtConfig->execute([$storeName, $storeEmail, $prefix, $adminDir]);

            // 5. Criação do Super Admin (Hash Argon2ID)
            $adminHash = password_hash($adminPass, PASSWORD_ARGON2ID);

            // Limpa usuários existentes com o mesmo username ou insere
            $stmtCheckUser = $pdo->prepare("DELETE FROM `{$prefix}user` WHERE `username` = ? OR `email` = ?");
            $stmtCheckUser->execute([$adminUser, $adminEmail]);

            $stmtAdmin = $pdo->prepare("INSERT INTO `{$prefix}user` (`user_group_id`, `username`, `password`, `firstname`, `lastname`, `email`, `image`, `ip`, `status`, `date_added`) VALUES (1, ?, ?, ?, ?, ?, '', '127.0.0.1', 1, ?)");
            $stmtAdmin->execute([
                $adminUser,
                $adminHash,
                $adminFirstname,
                $adminLastname,
                $adminEmail,
                date('Y-m-d H:i:s')
            ]);

            // 6. Gerenciamento do Diretório Físico do Dashboard em public_html/
            $publicHtmlDir = realpath(__DIR__ . '/../../../../../public_html') ?: (dirname(__DIR__, 5) . '/public_html');
            $targetAdminDir = $publicHtmlDir . '/' . $adminDir;

            // Busca por diretório administrativo pré-existente (ex: LPDHED2dC7Gjrg2b ou um admin antigo)
            $existingAdminDir = null;
            if (is_dir($publicHtmlDir)) {
                $items = scandir($publicHtmlDir);
                if ($items !== false) {
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..' || $item === $adminDir) continue;
                        $itemPath = $publicHtmlDir . '/' . $item;
                        if (is_dir($itemPath) && file_exists($itemPath . '/index.php')) {
                            $content = file_get_contents($itemPath . '/index.php');
                            if ($content !== false && (str_contains($content, 'APPLICATION') || str_contains($content, 'AppBootstrap'))) {
                                $existingAdminDir = $itemPath;
                                break;
                            }
                        }
                    }
                }
            }

            // Se encontrou pasta admin anterior (ex: LPDHED2dC7Gjrg2b) e a nova pasta for diferente
            if ($existingAdminDir && $existingAdminDir !== $targetAdminDir) {
                if (!is_dir($targetAdminDir)) {
                    @rename($existingAdminDir, $targetAdminDir);
                } else {
                    // Mover arquivos para o diretório alvo
                    if (file_exists($existingAdminDir . '/index.php') && !file_exists($targetAdminDir . '/index.php')) {
                        @rename($existingAdminDir . '/index.php', $targetAdminDir . '/index.php');
                    }
                    if (file_exists($existingAdminDir . '/.htaccess') && !file_exists($targetAdminDir . '/.htaccess')) {
                        @rename($existingAdminDir . '/.htaccess', $targetAdminDir . '/.htaccess');
                    }
                    // Remove o diretório antigo para não expor a pasta modelo
                    @rmdir($existingAdminDir);
                }
            }

            // Cria o diretório de destino caso não exista
            if (!is_dir($targetAdminDir)) {
                @mkdir($targetAdminDir, 0755, true);
            }

            // Garante o arquivo .htaccess
            if (!file_exists($targetAdminDir . '/.htaccess')) {
                file_put_contents($targetAdminDir . '/.htaccess', "RewriteEngine On\nOptions -Indexes\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^ index.php [QSA,L]\n");
            }

            // Garante o arquivo index.php no diretório do dashboard
            if (!file_exists($targetAdminDir . '/index.php')) {
                $indexTemplate = "<?php\n\n" .
                    "use Slim\Factory\AppFactory;\n" .
                    "use Slim\Views\Twig;\n" .
                    "use Slim\Views\TwigMiddleware;\n" .
                    "use Containers\AppBootstrap;\n\n" .
                    "require __DIR__ . '/../../backend/vendor/autoload.php';\n\n" .
                    "if (file_exists(__DIR__ . '/../../backend/.env')) {\n" .
                    "    \$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../backend/');\n" .
                    "    \$dotenv->safeLoad();\n" .
                    "}\n\n" .
                    "if (!defined('APPLICATION')) {\n" .
                    "    define('APPLICATION', 'admin');\n" .
                    "}\n\n" .
                    "require_once __DIR__ . '/../../backend/config.php';\n\n" .
                    "\$bootstrap = AppBootstrap::boot();\n" .
                    "\$container = \$bootstrap->getContainer();\n" .
                    "\$configSettings = \$bootstrap->getConfigSettings();\n" .
                    "\$language = \$bootstrap->getLanguage();\n" .
                    "\$seoUrlRepository = \$bootstrap->getSeoUrlRepository();\n\n" .
                    "\$appEnv = \$_ENV['APP_ENV'] ?? 'production';\n" .
                    "\$appDebug = filter_var(\$_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);\n" .
                    "\$isDev = (\$appEnv === 'development') && \$appDebug;\n\n" .
                    "\$twigCacheDir = __DIR__ . '/../../backend/storage/cache/twig_slim';\n" .
                    "if (!is_dir(\$twigCacheDir)) {\n" .
                    "    @mkdir(\$twigCacheDir, 0777, true);\n" .
                    "}\n" .
                    "@chmod(\$twigCacheDir, 0777);\n\n" .
                    "\$twig = Twig::create(__DIR__ . '/../../backend/resources/views', [\n" .
                    "    'cache'       => \$twigCacheDir,\n" .
                    "    'auto_reload' => \$isDev,\n" .
                    "    'debug'       => \$isDev,\n" .
                    "]);\n" .
                    "\$twigEnv = \$twig->getEnvironment();\n" .
                    "\$twigEnv->addExtension(new \Alpha\Support\Twig\UrlExtension(\$seoUrlRepository));\n\n" .
                    "\$twigEnv->addGlobal('settings',   \$configSettings);\n" .
                    "\$twigEnv->addGlobal('name',       \$configSettings['config_name'] ?? 'AG Sonhos e Construções');\n" .
                    "\$twigEnv->addGlobal('lang',       \$language ? \$language->getCode() : 'pt-br');\n" .
                    "\$twigEnv->addGlobal('admin_dir',  defined('ADMIN_DIR') ? ADMIN_DIR : basename(__DIR__));\n" .
                    "\$twigEnv->addGlobal('admin_path', defined('ADMIN_PATH') ? ADMIN_PATH : '/' . basename(__DIR__));\n\n" .
                    "\$container->bind(\\Twig\\Environment::class, \$twigEnv);\n" .
                    "\$container->bind(Twig::class, \$twig);\n\n" .
                    "AppFactory::setContainer(\$container);\n" .
                    "\$app = AppFactory::create();\n\n" .
                    "\$app->setBasePath('/' . (defined('ADMIN_DIR') ? ADMIN_DIR : basename(__DIR__)));\n\n" .
                    "use Alpha\Auth\Middleware\CsrfGuardMiddleware;\n" .
                    "use Alpha\Auth\Middleware\SecurityHeadersMiddleware;\n\n" .
                    "\$app->add(TwigMiddleware::create(\$app, \$twig));\n" .
                    "\$app->add(new CsrfGuardMiddleware(\$twigEnv));\n" .
                    "\$app->addBodyParsingMiddleware();\n" .
                    "\$app->add(new SecurityHeadersMiddleware());\n" .
                    "\$app->addRoutingMiddleware();\n\n" .
                    "\$routes = require __DIR__ . '/../../backend/Config/Routes.php';\n" .
                    "\$routes(\$app);\n\n" .
                    "\$app->run();\n";
                file_put_contents($targetAdminDir . '/index.php', $indexTemplate);
            }

            // 7. Atualização Atômica do Arquivo .env
            $envData = [
                'APP_ENV'              => 'development',
                'APP_DEBUG'            => 'true',
                'APP_INSTALLED'        => 'true',
                'DB_DRIVER'            => 'mysqli',
                'DB_HOSTNAME'          => $host,
                'DB_PORT'              => $port,
                'DB_USERNAME'          => $user,
                'DB_PASSWORD'          => $pass,
                'DB_DATABASE'          => $database,
                'DB_PREFIX'            => $prefix,
                'ADMIN_DIR'            => $adminDir,
                'JWT_SECRET_KEY'       => EnvironmentManager::generateRandomKey(32),
                'API_SIGNATURE_SECRET' => EnvironmentManager::generateRandomKey(32),
                'REDIS_HOST'           => '127.0.0.1',
                'REDIS_PORT'           => '6379',
                'REDIS_PASSWORD'       => '',
            ];

            if (!$this->envManager->updateEnv($envData)) {
                return $this->jsonResponse($response, false, 'Não foi possível gravar o arquivo .env com segurança.');
            }

            return $this->jsonResponse($response, true, 'Instalação concluída com sucesso! O sistema foi provisionado e configurado.', 200, [
                'redirect' => '/' . $adminDir
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse($response, false, 'Erro no provisionamento: ' . $e->getMessage());
        }
    }

    private function jsonResponse(Response $response, bool $success, string $message, int $statusCode = 200, array $extra = []): Response
    {
        $payload = array_merge([
            'success' => $success,
            'message' => $message,
        ], $extra);

        $response->getBody()->write((string)json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
