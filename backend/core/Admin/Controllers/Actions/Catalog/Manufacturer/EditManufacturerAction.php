<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class EditManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $manufacturerId = (int)($args['id'] ?? 0);

        if (!$manufacturerId) {
            $response->getBody()->write('ID do fabricante não fornecido.');
            return $response->withStatus(400);
        }

        /** @var ManufacturerRepository $manufacturerRepository */
        $manufacturerRepository = $this->getRepository(ManufacturerRepository::class);

        // Busca o fabricante com SEO Keyword via Repositório de Domínio
        $manufacturer = $manufacturerRepository->getManufacturerForEdit($manufacturerId, $this->storeId, $this->languageId);

        if (!$manufacturer) {
            $response->getBody()->write('Fabricante não encontrado.');
            return $response->withStatus(404);
        }

        $html = $this->getTemplate('admin/catalog/manufacturer/edit.html.twig', [
            'title'        => 'Editar Fabricante | Painel Administrativo',
            'manufacturer' => $manufacturer
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

