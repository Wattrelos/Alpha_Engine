<?php

declare(strict_types=1);

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Predis\Client as RedisClient;

/**
 * AdminSessionMiddleware - Garante a segurança das rotas administrativas verificando a sessão do admin.
 */
class AdminSessionMiddleware
{
    private ?RedisClient $redis = null;
    private bool $useRedis = false;

    public function __construct()
    {
        try {
            $this->redis = new RedisClient([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                'timeout' => 1.0
            ]);
            $this->redis->connect();
            $this->useRedis = true;
        } catch (\Exception $e) {
            $this->useRedis = false;
        }
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['admin_session_id'] ?? '';

        $sessionData = null;

        if ($this->useRedis && $this->redis) {
            // Tenta buscar os dados do administrador guardados na RAM do Redis
            $sessionData = $this->redis->get("sessao:admin:" . $sessionId);

            if ($sessionData) {
                // Estende o tempo do administrador no Redis por mais 2 horas
                $this->redis->expire("sessao:admin:" . $sessionId, 7200);
            }
        } else {
            // Fallback para sessão local PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_name('admin_session_id');
                if (!empty($sessionId)) {
                    session_id($sessionId);
                }
                session_start();
            }

            $expire = $_SESSION['logged_admin_expire'] ?? 0;
            if ($expire > time()) {
                $sessionData = $_SESSION['logged_admin'] ?? null;
                if ($sessionData) {
                    $_SESSION['logged_admin_expire'] = time() + 7200;
                }
            }
        }

        if (!$sessionData) {
            // Sem sessão válida: redireciona para a tela de login do painel administrativo
            $response = new Response();
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        // Injeta os dados do administrador na requisição para consumo posterior
        $user = json_decode($sessionData);
        $request = $request->withAttribute('logged_admin', $user);

        return $handler->handle($request);
    }
}
