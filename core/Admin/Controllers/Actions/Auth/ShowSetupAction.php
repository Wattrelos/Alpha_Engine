<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\UserRepository;

/**
 * ShowSetupAction - Exibe a tela de configuração inicial do primeiro administrador.
 */
class ShowSetupAction implements ActionInterface
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $userRepo = RepositoryFactory::getInstance()->get(UserRepository::class);
        $users = $userRepo->findAll();

        if (count($users) > 0) {
            // Se já existem usuários cadastrados, o OOBE está bloqueado por motivos de segurança.
            $routeContext = RouteContext::fromRequest($request);
            $url = $routeContext->getRouteParser()->urlFor('admin.login.form');
            return $response->withHeader('Location', $url)->withStatus(302);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $html = $this->twig->render('admin/auth/setup.html.twig', [
            'action' => $routeParser->urlFor('admin.setup.submit'),
            'errors' => [],
            'data'   => [],
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
