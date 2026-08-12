<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DeleteManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        // Verifica existência do fabricante
        $existing = $manufacturerRepository->getManufacturerForEdit($manufacturerId, $this->storeId, $this->languageId);
        if (!$existing) {
            $response->getBody()->write('Fabricante não encontrado.');
            return $response->withStatus(404);
        }

        try {
            $manufacturerRepository->deleteManufacturer($manufacturerId, $this->storeId, $this->languageId);
        } catch (\Throwable $e) {
            $response->getBody()->write('Erro ao excluir fabricante: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redireciona de volta para a lista
        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fabricantes')
            ->withStatus(302);
    }
}

