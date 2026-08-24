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

        if ($this->useRedis && $this->redis && !empty($sessionId)) {
            // Tenta buscar os dados do cliente guardados na RAM do Redis
            $sessionData = $this->redis->get("sessao:" . $sessionId);

            if ($sessionData) {
                // Regra de Negócio: Renova/estende o tempo do cliente no Redis por mais 2 horas
                $this->redis->expire("sessao:" . $sessionId, 7200);
            }
        }

        // Se não encontrou no Redis ou Redis desabilitado, lê da sessão nativa do PHP
        if (!$sessionData) {
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                if (defined('APPLICATION') && APPLICATION === 'admin') {
                    session_name('admin_session_id');
                } else {
                    session_name('session_id');
                }
                if (!empty($sessionId)) {
                    @session_id($sessionId);
                }
                @session_start();
            }

            if (!empty($_SESSION['logged_user'])) {
                $val = $_SESSION['logged_user'];
                $sessionData = is_string($val) ? $val : json_encode($val);
                $_SESSION['expire'] = time() + 7200;
                $_SESSION['logged_user_expire'] = time() + 7200;
            } elseif (!empty($_SESSION['customer_id'])) {
                $sessionData = json_encode([
                    'id' => (int)$_SESSION['customer_id'],
                    'name' => trim(($_SESSION['customer_firstname'] ?? '') . ' ' . ($_SESSION['customer_lastname'] ?? '')),
                    'email' => (string)($_SESSION['customer_email'] ?? ''),
                    'telephone' => (string)($_SESSION['customer_telephone'] ?? ''),
                    'customer_group_id' => (int)($_SESSION['customer_group_id'] ?? 1)
                ]);
                $_SESSION['logged_user'] = $sessionData;
                $_SESSION['expire'] = time() + 7200;
                $_SESSION['logged_user_expire'] = time() + 7200;
            }
        }

        if (!$sessionData) {
            // Se não tiver sessão válida, barra aqui e redireciona para a tela de login mantendo o idioma
            $lang = $request->getAttribute('lang') ?: 'pt-br';
            $response = new Response();
            return $response->withHeader('Location', '/' . $lang . '/login')->withStatus(302);
        }

        // Transforma os dados da sessão de volta em um array/objeto PHP
        $user = json_decode((string)$sessionData);

        // Injeta os dados do usuário na requisição para o Controller usar depois
        $request = $request->withAttribute('logged_user', $user);

        // Passa a requisição adiante no pipeline do Router
        return $handler->handle($request);
    }
}
