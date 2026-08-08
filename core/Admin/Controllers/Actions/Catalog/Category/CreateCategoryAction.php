<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CreateCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->getRepository(CategoryRepository::class);

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody() ?? [];

            $name = trim($data['name'] ?? '');
            if (empty($name)) {
                $response->getBody()->write('O nome da categoria é obrigatório.');
                return $response->withStatus(400);
            }

            // 1. Processa upload de imagem
            $uploadedFiles = $request->getUploadedFiles();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
            $imageFile = $uploadedFiles['image'] ?? null;
            $imagePath = '';

            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $clientFilename = $imageFile->getClientFilename();
                $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($extension, $allowedExtensions, true)) {
                    $safeFilename = sprintf('category_%d_%s.%s', time(), uniqid(), $extension);
                    $targetPathRel = 'image/category/' . $safeFilename;
                    $targetPathAbs = (defined('DIR_IMAGE') ? DIR_IMAGE : (DIR_ROOT . 'image/')) . $targetPathRel;

                    $targetDir = dirname($targetPathAbs);
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0755, true);
                    }

                    $imageFile->moveTo($targetPathAbs);
                    $imagePath = $targetPathRel;
                }
            }

            $data['image'] = $imagePath;

            try {
                $categoryRepository->createCategory($data, $this->storeId, $this->languageId);
            } catch (\Throwable $e) {
                $response->getBody()->write('Erro ao criar categoria: ' . $e->getMessage());
                return $response->withStatus(500);
            }

            // Redireciona de volta para a lista
            return $response
                ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/categorias')
                ->withStatus(302);
        }

        // GET Request: busca as categorias para o dropdown via Repositório de Domínio
        $categories = $categoryRepository->getCategoriesForSelect($this->languageId);

        $html = $this->getTemplate('admin/pages/category/create.html.twig', [
            'title'      => 'Adicionar Categoria | Painel Administrativo',
            'categories' => $categories
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

