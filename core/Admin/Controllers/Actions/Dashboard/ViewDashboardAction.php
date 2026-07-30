<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\Dashboard;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;

/**
 * ViewDashboardAction - Renderiza o painel inicial do admin.
 */
class ViewDashboardAction implements ActionInterface
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $html = $this->twig->render('admin/pages/dashboard/index.html.twig', [
                'title' => 'Dashboard | Painel Administrativo',
            ]);
        } catch (\Throwable $e) {
            $this->twig->setCache(false);
            $html = $this->twig->render('admin/pages/dashboard/index.html.twig', [
                'title' => 'Dashboard | Painel Administrativo',
            ]);
        }

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
