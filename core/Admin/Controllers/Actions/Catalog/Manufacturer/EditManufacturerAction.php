<?php

namespace Alpha\Admin\Controllers\Actions\Catalog\Manufacturer;

use Alpha\Controller\BaseController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

class EditManufacturerAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $manufacturerId = (int)($args['id'] ?? 0);

        if (!$manufacturerId) {
            $response->getBody()->write('ID do fabricante não fornecido.');
            return $response->withStatus(400);
        }

        $conn = ConnectionDB::getInstance()->getConnection();

        // 1. Fetch manufacturer details
        $stmtCat = $conn->prepare("
            SELECT m.*,
                   (SELECT keyword FROM `" . DB_PREFIX . "seo_url` 
                    WHERE `key` = 'manufacturer_id' AND `value` = CAST(m.id AS CHAR) 
                      AND store_id = ? AND language_id = ? LIMIT 1) AS seo_keyword
            FROM `" . DB_PREFIX . "manufacturer` m
            WHERE m.id = ?
        ");
        $stmtCat->execute([$this->storeId, $this->languageId, $manufacturerId]);
        $manufacturer = $stmtCat->fetch(\PDO::FETCH_ASSOC);

        if (!$manufacturer) {
            $response->getBody()->write('Fabricante não encontrado.');
            return $response->withStatus(404);
        }

        $html = $this->getTemplate('admin/catalog/manufacturer/edit.html.twig', [
            'title'        => 'Editar Fabricante | Painel Administrativo',
            'manufacturer' => $manufacturer
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
