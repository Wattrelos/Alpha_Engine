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
use Alpha\Events\EventDispatcher;
use Alpha\Events\OrderCreatedEvent;

/**
 * SubmitCheckoutAction - Processa o fechamento de pedidos (POST /checkout).
 */
class SubmitCheckoutAction implements ActionInterface
{
    private ContainerInterface $container;
    private EventDispatcher $eventDispatcher;

    public function __construct(ContainerInterface $container, EventDispatcher $eventDispatcher)
    {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $parsedBody = $request->getParsedBody();
        if (empty($parsedBody)) {
            $input = file_get_contents('php://input');
            $parsedBody = json_decode($input, true) ?? [];
        }

        $repositoryFactory = $this->container->get('alpha_repository_factory');
        $configSettings = $this->container->get('configSettings');
        $routeParser = null;
        try {
            $routeContext = RouteContext::fromRequest($request);
            $routeParser = $routeContext->getRouteParser();
        } catch (\RuntimeException $e) {
            // Routing not completed (e.g. testing context or direct controller call)
        }
        $lang = $request->getAttribute('lang', 'pt-br');

        // Verificação de Idempotência
        $idempotencyKey = $request->getHeaderLine('X-Idempotency-Key');
        if (!empty($idempotencyKey)) {
            $isDuplicate = false;
            try {
                $redis = new \Predis\Client([
                    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                    'port' => $_ENV['REDIS_PORT'] ?? 6379,
                    'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                ]);
                $redis->connect();
                $redisKey = "idempotency:" . $idempotencyKey;
                $result = $redis->executeRaw(['SET', $redisKey, 'processing', 'NX', 'EX', 300]);
                if ($result !== 'OK' && $result !== true) {
                    $isDuplicate = true;
                }
            } catch (\Throwable $e) {
                // Fallback para sessão
                if (!isset($_SESSION['idempotency_keys'])) {
                    $_SESSION['idempotency_keys'] = [];
                }
                if (isset($_SESSION['idempotency_keys'][$idempotencyKey])) {
                    $isDuplicate = true;
                } else {
                    $_SESSION['idempotency_keys'][$idempotencyKey] = time() + 300;
                }
            }

            if ($isDuplicate) {
                if ($this->isJsonRequest($request)) {
                    $response->getBody()->write(json_encode([
                        'error' => 'DUPLICATE_REQUEST',
                        'message' => 'Processamento em andamento.'
                    ]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
                }
                $_SESSION['error'] = 'Processamento em andamento. Por favor, aguarde.';
                $errorUrl = $routeParser ? $routeParser->urlFor('checkout.index', ['lang' => $lang]) : '/' . $lang . '/checkout';
                return $response->withHeader('Location', $errorUrl)->withStatus(302);
            }
        }

        // Buscar GeoZoneRepository para obter IDs a partir de siglas/UF
        /** @var \Alpha\Model\Domain\Repositories\GeoZoneRepository $zoneRepository */
        $zoneRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\GeoZoneRepository::class);

        // Resolução do Estado de Cobrança (Payment)
        $paymentZoneVal = $parsedBody['payment_zone_id'] ?? '';
        $paymentZoneId = 0;
        $paymentCountryId = 76; // Brasil padrão

        if (!empty($paymentZoneVal)) {
            $zoneCode = strtoupper(trim((string)$paymentZoneVal));
            if (!str_contains($zoneCode, '-')) {
                $zoneCode = 'BR-' . $zoneCode;
            }
            /** @var \Alpha\Model\Domain\Entities\Geo\Zone|null $zone */
            $zone = $zoneRepository->findOneBy(['isoCode' => $zoneCode]);
            if ($zone) {
                $paymentZoneId = $zone->getId();
                $paymentCountryId = method_exists($zone, 'getCountryId') ? $zone->getCountryId() : (int)($zone['country_id'] ?? $zone['countryId'] ?? $paymentCountryId);
            }
        }

        // 1. Guardar dados de endereço de cobrança na sessão
        $_SESSION['payment_address'] = [
            'firstname'    => $parsedBody['payment_firstname'] ?? '',
            'lastname'     => $parsedBody['payment_lastname'] ?? '',
            'company'      => $parsedBody['payment_company'] ?? '',
            'street'    => $parsedBody['payment_street'] ?? '',
            'number'       => (int)($parsedBody['payment_number'] ?? 0),
            'complement'    => $parsedBody['payment_complement'] ?? '',
            'neighborhood' => $parsedBody['payment_neighborhood'] ?? '',
            'city'         => $parsedBody['payment_city'] ?? '',
            'postcode'     => $parsedBody['payment_postcode'] ?? '',
            'country_id'   => $paymentCountryId,
            'zone_id'      => $paymentZoneId,
        ];

        // Resolução do Estado de Entrega (Shipping)
        $shippingZoneVal = $parsedBody['shipping_zone_id'] ?? '';
        $shippingZoneId = 0;
        $shippingCountryId = (int)($parsedBody['shipping_country_id'] ?? ($configSettings['config_country_id'] ?? 76));

        if (!empty($shippingZoneVal)) {
            $zoneCode = strtoupper(trim((string)$shippingZoneVal));
            if (!str_contains($zoneCode, '-')) {
                if ($shippingCountryId === 76) {
                    $zoneCode = 'BR-' . $zoneCode;
                }
            }
            /** @var \Alpha\Model\Domain\Entities\Geo\Zone|null $zone */
            $zone = $zoneRepository->findOneBy(['isoCode' => $zoneCode]);
            if ($zone) {
                $shippingZoneId = $zone->getId();
                $shippingCountryId = method_exists($zone, 'getCountryId') ? $zone->getCountryId() : (int)($zone['country_id'] ?? $zone['countryId'] ?? $shippingCountryId);
            }
        }

        // 2. Guardar dados de endereço de entrega na sessão (mesmo ou diferente)
        if (isset($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_street'])) {
            $_SESSION['shipping_address'] = [
                'firstname'    => $parsedBody['shipping_firstname'] ?? '',
                'lastname'     => $parsedBody['shipping_lastname'] ?? '',
                'company'      => $parsedBody['shipping_company'] ?? '',
                'street'       => $parsedBody['shipping_street'] ?? '',
                'number'       => (int)($parsedBody['shipping_number'] ?? 0),
                'complement'   => $parsedBody['shipping_complement'] ?? '',
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
            /** @var \Alpha\Model\Domain\Repositories\CustomerAddressesRepository $addressRepository */
            $addressRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\CustomerAddressesRepository::class);

            // ── Deduplicação: reutiliza ID de endereço existente com mesmo CEP+logradouro+número ──
            // Evita criar um novo registro a cada checkout quando o endereço já está salvo.
            $existingAddresses = $addressRepository->findByCustomerId($customerId);

            $findMatchId = function (array $addressData) use ($existingAddresses): int {
                $postcode  = preg_replace('/\D/', '', $addressData['postcode'] ?? '');
                $address1  = trim(strtolower($addressData['street'] ?? ''));
                $number    = trim((string)($addressData['number'] ?? ''));

                foreach ($existingAddresses as $existing) {
                    $exPostcode = preg_replace('/\D/', '', $existing->getPostalCode());
                    $exAddress1 = trim(strtolower($existing->getStreet()));
                    $exNumber   = trim((string)$existing->getNumber());

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
            if (isset($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_firstname']) && !empty($parsedBody['shipping_street'])) {
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



        try {
            // 4. Montar o DTO do Pedido manualmente (Removido do Repositório)
            /** @var CartRepository $cartRepository */
            $cartRepository = $repositoryFactory->get(CartRepository::class);
            $cartRepository->initializeContext();

            $orderData = [];
            $orderData['store_id']    = $this->container->has('storeId') ? (int)$this->container->get('storeId') : (int)($configSettings['config_store_id'] ?? 1);
            $orderData['language_id'] = (int)($configSettings['config_language_id'] ?? 2);
            $orderData['currency_id'] = (int)($configSettings['config_currency_id'] ?? 1);

            $orderData['customer_id'] = $customerId;
            $orderData['customer_group_id'] = $customerId > 0 ? ($_SESSION['customer_group_id'] ?? 1) : (int)($configSettings['config_customer_group_id'] ?? 1);

            $orderData['firstname'] = $parsedBody['payment_firstname'] ?? $_SESSION['payment_address']['firstname'] ?? '';
            $orderData['lastname']  = $parsedBody['payment_lastname'] ?? $_SESSION['payment_address']['lastname'] ?? '';
            $orderData['email']     = $parsedBody['email'] ?? $_SESSION['email'] ?? '';
            $orderData['telephone'] = $parsedBody['telephone'] ?? $_SESSION['telephone'] ?? '';

            $orderData['payment_firstname']   = $_SESSION['payment_address']['firstname'] ?? '';
            $orderData['payment_lastname']    = $_SESSION['payment_address']['lastname'] ?? '';
            $orderData['payment_company']     = $_SESSION['payment_address']['company'] ?? '';
            $orderData['payment_street']      = $_SESSION['payment_address']['street'] ?? '';
            $orderData['payment_number']      = (int)($_SESSION['payment_address']['number'] ?? 0);
            $orderData['payment_complement']  = $_SESSION['payment_address']['complement'] ?? '';
            $orderData['payment_district']    = $_SESSION['payment_address']['neighborhood'] ?? '';
            $orderData['payment_city']        = $_SESSION['payment_address']['city'] ?? '';
            $orderData['payment_postcode']    = $_SESSION['payment_address']['postcode'] ?? '';
            $orderData['payment_country_id']  = (int)($_SESSION['payment_address']['country_id'] ?? 0);
            $orderData['payment_zone_id']     = (int)($_SESSION['payment_address']['zone_id'] ?? 0);
            $orderData['payment_country']     = $_SESSION['payment_address']['country'] ?? '';
            $orderData['payment_zone']        = $_SESSION['payment_address']['zone'] ?? '';
            $orderData['payment_method']      = $_SESSION['payment_method']['title'] ?? '';
            $orderData['payment_code']        = $_SESSION['payment_method']['code'] ?? '';

            $orderData['shipping_firstname']  = $_SESSION['shipping_address']['firstname'] ?? '';
            $orderData['shipping_lastname']   = $_SESSION['shipping_address']['lastname'] ?? '';
            $orderData['shipping_company']    = $_SESSION['shipping_address']['company'] ?? '';
            $orderData['shipping_street']     = $_SESSION['shipping_address']['street'] ?? '';
            $orderData['shipping_number']     = (int)($_SESSION['shipping_address']['number'] ?? 0);
            $orderData['shipping_complement'] = $_SESSION['shipping_address']['complement'] ?? '';
            $orderData['shipping_district']   = $_SESSION['shipping_address']['neighborhood'] ?? '';
            $orderData['shipping_city']       = $_SESSION['shipping_address']['city'] ?? '';
            $orderData['shipping_postcode']   = $_SESSION['shipping_address']['postcode'] ?? '';
            $orderData['shipping_country_id'] = (int)($_SESSION['shipping_address']['country_id'] ?? 0);
            $orderData['shipping_zone_id']    = (int)($_SESSION['shipping_address']['zone_id'] ?? 0);

            $shippingCountryName = '';
            if ($orderData['shipping_country_id'] > 0) {
                /** @var \Alpha\Mappers\MapperFactory $mapperFactory */
                $mapperFactory = $this->container->get('alpha_mapper_factory');
                /** @var \Alpha\Mappers\EntityMappers\GeoCountryMapper $countryMapper */
                $countryMapper = $mapperFactory->get(\Alpha\Mappers\EntityMappers\GeoCountryMapper::class);
                /** @var \Alpha\Model\Domain\Entities\Geo\Country|null $sCountry */
                $sCountry = $countryMapper->findById($orderData['shipping_country_id']);
                if ($sCountry && method_exists($sCountry, 'getName')) {
                    $shippingCountryName = $sCountry->getName();
                }
            }
            $orderData['shipping_country']   = $shippingCountryName;

            $shippingZoneName = '';
            if ($orderData['shipping_zone_id'] > 0) {
                /** @var \Alpha\Model\Domain\Entities\Geo\Zone|null $sZone */
                $sZone = $zoneRepository->find($orderData['shipping_zone_id']);
                if ($sZone) {
                    $shippingZoneName = $sZone->getName();
                }
            }
            $orderData['shipping_zone']      = $shippingZoneName;

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

            // Dispara o evento de pedido criado (Padrão Observer)
            $this->eventDispatcher->dispatch(new OrderCreatedEvent($orderId, $orderDto));

            // Limpa o carrinho de compras
            $cartRepository->clear();

            // Grava o order_id na sessão para consulta futura na página de sucesso
            $_SESSION['last_order_id'] = $orderId;

            if ($this->isJsonRequest($request)) {
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'id' => $orderId
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            }

            $successUrl = $routeParser ? $routeParser->urlFor('checkout.success', ['lang' => $lang]) : '/' . $lang . '/checkout/sucesso';
            return $response->withHeader('Location', $successUrl)->withStatus(302);
        } catch (\Exception $e) {
            if ($this->isJsonRequest($request)) {
                $response->getBody()->write(json_encode([
                    'error' => 'ORDER_CREATION_FAILED',
                    'message' => $e->getMessage()
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
            $_SESSION['error'] = 'Erro ao processar o seu pedido: ' . $e->getMessage();
            $errorUrl = $routeParser ? $routeParser->urlFor('checkout.index', ['lang' => $lang]) : '/' . $lang . '/checkout';
            return $response->withHeader('Location', $errorUrl)->withStatus(302);
        }
    }

    private function isJsonRequest(Request $request): bool
    {
        $accept = $request->getHeaderLine('Accept');
        $contentType = $request->getHeaderLine('Content-Type');
        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
    }
}
