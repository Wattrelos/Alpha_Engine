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
        $repositoryFactory = $this->registry->get('alpha_repository_factory');

        // Buscar ZoneRepository para obter IDs a partir de siglas/UF
        /** @var \Alpha\Model\Domain\Repositories\ZoneRepository $zoneRepository */
        $zoneRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\ZoneRepository::class);

        // Resolução do Estado de Cobrança (Payment)
        $paymentZoneVal = $parsedBody['payment_zone_id'] ?? '';
        $paymentZoneId = 0;
        $paymentCountryId = 30; // Brasil padrão

        if (!empty($paymentZoneVal)) {
            /** @var \Alpha\Model\Domain\Entities\Zone|null $zone */
            $zone = $zoneRepository->findOneBy(['code' => $paymentZoneVal]);
            if ($zone) {
                $paymentZoneId = $zone->getId();
                $paymentCountryId = $zone->getCountryId();
            }
        }

        // 1. Guardar dados de endereço de cobrança na sessão
        $session->data['payment_address'] = [
            'firstname'    => $parsedBody['payment_firstname'] ?? '',
            'lastname'     => $parsedBody['payment_lastname'] ?? '',
            'company'      => $parsedBody['payment_company'] ?? '',
            'address_1'    => $parsedBody['payment_address_1'] ?? '',
            'number'       => (int)($parsedBody['payment_number'] ?? 0),
            'address_2'    => $parsedBody['payment_address_2'] ?? '',
            'neighborhood' => $parsedBody['payment_neighborhood'] ?? '',
            'city'         => $parsedBody['payment_city'] ?? '',
            'postcode'     => $parsedBody['payment_postcode'] ?? '',
            'country_id'   => $paymentCountryId,
            'zone_id'      => $paymentZoneId,
        ];

        // Resolução do Estado de Entrega (Shipping)
        $shippingZoneVal = $parsedBody['shipping_zone_id'] ?? '';
        $shippingZoneId = 0;
        $shippingCountryId = 30; // Brasil padrão

        if (!empty($shippingZoneVal)) {
            /** @var \Alpha\Model\Domain\Entities\Zone|null $zone */
            $zone = $zoneRepository->findOneBy(['code' => $shippingZoneVal]);
            if ($zone) {
                $shippingZoneId = $zone->getId();
                $shippingCountryId = $zone->getCountryId();
            }
        }

        // 2. Guardar dados de endereço de entrega na sessão (mesmo ou diferente)
        if (isset($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_firstname'])) {
            $session->data['shipping_address'] = [
                'firstname'    => $parsedBody['shipping_firstname'] ?? '',
                'lastname'     => $parsedBody['shipping_lastname'] ?? '',
                'company'      => $parsedBody['shipping_company'] ?? '',
                'address_1'    => $parsedBody['shipping_address_1'] ?? '',
                'number'       => (int)($parsedBody['shipping_number'] ?? 0),
                'address_2'    => $parsedBody['shipping_address_2'] ?? '',
                'neighborhood' => $parsedBody['shipping_neighborhood'] ?? '',
                'city'         => $parsedBody['shipping_city'] ?? '',
                'postcode'     => $parsedBody['shipping_postcode'] ?? '',
                'country_id'   => $shippingCountryId,
                'zone_id'      => $shippingZoneId,
            ];
        } else {
            // Se for igual, copia do endereço de cobrança
            $session->data['shipping_address'] = $session->data['payment_address'];
        }

        // 3. Se o cliente estiver logado, persistir o(s) endereço(s) na tabela address
        $customer = $this->registry->get('customer');
        $customerId = $customer ? (int)$customer->getId() : 0;

        if ($customerId > 0) {
            /** @var \Alpha\Model\Domain\Repositories\AddressRepository $addressRepository */
            $addressRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\AddressRepository::class);

            // ── Deduplicação: reutiliza ID de endereço existente com mesmo CEP+logradouro+número ──
            // Evita criar um novo registro a cada checkout quando o endereço já está salvo.
            $existingAddresses = $addressRepository->findByCustomerId($customerId);

            $findMatchId = function (array $addressData) use ($existingAddresses): int {
                $postcode  = preg_replace('/\D/', '', $addressData['postcode'] ?? '');
                $address1  = trim(strtolower($addressData['address_1'] ?? ''));
                $number    = (int)($addressData['number'] ?? 0);

                foreach ($existingAddresses as $existing) {
                    $exPostcode = preg_replace('/\D/', '', $existing->getPostcode());
                    $exAddress1 = trim(strtolower($existing->getAddress1()));
                    $exNumber   = $existing->getNumber();

                    if ($exPostcode === $postcode && $exAddress1 === $address1 && $exNumber === $number) {
                        return $existing->getId();
                    }
                }
                return 0; // 0 = não encontrado, criar novo
            };

            // Persiste o endereço de cobrança como default
            $paymentAddressData = array_merge($session->data['payment_address'], [
                'default'    => true,
                'address_id' => $findMatchId($session->data['payment_address']),
            ]);
            $addressRepository->save($paymentAddressData, $customerId);

            // Se o endereço de entrega for diferente, persiste ele também
            if (isset($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_firstname'])) {
                $shippingAddressData = array_merge($session->data['shipping_address'], [
                    'default'    => false,
                    'address_id' => $findMatchId($session->data['shipping_address']),
                ]);
                $addressRepository->save($shippingAddressData, $customerId);
            }
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
            
            // Alpha Engine: Confirma o pedido transicionando de 0 (Não confirmado) para o status padrão (Pendente)
            $config = $this->registry->get('config');
            $defaultOrderStatusId = $config ? (int)$config->get('config_order_status_id') : 1;
            if ($defaultOrderStatusId <= 0) {
                $defaultOrderStatusId = 1;
            }
            $orderRepository->confirm($orderId, $defaultOrderStatusId, 'Pedido realizado com sucesso via checkout.');

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

