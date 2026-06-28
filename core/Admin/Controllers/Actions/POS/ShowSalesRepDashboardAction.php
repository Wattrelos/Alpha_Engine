<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Action que renderiza a tela do Vendedor (Sales Rep) no PDV.
 */
class ShowSalesRepDashboardAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $loggedAdmin = $request->getAttribute('logged_admin');

        $html = $this->getTemplate(' pos/sales-rep/register-control.twig', [
            'title' => 'PDV - Painel do Vendedor',
            'logged_admin' => $loggedAdmin,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
