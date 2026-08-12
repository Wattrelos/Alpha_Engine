<?php

namespace Alpha\Controller\Actions\Setup;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Throwable;

/**
 * TestDatabaseConnectionAction - Action AJAX para validação instantânea de credenciais MySQL.
 */
class TestDatabaseConnectionAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = (array)$request->getParsedBody();
        
        $host = trim($params['host'] ?? '127.0.0.1');
        $port = trim($params['port'] ?? '3306');
        $user = trim($params['user'] ?? 'root');
        $pass = (string)($params['pass'] ?? '');
        $database = trim($params['database'] ?? '');

        if (empty($host) || empty($user)) {
            return $this->jsonResponse($response, false, 'Host e Usuário do banco de dados são obrigatórios.');
        }

        try {
            // Tenta conectar ao servidor MySQL sem especificar o banco primeiro
            $dsnWithoutDb = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsnWithoutDb, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            if (!empty($database)) {
                // Checa se a base de dados existe
                $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                $stmt->execute([$database]);
                $exists = $stmt->fetchColumn();

                if ($exists) {
                    return $this->jsonResponse($response, true, "Conexão estabelecida com sucesso! Banco de dados '{$database}' localizado.");
                } else {
                    return $this->jsonResponse($response, true, "Conexão com o MySQL bem-sucedida! O banco '{$database}' não existe e será criado automaticamente durante o setup.");
                }
            }

            return $this->jsonResponse($response, true, 'Conexão com o servidor MySQL realizada com sucesso!');
        } catch (Throwable $e) {
            return $this->jsonResponse($response, false, 'Falha na conexão com o MySQL: ' . $e->getMessage());
        }
    }

    private function jsonResponse(Response $response, bool $success, string $message): Response
    {
        $payload = json_encode([
            'success' => $success,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);

        $response->getBody()->write((string)$payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($success ? 200 : 400);
    }
}
