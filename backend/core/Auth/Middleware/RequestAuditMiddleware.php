<?php

declare(strict_types=1);

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Alpha\Services\Audit\AuditLoggerService;

/**
 * RequestAuditMiddleware - Intercepta requisições HTTP e registra logs de auditoria detalhados.
 * 
 * Captura: IP real, User-Agent (Navegador, SO, Dispositivo), Método HTTP, Rota, Status Code, 
 * Tempo de Resposta e Identidade do Usuário (Admin, Cliente ou Visitante).
 */
class RequestAuditMiddleware implements MiddlewareInterface
{
    private AuditLoggerService $auditLogger;

    private const IGNORED_EXTENSIONS = [
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 
        'woff', 'woff2', 'ttf', 'eot', 'otf', 'map', 'webp', 'avif'
    ];

    public function __construct(?AuditLoggerService $auditLogger = null)
    {
        $this->auditLogger = $auditLogger ?? new AuditLoggerService();
    }

    public function process(Request $request, Handler $handler): Response
    {
        $startTime = microtime(true);
        $uri = $request->getUri()->getPath();

        // 1. Ignora arquivos estáticos para não sobrecarregar o log
        $extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
        if (in_array($extension, self::IGNORED_EXTENSIONS, true)) {
            return $handler->handle($request);
        }

        // Executa a requisição
        $response = $handler->handle($request);

        // 2. Calcula tempo de execução
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // 3. Extrai IP real
        $ip = $this->getClientIp($request);

        // 4. Extrai User-Agent e analisa metadados
        $userAgent = $request->getHeaderLine('User-Agent') ?: 'Unknown';
        $uaInfo = $this->parseUserAgent($userAgent);

        // 5. Identifica o usuário
        $username = $this->resolveUsername($request);

        // 6. Define o tipo de evento
        $statusCode = $response->getStatusCode();
        $method = strtoupper($request->getMethod());
        $isAdmin = defined('APPLICATION') && APPLICATION === 'admin';
        $event = $isAdmin ? 'admin.request' : 'visitor.request';

        if ($statusCode >= 500) {
            $event = 'http.500_error';
        } elseif ($statusCode === 404) {
            $event = 'http.404_not_found';
        } elseif ($statusCode === 403) {
            $event = 'http.403_forbidden';
        }

        // 7. Monta payload de auditoria
        $payload = [
            'method'       => $method,
            'path'         => $uri,
            'query'        => $request->getUri()->getQuery() ?: null,
            'status'       => $statusCode,
            'duration_ms'  => $durationMs,
            'browser'      => $uaInfo['browser'],
            'browser_ver'  => $uaInfo['browser_version'],
            'os'           => $uaInfo['os'],
            'device'       => $uaInfo['device'],
            'referer'      => $request->getHeaderLine('Referer') ?: null,
        ];

        try {
            $this->auditLogger->logEvent(
                $event,
                $payload,
                $username,
                $ip,
                1
            );
        } catch (\Throwable $e) {
            // Falha silenciosa para não interromper a resposta ao usuário
            error_log("Falha ao registrar auditoria em RequestAuditMiddleware: " . $e->getMessage());
        }

        return $response;
    }

    /**
     * Extrai o IP real do cliente mesmo atrás de CDN/Proxy (Cloudflare, Nginx, AWS).
     */
    private function getClientIp(Request $request): string
    {
        $headers = [
            'CF-Connecting-IP',
            'X-Forwarded-For',
            'X-Real-IP',
            'Client-IP',
        ];

        foreach ($headers as $header) {
            $ipLine = $request->getHeaderLine($header);
            if (!empty($ipLine)) {
                $ips = explode(',', $ipLine);
                $clientIp = trim($ips[0]);
                if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                    return $clientIp;
                }
            }
        }

        $serverParams = $request->getServerParams();
        return $serverParams['REMOTE_ADDR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    /**
     * Identifica o nome do usuário autenticado ou define como VISITANTE.
     */
    private function resolveUsername(Request $request): string
    {
        // Administrador autenticado
        $loggedAdmin = $request->getAttribute('logged_admin');
        if ($loggedAdmin) {
            if (is_object($loggedAdmin) && isset($loggedAdmin->username)) {
                return 'ADMIN: ' . $loggedAdmin->username;
            }
            if (is_array($loggedAdmin) && isset($loggedAdmin['username'])) {
                return 'ADMIN: ' . $loggedAdmin['username'];
            }
        }

        // Cliente logado na loja via sessão
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['customer_id'])) {
            $email = $_SESSION['customer_email'] ?? ('ID#' . $_SESSION['customer_id']);
            return 'CLIENTE: ' . $email;
        }

        return 'VISITANTE';
    }

    /**
     * Analisa o User-Agent e extrai Navegador, Versão, Sistema Operacional e Tipo de Dispositivo.
     */
    private function parseUserAgent(string $ua): array
    {
        $browser = 'Desconhecido';
        $browserVer = '';
        $os = 'Desconhecido';
        $device = 'Desktop';

        // Dispositivo
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(iphone|ipod|blackberry|android|iemobile|opera mini|mobile)/i', $ua)) {
            $device = 'Mobile';
        } elseif (preg_match('/(bot|crawl|spider|slurp|facebookexternalhit)/i', $ua)) {
            $device = 'Bot/Crawler';
        }

        // Sistema Operacional
        if (preg_match('/windows nt 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6\.2/i', $ua)) {
            $os = 'Windows 8';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/windows/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        }

        // Navegador
        if (preg_match('/Edg\/([0-9.]+)/i', $ua, $matches)) {
            $browser = 'Microsoft Edge';
            $browserVer = $matches[1];
        } elseif (preg_match('/(OPR|Opera)\/([0-9.]+)/i', $ua, $matches)) {
            $browser = 'Opera';
            $browserVer = $matches[2];
        } elseif (preg_match('/Chrome\/([0-9.]+)/i', $ua, $matches)) {
            $browser = 'Google Chrome';
            $browserVer = $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/i', $ua, $matches)) {
            $browser = 'Mozilla Firefox';
            $browserVer = $matches[1];
        } elseif (preg_match('/Version\/([0-9.]+).*Safari/i', $ua, $matches)) {
            $browser = 'Apple Safari';
            $browserVer = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/i', $ua, $matches)) {
            $browser = 'Apple Safari';
            $browserVer = $matches[1];
        } elseif (preg_match('/bot|crawler|spider/i', $ua)) {
            $browser = 'Crawler/Bot';
        }

        return [
            'browser'         => $browser,
            'browser_version' => $browserVer,
            'os'              => $os,
            'device'          => $device,
        ];
    }
}
