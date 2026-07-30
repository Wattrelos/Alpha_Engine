<?php

declare(strict_types=1);

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment as TwigEnvironment;

/**
 * CsrfGuardMiddleware - Middleware de Proteção Anti-CSRF para a Alpha Engine.
 * 
 * Integra o Slim\Csrf\Guard com inicialização preguiçosa (Lazy Initialization),
 * respostas JSON estruturadas para AJAX, fallback gracioso para formulários padrão
 * e injeção automática de tokens no Twig.
 */
class CsrfGuardMiddleware implements MiddlewareInterface
{
    private ?Guard $guard = null;
    private ?TwigEnvironment $twig;

    public function __construct(?TwigEnvironment $twig = null, ?Guard $guard = null)
    {
        $this->twig = $twig;
        $this->guard = $guard;
    }

    /**
     * Obtém ou inicializa a instância do Guard garantindo que a sessão PHP já esteja ativa.
     */
    private function getGuard(): Guard
    {
        if ($this->guard === null) {
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                if (defined('APPLICATION') && APPLICATION === 'admin') {
                    session_name('admin_session_id');
                }
                @session_start();
            }

            $responseFactory = new ResponseFactory();

            if (!isset($_SESSION['csrf']) || !is_array($_SESSION['csrf'])) {
                $_SESSION['csrf'] = [];
            }
            foreach ($_SESSION['csrf'] as $k => $v) {
                if (!is_string($v) || !is_string($k)) {
                    unset($_SESSION['csrf'][$k]);
                }
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                $guard = new Guard($responseFactory);
            } else {
                $guard = new Guard($responseFactory, 'csrf', $_SESSION['csrf']);
            }

            $guard->setPersistentTokenMode(true);

            // Define Handler de Falha customizado para interceptar rejeições CSRF
            $guard->setFailureHandler(function (Request $request, Handler $handler): Response {
                $response = new \Slim\Psr7\Response();

                $isXmlHttpRequest = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
                $acceptsJson = str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json');

                if ($isXmlHttpRequest || $acceptsJson) {
                    $response->getBody()->write((string)json_encode([
                        'error' => [
                            'warning' => 'Sua sessão expirou ou o token de segurança é inválido. Por favor, recarregue a página.'
                        ]
                    ]));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(400);
                }

                $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>400 Bad Request - Erro de Segurança</title>'
                    . '<style>body{font-family:sans-serif;text-align:center;padding:50px;background:#f8fafc;color:#334155;}'
                    . '.card{background:#fff;max-width:500px;margin:0 auto;padding:30px;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);}'
                    . 'h1{color:#e11d48;font-size:24px;}a{display:inline-block;margin-top:20px;padding:10px 20px;background:#0284c7;color:#fff;text-decoration:none;border-radius:6px;}</style>'
                    . '</head><body><div class="card">'
                    . '<h1>400 - Requisição Rejeitada (CSRF)</h1>'
                    . '<p>Sua sessão expirou ou a validação de segurança do formulário falhou.</p>'
                    . '<a href="javascript:history.back()">Voltar e Tentar Novamente</a>'
                    . '</div></body></html>';

                $response->getBody()->write($html);
                return $response->withStatus(400);
            });

            $this->guard = $guard;
        }

        return $this->guard;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $guard = $this->getGuard();

        // Wrapper local para capturar atributos gerados pelo Guard e injetar no Twig
        $wrappedHandler = new class($handler, $guard, $this->twig) implements Handler {
            private Handler $nextHandler;
            private Guard $guard;
            private ?TwigEnvironment $twig;

            public function __construct(Handler $nextHandler, Guard $guard, ?TwigEnvironment $twig)
            {
                $this->nextHandler = $nextHandler;
                $this->guard = $guard;
                $this->twig = $twig;
            }

            public function handle(Request $request): Response
            {
                $nameKey = $this->guard->getTokenNameKey();
                $valueKey = $this->guard->getTokenValueKey();

                $name = $request->getAttribute($nameKey);
                $value = $request->getAttribute($valueKey);

                if ($this->twig !== null) {
                    $this->twig->addGlobal('csrf', [
                        'name'  => $name,
                        'value' => $value,
                        'keys'  => [
                            'name'  => $nameKey,
                            'value' => $valueKey
                        ]
                    ]);
                }

                return $this->nextHandler->handle($request);
            }
        };

        return $guard->process($request, $wrappedHandler);
    }
}
