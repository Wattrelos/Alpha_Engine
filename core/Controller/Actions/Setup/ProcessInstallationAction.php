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
        $database = trim($params['db_name'] ?? 'AlphaAgsonhos');
        $prefix = trim($params['db_prefix'] ?? 'agsc_');

        $storeName = trim($params['store_name'] ?? 'AgSonhos E-commerce');
        $storeEmail = trim($params['store_email'] ?? 'atendimento@agsonhos.com');

        $adminFirstname = trim($params['admin_firstname'] ?? 'Administrador');
        $adminLastname = trim($params['admin_lastname'] ?? 'SaaS');
        $adminUser = trim($params['admin_user'] ?? 'admin');
        $adminEmail = trim($params['admin_email'] ?? 'admin@agsonhos.com');
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

            // 4. Gravação de Configurações da Loja
            $stmtConfig = $pdo->prepare("INSERT INTO `{$prefix}setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES (0, 'config', 'config_name', ?, 0), (0, 'config', 'config_email', ?, 0) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            $stmtConfig->execute([$storeName, $storeEmail]);

            // 5. Criação do Super Admin (Hash Argon2ID)
            $adminHash = password_hash($adminPass, PASSWORD_ARGON2ID);
            
            // Limpa usuários existentes com o mesmo username ou insere
            $stmtCheckUser = $pdo->prepare("DELETE FROM `{$prefix}user` WHERE `username` = ? OR `email` = ?");
            $stmtCheckUser->execute([$adminUser, $adminEmail]);

            $stmtAdmin = $pdo->prepare("INSERT INTO `{$prefix}user` (`user_group_id`, `username`, `password`, `firstname`, `lastname`, `email`, `image`, `code`, `ip`, `status`, `date_added`) VALUES (1, ?, ?, ?, ?, ?, '', '', '127.0.0.1', 1, ?)");
            $stmtAdmin->execute([
                $adminUser,
                $adminHash,
                $adminFirstname,
                $adminLastname,
                $adminEmail,
                date('Y-m-d H:i:s')
            ]);

            // 6. Atualização Atômica do Arquivo .env
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
                'redirect' => '/LPDHED2dC7Gjrg2b'
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
