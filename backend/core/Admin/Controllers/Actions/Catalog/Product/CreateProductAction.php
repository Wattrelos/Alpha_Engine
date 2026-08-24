<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CreateProductAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(ProductRepository::class);
        /** @var CategoryRepository $categoryRepo */
        $categoryRepo = $this->getRepository(CategoryRepository::class);
        /** @var ManufacturerRepository $manufacturerRepo */
        $manufacturerRepo = $this->getRepository(ManufacturerRepository::class);

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody() ?? [];

            $name = trim($data['name'] ?? '');
            if (empty($name)) {
                $response->getBody()->write('O nome do produto é obrigatório.');
                return $response->withStatus(400);
            }

            // 1. Handle image upload
            $uploadedFiles = $request->getUploadedFiles();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
            $imageFile = $uploadedFiles['image'] ?? null;
            $imagePath = '';

            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $clientFilename = $imageFile->getClientFilename();
                $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($extension, $allowedExtensions, true)) {
                    $safeFilename = sprintf('product_%d_%s.%s', time(), uniqid(), $extension);
                    $targetPathRel = 'image/product/' . $safeFilename;
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
                $productRepo->createAdminProduct($data, $this->storeId, $this->languageId);
            } catch (\Throwable $e) {
                $response->getBody()->write('Erro ao criar produto: ' . $e->getMessage());
                return $response->withStatus(500);
            }

            // Redirect back to list
            return $response
                ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/produtos')
                ->withStatus(302);
        }

        // GET request - render the create view
        $manufacturers = $manufacturerRepo->getManufacturers();
        $stockStatuses = $productRepo->getStockStatuses($this->languageId);
        $categories = $categoryRepo->getCategoriesForSelect($this->languageId);
        $weightClasses = $productRepo->getWeightClasses($this->languageId);
        $lengthClasses = $productRepo->getLengthClasses($this->languageId);
        $taxClasses = $productRepo->getTaxClasses();

        $html = $this->getTemplate('admin/pages/products/create.html.twig', [
            'title'          => 'Adicionar Produto | Painel Administrativo',
            'manufacturers'  => $manufacturers,
            'stock_statuses' => $stockStatuses,
            'categories'     => $categories,
            'weight_classes' => $weightClasses,
            'length_classes' => $lengthClasses,
            'tax_classes'    => $taxClasses
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

