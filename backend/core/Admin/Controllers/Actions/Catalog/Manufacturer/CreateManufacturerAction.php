<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CreateManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $html = $this->getTemplate('admin/catalog/manufacturer/create.html.twig', [
            'title' => 'Adicionar Fabricante | Painel Administrativo'
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
