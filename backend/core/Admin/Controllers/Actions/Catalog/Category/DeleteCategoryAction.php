<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DeleteCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $categoryId = (int)($args['id'] ?? 0);

        if (!$categoryId) {
            $response->getBody()->write('ID da categoria não fornecido.');
            return $response->withStatus(400);
        }

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->getRepository(CategoryRepository::class);

        // Verifica existência da categoria
        $existing = $categoryRepository->getCategoryForEdit($categoryId, $this->languageId, $this->storeId);
        if (!$existing) {
            $response->getBody()->write('Categoria não encontrada.');
            return $response->withStatus(404);
        }

        try {
            $categoryRepository->deleteCategory($categoryId, $this->storeId, $this->languageId);
        } catch (\Throwable $e) {
            $response->getBody()->write('Erro ao excluir categoria: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redireciona de volta para a lista
        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/categorias')
            ->withStatus(302);
    }
}

