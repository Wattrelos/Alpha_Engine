<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;

/**
 * ShowLoginAction - Exibe o formulário de login do painel administrativo.
 */
class ShowLoginAction implements ActionInterface
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $queryParams = $request->getQueryParams();
        $error = isset($queryParams['error']) ? 'Usuário ou senha inválidos.' : null;

        $html = $this->twig->render('admin/auth/login.html.twig', [
            'action'   => $routeParser->urlFor('admin.login.submit'),
            'username' => '',
            'error'    => $error,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
