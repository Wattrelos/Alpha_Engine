<?php

declare(strict_types=1);

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;

/**
 * SecurityHeadersMiddleware - Injeta cabeçalhos defensivos HTTP em todas as respostas da Alpha Engine.
 * 
 * Segue as especificações de segurança da OWASP para mitigar Clickjacking, MIME Sniffing,
 * injeções XSS e vazamentos de origem/referência.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $response = $handler->handle($request);

        $scheme = $request->getUri()->getScheme();
        $isHttps = ($scheme === 'https') || (strtolower($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        $cspPolicy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'"
        ]);

        $response = $response
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->withHeader('Content-Security-Policy', $cspPolicy);

        if ($isHttps) {
            $response = $response->withHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
