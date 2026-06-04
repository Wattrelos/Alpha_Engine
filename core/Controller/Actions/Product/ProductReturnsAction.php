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
    private \Alpha\Support\Language $translator;

    public function __construct(
        TwigEnvironment $twig,
        OrderReturnRepository $orderReturnRepository,
        \Alpha\Support\Language $translator
    ) {
        $this->twig = $twig;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->translator->load('account/returns');
        $this->twig->addGlobal('Returns', $this->translator->getNestedData('account/returns'));

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $breadcrumbs = [
            ['text' => $this->translator->get('breadcrumbHome', 'account/returns'), 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => $this->translator->get('breadcrumbAccount', 'account/returns'), 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => $this->translator->get('breadcrumbReturns', 'account/returns'), 'href' => $routeParser->urlFor('account.returns', ['lang' => $lang])]
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
            'heading_title'     => $this->translator->get('headingTitle', 'account/returns'),
            'column_return_id'  => $this->translator->get('columnReturnId', 'account/returns'),
            'column_order_id'   => $this->translator->get('columnOrderId', 'account/returns'),
            'column_status'     => $this->translator->get('columnStatus', 'account/returns'),
            'column_date_added' => $this->translator->get('columnDateAdded', 'account/returns'),
            'text_no_results'   => $this->translator->get('textNoResults', 'account/returns'),
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
