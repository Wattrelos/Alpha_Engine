<?php

namespace Alpha\Admin\Controllers\Actions\Procurement\Supplier;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\SupplierRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

class DeleteSupplierAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $supplierId = (int)($args['id'] ?? 0);

        if ($supplierId) {
            /** @var SupplierRepository $supplierRepository */
            $supplierRepository = RepositoryFactory::getInstance()->get(SupplierRepository::class);
            $supplierRepository->delete($supplierId);
        }

        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fornecedores')
            ->withStatus(302);
    }
}
