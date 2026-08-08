<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Category;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateCategoryAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
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

        $data = $request->getParsedBody() ?? [];

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $response->getBody()->write('O nome da categoria é obrigatório.');
            return $response->withStatus(400);
        }

        // 1. Processa upload de imagem se enviado
        $uploadedFiles = $request->getUploadedFiles();
        /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
        $imageFile = $uploadedFiles['image'] ?? null;

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
                $data['image'] = $targetPathRel;
            }
        }

        try {
            $categoryRepository->updateCategory($categoryId, $data, $this->storeId, $this->languageId);
        } catch (\Throwable $e) {
            $response->getBody()->write('Erro ao salvar categoria: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redireciona de volta para a lista
        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/categorias')
            ->withStatus(302);
    }
}

