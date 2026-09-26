<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

class OrderHistoryAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private OrderRepository $orderRepository;
    private \Alpha\Support\Customer $customer;

    public function __construct(TwigEnvironment $twig, OrderRepository $orderRepository, \Alpha\Support\Customer $customer)
    {
        $this->twig = $twig;
        $this->orderRepository = $orderRepository;
        $this->customer = $customer;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $lang = $request->getAttribute('lang', 'pt-br');
        $customerId = $this->customer->isLogged() ? (int)$this->customer->getId() : 0;
        if (!$customerId) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $order_id = (int)($args['order_id'] ?? 0);
        
        // O método getOrder filtra pelo ID do cliente logado internamente
        $order = $this->orderRepository->getOrder($order_id, $customerId);

        if (!$order) {
            $html = $this->twig->render('pages/errors/404.html.twig', [
                'title'       => 'Pedido Não Encontrado | meusite',
                'description' => 'O pedido solicitado não existe ou não pertence a esta conta.',
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Meus Pedidos', 'href' => $routeParser->urlFor('account.orders', ['lang' => $lang])],
            ['text' => 'Pedido #' . $order_id, 'href' => $routeParser->urlFor('account.order.history', ['lang' => $lang, 'order_id' => (string)$order_id])]
        ];

        // Histórico de status
        $historiesData = $this->orderRepository->getHistories($order_id);
        $histories = [];
        foreach ($historiesData as $history) {
            $histories[] = [
                'date_added' => date('d/m/Y H:i:s', strtotime($history['date_added'])),
                'comment'    => nl2br(htmlspecialchars($history['comment'])),
                'status'     => $history['status']
            ];
        }

        // Produtos do Pedido
        $productsData = $this->orderRepository->getProducts($order_id);
        $products = [];
        foreach ($productsData as $product) {
            $optionsData = $this->orderRepository->getOptions($order_id, (int)$product['order_product_id']);
            $options = [];
            foreach ($optionsData as $option) {
                $options[] = [
                    'name'  => $option['name'],
                    'value' => $option['value']
                ];
            }
            $products[] = [
                'product_id' => (int)$product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'quantity'   => $product['quantity'],
                'price'      => 'R$ ' . number_format((float)$product['price'], 2, ',', '.'),
                'total'      => 'R$ ' . number_format((float)$product['total'], 2, ',', '.'),
                'option'     => $options
            ];
        }

        // Totais (Subtotal, Frete, Total etc.)
        $totalsData = $this->orderRepository->getTotals($order_id);
        $totals = [];
        foreach ($totalsData as $total) {
            $totals[] = [
                'title' => $total['title'],
                'text'  => 'R$ ' . number_format((float)$total['value'], 2, ',', '.')
            ];
        }

        // Formatação dos Endereços
        $paymentAddress = '';
        if ($order['payment_firstname']) {
            $paymentAddress = $order['payment_firstname'] . ' ' . $order['payment_lastname'] . '<br/>';
            if ($order['payment_company']) {
                $paymentAddress .= $order['payment_company'] . '<br/>';
            }
            $paymentAddress .= $order['payment_street'] . ($order['payment_number'] ? ', ' . $order['payment_number'] : '') . '<br/>';
            if ($order['payment_complement']) {
                $paymentAddress .= $order['payment_complement'] . '<br/>';
            }
            if ($order['payment_district']) {
                $paymentAddress .= $order['payment_district'] . '<br/>';
            }
            $paymentAddress .= $order['payment_city'] . ' - ' . $order['payment_zone'] . '<br/>';
            $paymentAddress .= $order['payment_postcode'];
        }

        $shippingAddress = '';
        if ($order['shipping_firstname']) {
            $shippingAddress = $order['shipping_firstname'] . ' ' . $order['shipping_lastname'] . '<br/>';
            if ($order['shipping_company']) {
                $shippingAddress .= $order['shipping_company'] . '<br/>';
            }
            $shippingAddress .= $order['shipping_street'] . ($order['shipping_number'] ? ', ' . $order['shipping_number'] : '') . '<br/>';
            if ($order['shipping_complement']) {
                $shippingAddress .= $order['shipping_complement'] . '<br/>';
            }
            if ($order['shipping_district']) {
                $shippingAddress .= $order['shipping_district'] . '<br/>';
            }
            $shippingAddress .= $order['shipping_city'] . ' - ' . $order['shipping_zone'] . '<br/>';
            $shippingAddress .= $order['shipping_postcode'];
        }

        $data = [
            'breadcrumbs'          => $breadcrumbs,
            'heading_title'        => 'Pedido #' . $order_id,
            'order_id'             => $order_id,
            'date_added'           => date('d/m/Y H:i:s', strtotime($order['date_added'])),
            'payment_method'       => $order['payment_method'],
            'shipping_method'      => $order['shipping_method'],
            'payment_address'      => $paymentAddress,
            'shipping_address'     => $shippingAddress,
            'products'             => $products,
            'totals'               => $totals,
            'comment'              => nl2br(htmlspecialchars($order['comment'])),
            
            'column_date_added'    => 'Data de Envio',
            'column_comment'       => 'Comentários',
            'column_status'        => 'Situação',
            'column_name'          => 'Nome do Produto',
            'column_model'         => 'Modelo',
            'column_quantity'      => 'Quantidade',
            'column_price'         => 'Preço Unitário',
            'column_total'         => 'Total',
            'text_no_results'      => 'Nenhum histórico encontrado para este pedido.',
            'text_order_detail'    => 'Detalhes do Pedido',
            'text_instruction'     => 'Instruções / Comentários',
            'text_history'         => 'Histórico de Situações',
            'text_payment_address' => 'Endereço de Faturamento',
            'text_shipping_address'=> 'Endereço de Entrega',
            
            'histories'            => $histories,
            'continue'             => $routeParser->urlFor('account.orders', ['lang' => $lang]),
            'pagination'           => '',
            'results'              => ''
        ];

        $html = $this->twig->render('pages/users/accounts/order-history.twig', $data);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}

