<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Predis\Client as RedisClient;

/* Este arquivo SignatureMiddleware irá atuar como o primeiro escudo do seu roteador.
*  Ele valida se os dados da requisição vieram de uma fonte confiável e se não foram alterados no caminho.
*/

class SignatureMiddleware
{
    private $redis;
    private $apiSecret;

    public function __construct()
    {
        $this->apiSecret = $_ENV['API_SIGNATURE_SECRET'] ?? 'sua_chave_secreta_e_muito_longa_123';

        try {
            // Conecta ao Redis com as credenciais do .env
            $this->redis = new RedisClient([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                'timeout' => 1.0
            ]);
            $this->redis->connect();
        } catch (\Throwable $e) {
            $this->redis = null;
        }
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        // 1. Pega os dados enviados e a assinatura que veio no cabeçalho (Header)
        $signatureReceived = $request->getHeaderLine('X-Signature');
        $bodyContent = (string)$request->getBody();
        $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // 2. CONTROLE DE ABUSO (Rate Limit com Redis / Cache)
        // Se alguém tentar quebrar a assinatura por força bruta, bloqueia o IP
        $chaveErros = "erros:assinatura:" . $ipCliente;
        $errosSeguidos = 0;
        try {
            if ($this->redis) {
                $errosSeguidos = (int)$this->redis->get($chaveErros);
            }
        } catch (\Throwable $e) {
            // Fallback se Redis não estiver conectado
        }

        if ($errosSeguidos >= 5) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Muitas tentativas inválidas. Bloqueado por 15 minutos.'
            ]));
            return $response->withStatus(429)->withHeader('Content-Type', 'application/json');
        }

        // 3. VALIDAÇÃO MATEMÁTICA DA ASSINATURA
        // Recria o hash usando o corpo da requisição e a chave secreta
        $expectedSignature = hash_hmac('sha256', $bodyContent, $this->apiSecret);

        // Usa hash_equals para evitar ataques de tempo (timing attacks)
        if (empty($signatureReceived) || !hash_equals($expectedSignature, $signatureReceived)) {

            // Incrementa o erro no Redis e bota validade de 15 minutos (900 segundos)
            try {
                if ($this->redis) {
                    $this->redis->incr($chaveErros);
                    $this->redis->expire($chaveErros, 900);
                }
            } catch (\Throwable $e) {
                // Ignora falha de Redis
            }

            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Assinatura da requisição inválida ou ausente.'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // 4. SUCESSO
        // Se a assinatura bateu, limpamos o contador de erros do Redis para esse IP
        try {
            if ($this->redis) {
                $this->redis->del($chaveErros);
            }
        } catch (\Throwable $e) {}

        // Passa a requisição adiante para os próximos middlewares ou controllers
        return $handler->handle($request);
    }
}
