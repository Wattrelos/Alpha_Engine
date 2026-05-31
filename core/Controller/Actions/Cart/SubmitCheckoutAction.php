<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Support\Registry;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\CartRepository;
use Slim\Routing\RouteContext;

/**
 * SubmitCheckoutAction - Processa o fechamento de pedidos (POST /checkout).
 */
class SubmitCheckoutAction implements ActionInterface
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $session = $this->registry->get('session');
        $parsedBody = $request->getParsedBody();

        // 1. Guardar dados de endereço de cobrança na sessão
        $session->data['payment_address'] = [
            'firstname'    => $parsedBody['payment_firstname'] ?? '',
            'lastname'     => $parsedBody['payment_lastname'] ?? '',
            'company'      => $parsedBody['payment_company'] ?? '',
            'address_1'    => $parsedBody['payment_address_1'] ?? '',
            'number'       => $parsedBody['payment_number'] ?? '',
            'address_2'    => $parsedBody['payment_address_2'] ?? '',
            'neighborhood' => $parsedBody['payment_neighborhood'] ?? '',
            'city'         => $parsedBody['payment_city'] ?? '',
            'postcode'     => $parsedBody['payment_postcode'] ?? '',
            'country_id'   => $parsedBody['payment_country_id'] ?? '',
            'zone_id'      => $parsedBody['payment_zone_id'] ?? '',
        ];

        // 2. Guardar dados de endereço de entrega na sessão (mesmo ou diferente)
        if (isset($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_firstname'])) {
            $session->data['shipping_address'] = [
                'firstname'    => $parsedBody['shipping_firstname'] ?? '',
                'lastname'     => $parsedBody['shipping_lastname'] ?? '',
                'company'      => $parsedBody['shipping_company'] ?? '',
                'address_1'    => $parsedBody['shipping_address_1'] ?? '',
                'number'       => $parsedBody['shipping_number'] ?? '',
                'address_2'    => $parsedBody['shipping_address_2'] ?? '',
                'neighborhood' => $parsedBody['shipping_neighborhood'] ?? '',
                'city'         => $parsedBody['shipping_city'] ?? '',
                'postcode'     => $parsedBody['shipping_postcode'] ?? '',
                'country_id'   => $parsedBody['shipping_country_id'] ?? '',
                'zone_id'      => $parsedBody['shipping_zone_id'] ?? '',
            ];
        } else {
            // Se for igual, copia do endereço de cobrança
            $session->data['shipping_address'] = $session->data['payment_address'];
        }

        // Método de pagamento selecionado e observações
        $paymentMethodCode = $parsedBody['payment_method'] ?? 'cod';
        $paymentMethodTitle = 'Pagar na Entrega';
        if ($paymentMethodCode === 'transferencia') {
            $paymentMethodTitle = 'Transferência Bancária';
        } elseif ($paymentMethodCode === 'pix') {
            $paymentMethodTitle = 'Pix';
        } elseif ($paymentMethodCode === 'link_pagamento') {
            $paymentMethodTitle = 'Gerar Link de Pagamento';
        }

        $session->data['payment_method'] = [
            'title' => $paymentMethodTitle,
            'code'  => $paymentMethodCode
        ];

        $session->data['comment'] = $parsedBody['payment_note'] ?? '';

        $session->data['shipping_method'] = [
            'title' => 'Retirar na Loja',
            'code'  => 'pickup'
        ];

        // 3. Salvar o pedido via OrderRepository
        $repositoryFactory = $this->registry->get('alpha_repository_factory');
        /** @var OrderRepository $orderRepository */
        $orderRepository = $repositoryFactory->get(OrderRepository::class);

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        try {
            $orderId = $orderRepository->createFromSession();
            
            // Limpa o carrinho de compras
            /** @var CartRepository $cartRepository */
            $cartRepository = $repositoryFactory->get(CartRepository::class);
            $cartRepository->initializeContext();
            $cartRepository->clear();

            // Grava o order_id na sessão para consulta futura na página de sucesso
            $session->data['last_order_id'] = $orderId;

            $successUrl = $routeParser->urlFor('checkout.success', ['lang' => $lang]);
            return $response->withHeader('Location', $successUrl)->withStatus(302);
        } catch (\Exception $e) {
            $session->data['error'] = 'Erro ao processar o seu pedido: ' . $e->getMessage();
            $errorUrl = $routeParser->urlFor('checkout.index', ['lang' => $lang]);
            return $response->withHeader('Location', $errorUrl)->withStatus(302);
        }
    }
}

