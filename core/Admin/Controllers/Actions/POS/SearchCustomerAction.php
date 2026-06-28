<?php

declare(strict_types=1);

namespace Alpha\Admin\Controllers\Actions\POS;

use Alpha\Controller\BaseController;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\DataAccessObject\ConnectionDB;

/**
 * Action responsável por buscar clientes ativos no sistema para o PDV (POS).
 */
class SearchCustomerAction extends BaseController implements ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $query = $queryParams['q'] ?? '';

        $conn = ConnectionDB::getInstance()->getConnection();
        
        $stmt = $conn->prepare("
            SELECT id, firstname, lastname, email, telephone
            FROM `" . DB_PREFIX . "customer`
            WHERE status = 1 AND (
                CONCAT(firstname, ' ', lastname) LIKE ? OR
                email LIKE ? OR
                telephone LIKE ?
            )
            LIMIT 15
        ");

        $likeQuery = "%" . $query . "%";
        $stmt->execute([$likeQuery, $likeQuery, $likeQuery]);
        $customersRaw = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $customers = [];
        foreach ($customersRaw as $c) {
            $customers[] = [
                'customer_id' => (int)$c['id'],
                'name'        => $c['firstname'] . ' ' . $c['lastname'],
                'email'       => $c['email'],
                'telephone'   => $c['telephone'],
            ];
        }

        $response->getBody()->write(json_encode($customers, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
