<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Predis\Client as RedisClient;

use Psr\Container\ContainerInterface;

class SessionMiddleware
{
    private ?RedisClient $redis = null;
    private bool $useRedis = false;
    private ?ContainerInterface $container = null;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
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
            $existingSession = $_SESSION ?? [];
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
            if (!empty($existingSession)) {
                $_SESSION = array_merge($_SESSION, $existingSession);
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
        $user = is_string($sessionData) ? json_decode($sessionData) : (object)$sessionData;

        // Injeta os dados do usuário na requisição para o Controller usar depois
        $request = $request->withAttribute('logged_user', $user);

        // Sincroniza $_SESSION para retrocompatibilidade
        if (session_status() === PHP_SESSION_ACTIVE || (session_status() === PHP_SESSION_NONE && !headers_sent())) {
            if (session_status() === PHP_SESSION_NONE) {
                session_name('session_id');
                if (!empty($sessionId)) {
                    @session_id($sessionId);
                }
                @session_start();
            }
            $_SESSION['logged_user'] = is_string($sessionData) ? $sessionData : json_encode($sessionData);
            if ($user instanceof \stdClass && !empty($user->id)) {
                $_SESSION['customer_id'] = $user->id;
                $_SESSION['customer_group_id'] = $user->customer_group_id ?? 1;
                $_SESSION['customer_firstname'] = explode(' ', trim($user->name ?? ''))[0] ?? '';
                $_SESSION['customer_lastname'] = explode(' ', trim($user->name ?? ''), 2)[1] ?? '';
                $_SESSION['customer_email'] = $user->email ?? '';
                $_SESSION['customer_telephone'] = $user->telephone ?? '';
            }
        }

        // Atualiza o Customer helper no container se disponível
        if ($this->container && $this->container->has('customer')) {
            $customerHelper = $this->container->get('customer');
            if ($customerHelper instanceof \Alpha\Support\Customer) {
                $customerHelper->setUser($user);
            }
        }

        // Passa a requisição adiante no pipeline do Router
        return $handler->handle($request);
    }
}
