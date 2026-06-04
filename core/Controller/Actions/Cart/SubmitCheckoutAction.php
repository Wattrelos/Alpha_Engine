<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Container\ContainerInterface;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\DTOs\OrderDataDTO;
use Slim\Routing\RouteContext;

/**
 * SubmitCheckoutAction - Processa o fechamento de pedidos (POST /checkout).
 */
class SubmitCheckoutAction implements ActionInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $parsedBody = $request->getParsedBody();
        $repositoryFactory = $this->container->get('alpha_repository_factory');
        $configSettings = $this->container->get('configSettings');

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
        $_SESSION['payment_address'] = [
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
            $_SESSION['shipping_address'] = [
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
            $_SESSION['shipping_address'] = $_SESSION['payment_address'];
        }

        // 3. Se o cliente estiver logado, persistir o(s) endereço(s) na tabela address
        $customerId = (int)($_SESSION['customer_id'] ?? 0);

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
            $paymentAddressData = array_merge($_SESSION['payment_address'], [
                'default'    => true,
                'address_id' => $findMatchId($_SESSION['payment_address']),
            ]);
            $addressRepository->save($paymentAddressData, $customerId);

            // Se o endereço de entrega for diferente, persiste ele também
            if (isset($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_firstname'])) {
                $shippingAddressData = array_merge($_SESSION['shipping_address'], [
                    'default'    => false,
                    'address_id' => $findMatchId($_SESSION['shipping_address']),
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

        $_SESSION['payment_method'] = [
            'title' => $paymentMethodTitle,
            'code'  => $paymentMethodCode
        ];

        $_SESSION['comment'] = $parsedBody['payment_note'] ?? '';

        $_SESSION['shipping_method'] = [
            'title' => 'Retirar na Loja',
            'code'  => 'pickup'
        ];

        // 4. Salvar o pedido via OrderRepository
        /** @var OrderRepository $orderRepository */
        $orderRepository = $repositoryFactory->get(OrderRepository::class);

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $lang = $request->getAttribute('lang', 'pt-br');

        try {
            // 4. Montar o DTO do Pedido manualmente (Removido do Repositório)
            /** @var CartRepository $cartRepository */
            $cartRepository = $repositoryFactory->get(CartRepository::class);
            $cartRepository->initializeContext();

            $orderData = [];
            if (!isset($configSettings['config_store_id'])) {
                throw new \RuntimeException('[Alpha Engine] config_store_id ausente nas configura\u00e7\u00f5es do checkout. Falha de bootstrap.');
            }
            $orderData['store_id']    = (int)$configSettings['config_store_id'];
            $orderData['language_id'] = (int)($configSettings['config_language_id'] ?? 2);
            $orderData['currency_id'] = (int)($configSettings['config_currency_id'] ?? 1);
            
            $orderData['customer_id'] = $customerId;
            $orderData['customer_group_id'] = $customerId > 0 ? ($_SESSION['customer_group_id'] ?? 1) : (int)($configSettings['config_customer_group_id'] ?? 1);
            
            $orderData['firstname'] = $parsedBody['payment_firstname'] ?? $_SESSION['payment_address']['firstname'] ?? '';
            $orderData['lastname']  = $parsedBody['payment_lastname'] ?? $_SESSION['payment_address']['lastname'] ?? '';
            $orderData['email']     = $parsedBody['email'] ?? $_SESSION['email'] ?? '';
            $orderData['telephone'] = $parsedBody['telephone'] ?? $_SESSION['telephone'] ?? '';

            $orderData['payment_firstname'] = $_SESSION['payment_address']['firstname'] ?? '';
            $orderData['payment_lastname']  = $_SESSION['payment_address']['lastname'] ?? '';
            $orderData['payment_address_1'] = $_SESSION['payment_address']['address_1'] ?? '';
            $orderData['payment_city']      = $_SESSION['payment_address']['city'] ?? '';
            $orderData['payment_postcode']  = $_SESSION['payment_address']['postcode'] ?? '';
            $orderData['payment_country_id']= (int)($_SESSION['payment_address']['country_id'] ?? 0);
            $orderData['payment_zone_id']   = (int)($_SESSION['payment_address']['zone_id'] ?? 0);
            $orderData['payment_method']    = $_SESSION['payment_method']['title'] ?? '';
            $orderData['payment_code']      = $_SESSION['payment_method']['code'] ?? '';

            $orderData['shipping_firstname'] = $_SESSION['shipping_address']['firstname'] ?? '';
            $orderData['shipping_lastname']  = $_SESSION['shipping_address']['lastname'] ?? '';
            $orderData['shipping_address_1'] = $_SESSION['shipping_address']['address_1'] ?? '';
            $orderData['shipping_city']      = $_SESSION['shipping_address']['city'] ?? '';
            $orderData['shipping_postcode']  = $_SESSION['shipping_address']['postcode'] ?? '';
            $orderData['shipping_country_id']= (int)($_SESSION['shipping_address']['country_id'] ?? 0);
            $orderData['shipping_zone_id']   = (int)($_SESSION['shipping_address']['zone_id'] ?? 0);
            $orderData['shipping_method']    = $_SESSION['shipping_method']['title'] ?? '';
            $orderData['shipping_code']      = $_SESSION['shipping_method']['code'] ?? '';

            $orderData['products'] = $cartRepository->getProducts();
            $orderData['vouchers'] = $_SESSION['vouchers'] ?? [];
            $orderData['totals']   = $_SESSION['totals'] ?? [];
            $orderData['total']    = $cartRepository->getTotal();
            $orderData['comment']  = $_SESSION['comment'] ?? '';
            
            $coupon_code = $_SESSION['coupon'] ?? '';
            $orderData['coupon_id'] = 0;
            $orderData['coupon_amount'] = 0.0;

            if ($coupon_code) {
                /** @var \Alpha\Model\Domain\Repositories\CouponRepository $couponRepo */
                $couponRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\CouponRepository::class);
                $coupon = $couponRepo->findByCode($coupon_code);
                if ($coupon) {
                    $orderData['coupon_id'] = $coupon->getId();
                    foreach ($orderData['totals'] as $total) {
                        if ($total['code'] === 'coupon') {
                            $orderData['coupon_amount'] = abs((float)$total['value']);
                            break;
                        }
                    }
                }
            }

            $orderData['ip'] = $request->getServerParams()['REMOTE_ADDR'] ?? '';
            $orderData['user_agent'] = $request->getServerParams()['HTTP_USER_AGENT'] ?? '';

            $orderDto = new OrderDataDTO($orderData);
            if (!$orderDto->isValid()) {
                throw new \Exception('Alpha Engine: Dados insuficientes para criar o pedido.');
            }

            $orderId = $orderRepository->save($orderDto);

            // Alpha Engine: Confirma o pedido transicionando de 0 (Não confirmado) para o status padrão (Pendente)
            $defaultOrderStatusId = (int)($configSettings['config_order_status_id'] ?? 1);
            $orderRepository->confirm($orderId, $defaultOrderStatusId, 'Pedido realizado com sucesso via checkout.');

            // Limpa o carrinho de compras
            $cartRepository->clear();

            // Grava o order_id na sessão para consulta futura na página de sucesso
            $_SESSION['last_order_id'] = $orderId;

            $successUrl = $routeParser->urlFor('checkout.success', ['lang' => $lang]);
            return $response->withHeader('Location', $successUrl)->withStatus(302);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao processar o seu pedido: ' . $e->getMessage();
            $errorUrl = $routeParser->urlFor('checkout.index', ['lang' => $lang]);
            return $response->withHeader('Location', $errorUrl)->withStatus(302);
        }
    }
}
