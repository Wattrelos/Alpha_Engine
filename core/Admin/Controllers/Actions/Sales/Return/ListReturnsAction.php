<?php

namespace Alpha\Admin\Controllers\Actions\Sales\Return;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\OrderReturnRepository;
use Alpha\Model\Domain\Repositories\ReturnDictionaryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListReturnsAction extends BaseController implements \Alpha\Controller\Actions\ActionInterface
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $limit = 15;

        /** @var OrderReturnRepository $returnRepo */
        $returnRepo = $this->getRepository(OrderReturnRepository::class);

        // Busca dados paginados e filtrados via Repositório de Domínio
        $result = $returnRepo->getAdminReturnsPaginated($queryParams, $page, $limit, $this->languageId);
        $totalReturns = $result['total'];
        $returnsData = $result['data'];

        $returns = [];
        foreach ($returnsData as $r) {
            $returns[] = [
                'return_id'   => $r['id'],
                'order_id'    => $r['order_id'],
                'customer'    => $r['firstname'] . ' ' . $r['lastname'],
                'product'     => $r['product'],
                'model'       => $r['model'],
                'status'      => $r['status_name'] ?? 'Pendente',
                'date_added'  => date('d/m/Y H:i', strtotime($r['date_added'])),
            ];
        }

        // Carrega status para o filtro select
        /** @var ReturnDictionaryRepository $dictRepo */
        $dictRepo = $this->getRepository(ReturnDictionaryRepository::class);
        $statuses = $dictRepo->getStatusesByLanguage($this->languageId);

        // Reconstrução de URL de paginação
        $urlParams = $queryParams;
        unset($urlParams['page']);
        $urlQueryString = http_build_query($urlParams);

        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $baseUrl = $routeContext->getRouteParser()->urlFor('admin.returns.index');
        } catch (\Throwable $e) {
            $baseUrl = (defined('ADMIN_PATH') ? ADMIN_PATH : '/LPDHED2dC7Gjrg2b') . '/devolucoes';
        }
        $url = $baseUrl . '?' . ($urlQueryString ? $urlQueryString . '&' : '') . 'page={page}';

        $html = $this->getTemplate('admin/sales/return/index.html.twig', [
            'title'        => 'Devoluções | Painel Administrativo',
            'returns'      => $returns,
            'total'        => $totalReturns,
            'limit'        => $limit,
            'current_page' => $page,
            'url'          => $url,
            'statuses'     => $statuses,
            'filters'      => $queryParams,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

