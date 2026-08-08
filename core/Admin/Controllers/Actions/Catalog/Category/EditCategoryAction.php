<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class EditCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        // 1. Busca detalhes da categoria via Repositório de Domínio
        $category = $categoryRepository->getCategoryForEdit($categoryId, $this->languageId, $this->storeId);

        if (!$category) {
            $response->getBody()->write('Categoria não encontrada.');
            return $response->withStatus(404);
        }

        // 2. Busca lista de categorias pai elegíveis
        $categories = $categoryRepository->getParentCategoriesForSelect($categoryId, $this->languageId);

        $html = $this->getTemplate('admin/pages/category/edit.html.twig', [
            'title'      => 'Editar Categoria | Painel Administrativo',
            'category'   => $category,
            'categories' => $categories
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

