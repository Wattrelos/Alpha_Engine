<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Predis\Client as RedisClient;

class SessionMiddleware
{
    private $redis;

    public function __construct()
    {
        // Conecta ao Redis de forma performática
        $this->redis = new RedisClient();
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['session_id'] ?? '';

        // Tenta buscar os dados do cliente guardados na RAM do Redis
        $sessionData = $this->redis->get("sessao:" . $sessionId);

        if (!$sessionData) {
            // Se não tiver sessão válida, barra aqui e redireciona para a tela de login
            $response = new Response();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // Regra de Negócio: Renova/estende o tempo do cliente no Redis por mais 2 horas
        $this->redis->expire("sessao:" . $sessionId, 7200);

        // Transforma os dados da sessão de volta em um array/objeto PHP
        $user = json_decode($sessionData);

        // Injeta os dados do usuário na requisição para o Controller usar depois
        $request = $request->withAttribute('logged_user', $user);

        // Passa a requisição adiante no pipeline do Router
        return $handler->handle($request);
    }
}
