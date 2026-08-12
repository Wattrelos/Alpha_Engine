<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Predis\Client as RedisClient;

class SessionMiddleware
{
    private ?RedisClient $redis = null;
    private bool $useRedis = false;

    public function __construct()
    {
        $redisHost = $_ENV['REDIS_HOST'] ?? '';
        $redisEnabled = filter_var($_ENV['REDIS_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if ($redisEnabled && !empty($redisHost)) {
            try {
                $this->redis = new RedisClient([
                    'host' => $redisHost,
                    'port' => $_ENV['REDIS_PORT'] ?? 6379,
                    'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                    'timeout' => 1.0
                ]);
                $this->redis->connect();
                $this->useRedis = true;
            } catch (\Exception $e) {
                $this->useRedis = false;
            }
        } else {
            $this->useRedis = false;
        }
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['session_id'] ?? '';

        $sessionData = null;

        if ($this->useRedis && $this->redis) {
            // Tenta buscar os dados do cliente guardados na RAM do Redis
            $sessionData = $this->redis->get("sessao:" . $sessionId);

            if ($sessionData) {
                // Regra de Negócio: Renova/estende o tempo do cliente no Redis por mais 2 horas
                $this->redis->expire("sessao:" . $sessionId, 7200);
            }
        } else {
            // Fallback para sessão local PHP com AlphaSessionHandler
            if (session_status() === PHP_SESSION_NONE) {
                session_name('session_id');
                if (!empty($sessionId)) {
                    session_id($sessionId);
                }
                session_start();
            }

            $expire = $_SESSION['expire'] ?? $_SESSION['logged_user_expire'] ?? 0;
            if ($expire > time()) {
                $sessionData = $_SESSION['logged_user'] ?? null;
                if ($sessionData) {
                    // Renova/estende o tempo da sessão local por mais 2 horas
                    $_SESSION['expire'] = time() + 7200;
                    $_SESSION['logged_user_expire'] = time() + 7200;
                }
            }
        }

        if (!$sessionData) {
            // Se não tiver sessão válida, barra aqui e redireciona para a tela de login
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // Transforma os dados da sessão de volta em um array/objeto PHP
        $user = json_decode($sessionData);

        // Injeta os dados do usuário na requisição para o Controller usar depois
        $request = $request->withAttribute('logged_user', $user);

        // Passa a requisição adiante no pipeline do Router
        return $handler->handle($request);
    }
}
