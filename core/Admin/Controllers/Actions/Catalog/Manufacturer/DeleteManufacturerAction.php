<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class DeleteManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $manufacturerId = (int)($args['id'] ?? 0);

        if (!$manufacturerId) {
            $response->getBody()->write('ID do fabricante não fornecido.');
            return $response->withStatus(400);
        }

        $conn = ConnectionDB::getInstance()->getConnection();

        // Check if manufacturer exists
        $stmtCheck = $conn->prepare("SELECT id FROM `" . DB_PREFIX . "manufacturer` WHERE id = ?");
        $stmtCheck->execute([$manufacturerId]);
        $exists = (bool)$stmtCheck->fetchColumn();

        if (!$exists) {
            $response->getBody()->write('Fabricante não encontrado.');
            return $response->withStatus(404);
        }

        try {
            $conn->beginTransaction();

            // 1. Update products to disassociate the manufacturer (set manufacturer_id = 0)
            $stmtUpdateProducts = $conn->prepare("UPDATE `" . DB_PREFIX . "product` SET `manufacturer_id` = 0 WHERE `manufacturer_id` = ?");
            $stmtUpdateProducts->execute([$manufacturerId]);

            // 2. Delete manufacturer to store mapping records
            $stmtStoreDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE `manufacturer_id` = ?");
            $stmtStoreDel->execute([$manufacturerId]);

            // 3. Delete manufacturer to layout mapping records
            $stmtLayoutDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "manufacturer_to_layout` WHERE `manufacturer_id` = ?");
            $stmtLayoutDel->execute([$manufacturerId]);

            // 4. Delete SEO URL keyword for this manufacturer
            $stmtSeoDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'manufacturer_id' AND `value` = CAST(? AS CHAR)");
            $stmtSeoDel->execute([$manufacturerId]);

            // 5. Delete main manufacturer record
            $stmtCatDel = $conn->prepare("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE `id` = ?");
            $stmtCatDel->execute([$manufacturerId]);

            $conn->commit();

            // Clear cache
            $this->clearManufacturerCaches($manufacturerId);

        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $response->getBody()->write('Erro ao excluir fabricante: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redirect back to list
        return $response
            ->withHeader('Location', '/LPDHED2dC7Gjrg2b/fabricantes')
            ->withStatus(302);
    }

    private function clearManufacturerCaches(int $manufacturerId): void
    {
        if ($this->container->has(\Alpha\Support\Cache\CacheStrategyInterface::class)) {
            $cache = $this->container->get(\Alpha\Support\Cache\CacheStrategyInterface::class);
            if ($cache) {
                $cache->delete("manufacturer.{$manufacturerId}.{$this->languageId}.{$this->storeId}");
            }
        }
    }
}
