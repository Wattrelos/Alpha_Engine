<?php

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Alpha\Support\EnvironmentManager;
use Slim\Psr7\Response;

/**
 * InstallationCheckMiddleware - Middleware Global para Controle de Estado de Instalação.
 * 
 * - Se APP_INSTALLED=false: Bloqueia qualquer rota do e-commerce/admin e redireciona para /setup.
 * - Se APP_INSTALLED=true: Bloqueia o acesso a /setup com status 403 Forbidden.
 */
class InstallationCheckMiddleware implements MiddlewareInterface
{
    private EnvironmentManager $envManager;

    public function __construct(?EnvironmentManager $envManager = null)
    {
        $this->envManager = $envManager ?? new EnvironmentManager();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        // Ignora arquivos estáticos de mídia ou CSS/JS servidos diretamente
        if (preg_match('/\.(png|jpg|jpeg|gif|css|js|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return $handler->handle($request);
        }

        $isSetupRoute = (str_starts_with($path, '/setup') || str_starts_with($path, '/install'));
        $isInstalled = $this->envManager->isInstalled();

        if (!$isInstalled) {
            // Sistema NÃO instalado
            if (!$isSetupRoute) {
                $response = new Response();
                return $response
                    ->withHeader('Location', '/setup')
                    ->withStatus(302);
            }
            return $handler->handle($request);
        }

        // Sistema JÁ instalado
        if ($isSetupRoute) {
            $response = new Response();
            
            $acceptsJson = str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json') ||
                          strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';

            if ($acceptsJson) {
                $response->getBody()->write((string)json_encode([
                    'error' => [
                        'message' => 'O sistema já está instalado. Acesso ao assistente de setup proibido.'
                    ]
                ], JSON_UNESCAPED_UNICODE));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(403);
            }

            $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>403 Proibido</title>' .
                    '<style>body{font-family:sans-serif;background:#121212;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}' .
                    '.box{background:#1e1e1e;padding:40px;border-radius:12px;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,0.5);max-width:480px;}' .
                    'h1{color:#e74c3c;margin-top:0;}a{color:#3498db;text-decoration:none;font-weight:bold;}</style></head><body>' .
                    '<div class="box"><h1>403 Proibido</h1><p>O sistema já foi instalado e provisionado anteriormente.</p>' .
                    '<p><a href="/">Ir para a Loja</a> | <a href="/LPDHED2dC7Gjrg2b">Painel Administrativo</a></p></div></body></html>';

            $response->getBody()->write($html);
            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }
}
