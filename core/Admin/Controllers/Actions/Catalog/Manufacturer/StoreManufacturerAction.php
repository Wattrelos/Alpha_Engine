<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class StoreManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $response->getBody()->write('O nome do fabricante é obrigatório.');
            return $response->withStatus(400);
        }

        /** @var ManufacturerRepository $manufacturerRepository */
        $manufacturerRepository = $this->getRepository(ManufacturerRepository::class);

        // 1. Processar upload de imagem
        $uploadedFiles = $request->getUploadedFiles();
        /** @var \Psr\Http\Message\UploadedFileInterface|null $imageFile */
        $imageFile = $uploadedFiles['image'] ?? null;
        $imagePath = '';

        if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
            $clientFilename = $imageFile->getClientFilename();
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($extension, $allowedExtensions, true)) {
                $safeFilename = sprintf('manufacturer_%d_%s.%s', time(), uniqid(), $extension);
                $targetPathRel = 'image/manufacturer/' . $safeFilename;
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
            $manufacturerRepository->createManufacturer($data, $this->storeId, $this->languageId);
        } catch (\Throwable $e) {
            $response->getBody()->write('Erro ao criar fabricante: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redireciona de volta para a lista
        return $response
            ->withHeader('Location', (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/fabricantes')
            ->withStatus(302);
    }
}

