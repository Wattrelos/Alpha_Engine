<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\OrderReturnRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

class ProductReturnsAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private OrderReturnRepository $orderReturnRepository;

    public function __construct(TwigEnvironment $twig, OrderReturnRepository $orderReturnRepository)
    {
        $this->twig = $twig;
        $this->orderReturnRepository = $orderReturnRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Devoluções', 'href' => $routeParser->urlFor('account.returns', ['lang' => $lang])]
        ];

        // Busca devoluções do cliente logado
        $returnsData = $this->orderReturnRepository->getReturns(0, 20);
        $returns = [];

        foreach ($returnsData as $ret) {
            $returns[] = [
                'return_id'  => $ret['return_id'],
                'order_id'   => $ret['order_id'],
                'firstname'  => $ret['firstname'],
                'lastname'   => $ret['lastname'],
                'status'     => $ret['status'],
                'date_added' => date('d/m/Y H:i:s', strtotime($ret['date_added']))
            ];
        }

        $data = [
            'breadcrumbs'       => $breadcrumbs,
            'heading_title'     => 'Devoluções e Trocas',
            'column_return_id'  => 'ID da Devolução',
            'column_order_id'   => 'ID do Pedido',
            'column_status'     => 'Situação',
            'column_date_added' => 'Data de Solicitação',
            'text_no_results'   => 'Você ainda não possui solicitações de devolução.',
            'returns'           => $returns,
            'continue'          => $routeParser->urlFor('account.index', ['lang' => $lang]),
            'column_left'       => '',
            'column_right'      => '',
            'content_top'       => '',
            'content_bottom'    => '',
            'pagination'        => '',
            'results'           => ''
        ];

        $html = $this->twig->render('pages/product/product-returns.html.twig', $data);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
