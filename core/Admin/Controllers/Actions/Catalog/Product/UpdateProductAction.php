<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpdateProductAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $productId = (int)($args['id'] ?? 0);

        if (!$productId) {
            $response->getBody()->write('ID do produto não fornecido.');
            return $response->withStatus(400);
        }

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(ProductRepository::class);
        
        $existing = $productRepo->getAdminProductForEdit($productId, $this->languageId);
        if (!$existing) {
            $response->getBody()->write('Produto não encontrado.');
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody() ?? [];

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $response->getBody()->write('O nome do produto é obrigatório.');
            return $response->withStatus(400);
        }

        $uploadedFiles = $request->getUploadedFiles();

        // 1. Process main product image upload
        /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
        $imageFile = $uploadedFiles['image'] ?? null;

        if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
            $clientFilename = $imageFile->getClientFilename();
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($extension, $allowedExtensions, true)) {
                $safeFilename = sprintf('product_%d_%d.%s', $productId, time(), $extension);
                $targetPathRel = 'image/product/' . $safeFilename;
                $targetPathAbs = (defined('DIR_IMAGE') ? DIR_IMAGE : (DIR_ROOT . 'image/')) . $targetPathRel;

                $targetDir = dirname($targetPathAbs);
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }

                $imageFile->moveTo($targetPathAbs);
                $data['image'] = $targetPathRel;
            }
        }

        // 2. Process variant images upload
        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $index => &$v) {
                $vImageFile = $uploadedFiles["variant_image_{$index}"] ?? null;
                if ($vImageFile && $vImageFile->getError() === UPLOAD_ERR_OK) {
                    $clientFilename = $vImageFile->getClientFilename();
                    $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (in_array($extension, $allowedExtensions, true)) {
                        $safeFilename = sprintf('product_var_%d_%d.%s', (int)($v['id'] ?? 0), time(), $extension);
                        $targetPathRel = 'image/product/' . $safeFilename;
                        $targetPathAbs = (defined('DIR_IMAGE') ? DIR_IMAGE : (DIR_ROOT . 'image/')) . $targetPathRel;

                        $targetDir = dirname($targetPathAbs);
                        if (!is_dir($targetDir)) {
                            @mkdir($targetDir, 0755, true);
                        }

                        $vImageFile->moveTo($targetPathAbs);
                        $v['image'] = $targetPathRel;
                    }
                }
            }
            unset($v);
        }

        try {
            $productRepo->updateAdminProduct($productId, $data, $this->storeId, $this->languageId);
        } catch (\Throwable $e) {
            $response->getBody()->write('Erro ao salvar produto: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redireciona de volta para a listagem
        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/produtos')
            ->withStatus(302);
    }
}

