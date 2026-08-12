<?php

declare(strict_types=1);

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Predis\Client as RedisClient;

/**
 * RateLimitMiddleware - Middleware de Limitação de Taxa por IP com suporte a Redis e Fallback de Arquivos.
 * 
 * Protege a aplicação contra ataques de força bruta, estouro de cotas e DoS.
 * Se o Redis estiver indisponível (ex: ambiente local sem daemon Redis), utiliza fallback
 * de cache de arquivos em storage/cache/rate_limit/.
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private ?RedisClient $redis = null;
    private bool $useRedis = false;
    private int $maxRequests;
    private int $decaySeconds;
    private string $routeGroup;

    public function __construct(
        int $maxRequests = 60,
        int $decaySeconds = 60,
        string $routeGroup = 'global',
        ?RedisClient $redis = null
    ) {
        $this->maxRequests = $maxRequests;
        $this->decaySeconds = $decaySeconds;
        $this->routeGroup = $routeGroup;

        if ($redis !== null) {
            $this->redis = $redis;
            $this->useRedis = true;
        } else {
            $redisHost = $_ENV['REDIS_HOST'] ?? '';
            $redisEnabled = filter_var($_ENV['REDIS_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);

            if ($redisEnabled && !empty($redisHost)) {
                try {
                    $this->redis = new RedisClient([
                        'host'     => $redisHost,
                        'port'     => $_ENV['REDIS_PORT'] ?? 6379,
                        'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                        'timeout'  => 1.0
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
    }

    public function process(Request $request, Handler $handler): Response
    {
        $ip = $this->getClientIp($request);
        $key = sprintf('rate_limit:%s:%s', $this->routeGroup, md5($ip));

        $current = 0;
        $ttl = $this->decaySeconds;

        if ($this->useRedis && $this->redis) {
            try {
                $current = (int)$this->redis->incr($key);

                if ($current === 1) {
                    $this->redis->expire($key, $this->decaySeconds);
                }

                $ttl = (int)$this->redis->ttl($key);
                if ($ttl <= 0) {
                    $ttl = $this->decaySeconds;
                }
            } catch (\Exception $e) {
                // Fallback para arquivo em caso de erro na comunicação com Redis
                $res = $this->hitFileCache($key);
                $current = $res['current'];
                $ttl = $res['ttl'];
            }
        } else {
            // Fallback de armazenamento local em arquivo
            $res = $this->hitFileCache($key);
            $current = $res['current'];
            $ttl = $res['ttl'];
        }

        // Excedeu a cota de requisições permitidas
        if ($current > $this->maxRequests) {
            $response = new \Slim\Psr7\Response();

            $isXmlHttpRequest = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
            $acceptsJson = str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json');

            $response = $response
                ->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
                ->withHeader('X-RateLimit-Remaining', '0')
                ->withHeader('Retry-After', (string)$ttl)
                ->withStatus(429);

            if ($isXmlHttpRequest || $acceptsJson) {
                $response->getBody()->write((string)json_encode([
                    'error' => [
                        'warning' => sprintf('Muitas requisições efetuadas. Por favor, aguarde %d segundo(s) antes de tentar novamente.', $ttl)
                    ]
                ], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json');
            }

            $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>429 Too Many Requests</title>'
                . '<style>body{font-family:sans-serif;text-align:center;padding:50px;background:#f8fafc;color:#334155;}'
                . '.card{background:#fff;max-width:500px;margin:0 auto;padding:30px;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);}'
                . 'h1{color:#e11d48;font-size:24px;}p{margin:15px 0;}</style>'
                . '</head><body><div class="card">'
                . '<h1>429 - Limite de Requisições Excedido</h1>'
                . '<p>Você efetuou muitas requisições em um curto período de tempo.</p>'
                . sprintf('<p>Por favor, aguarde <strong>%d segundos</strong> antes de recarregar.</p>', $ttl)
                . '</div></body></html>';

            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        // Dentro da cota: Processa a requisição e injeta cabeçalhos de controle
        $response = $handler->handle($request);
        $remaining = max(0, $this->maxRequests - $current);

        return $response
            ->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string)$remaining);
    }

    /**
     * Fallback de contagem via arquivos locais para ambientes sem servidor Redis ativo.
     */
    private function hitFileCache(string $key): array
    {
        $dir = __DIR__ . '/../../storage/cache/rate_limit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $file = $dir . '/' . md5($key) . '.json';
        $now = time();
        $data = ['count' => 0, 'expires' => $now + $this->decaySeconds];

        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['expires']) && $decoded['expires'] > $now) {
                    $data = $decoded;
                }
            }
        }

        $data['count']++;
        @file_put_contents($file, json_encode($data));

        $ttl = max(1, $data['expires'] - $now);

        return [
            'current' => $data['count'],
            'ttl'     => $ttl
        ];
    }

    /**
     * Extrai o IP real do cliente tratando proxies e balanceadores de carga.
     */
    private function getClientIp(Request $request): string
    {
        $server = $request->getServerParams();

        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $server['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        if (!empty($server['HTTP_CLIENT_IP'])) {
            return trim($server['HTTP_CLIENT_IP']);
        }

        return $server['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
