<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CartMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\AlphaString;
use Alpha\Model\Domain\Entities\Geo\Country;
use Alpha\Model\Domain\Entities\Geo\Zone;

/**
 * CartRepository - Orquestra a lógica de negócios do Carrinho de Compras.
 * 
 * Substitui a pesada biblioteca legada system/library/cart/cart.php,
 * delegando a persistência (SQL) ao CartMapper e a hidratação atômica ao ProductMapper.
 */
class CartRepository extends AbstractRepository implements BaseRepositoryInterface
{
    private array $data = [];
    private bool $isLoaded = false;
    private ?float $cachedSubTotal = null;
    private ?float $cachedWeight = null;

    protected function getMapper(): CartMapper
    {
        return $this->mapperFactory->get(CartMapper::class);
    }

    // ─────────────────────────────────────────────────────────
    // Métodos Defensivos de Acesso ao Container (Fim do God Registry e métodos Mágicos)
    // ─────────────────────────────────────────────────────────

    private function getCustomer()
    {
        return property_exists($this, 'container') && $this->container->has('customer') ? $this->container->get('customer') : null;
    }

    private function getSession()
    {
        return property_exists($this, 'container') && $this->container->has('session') ? $this->container->get('session') : null;
    }

    private function getConfig()
    {
        return property_exists($this, 'container') && $this->container->has('config') ? $this->container->get('config') : null;
    }

    private function getTax()
    {
        return property_exists($this, 'container') && $this->container->has('tax') ? $this->container->get('tax') : null;
    }

    private function getWeightService()
    {
        return property_exists($this, 'container') && $this->container->has('weight') ? $this->container->get('weight') : null;
    }

    private function getStoreId(): int
    {
        $config = $this->getConfig();
        if ($config && $config->get('config_store_id')) {
            return (int)$config->get('config_store_id');
        }
        return 1;
    }

    private function getLanguageId(): int
    {
        $config = $this->getConfig();
        return (int)($config ? $config->get('config_language_id') : 2);
    }

    // ─────────────────────────────────────────────────────────

    private function getSessionId(): string
    {
        $session = $this->getSession();
        if ($session && method_exists($session, 'getId')) {
            return $session->getId();
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_id();
        }
        return '';
    }

    private function getCustomerId(): int
    {
        $customer = $this->getCustomer();
        return ($customer && $customer->isLogged()) ? (int)$customer->getId() : 0;
    }

    /**
     * Alpha Engine: Inicializa o contexto do carrinho.
     * Mescla carrinhos de sessão com carrinhos de cliente (se logado).
     */
    public function initializeContext(): void
    {
        $customer = $this->getCustomer();
        if ($customer && $customer->isLogged() && $this->getSessionId()) {
            $this->getMapper()->mergeCartOnLogin($this->getCustomerId(), $this->getSessionId(), $this->getStoreId());
        }
    }

    /**
     * Adiciona um item ao carrinho.
     */
    public function add(int $productId, int $quantity = 1, array $option = [], int $subscriptionPlanId = 0): void
    {
        $mapper = $this->getMapper();

        // Na Alpha Engine, garantimos que as opções virem um hash JSON para comparação exata no banco
        $optionData = !empty($option) ? json_encode($option) : '';

        // Verifica se o item já existe no carrinho para somar a quantidade (Fim do // TODO)
        $cartItems = $mapper->getItems($this->getCustomerId(), $this->getSessionId(), $this->getStoreId());
        $existingCartId = 0;

        foreach ($cartItems as $item) {
            $itemOption = $item['option'] ?? '';
            $itemSubPlan = (int)($item['subscription_plan_id'] ?? 0);

            if ((int)$item['product_id'] === $productId && $itemOption === $optionData && $itemSubPlan === $subscriptionPlanId) {
                $existingCartId = (int)($item['cart_id'] ?? $item['id']);
                $quantity += (int)$item['quantity'];
                break;
            }
        }

        if ($existingCartId > 0) {
            $mapper->updateItem($existingCartId, $quantity, $this->getCustomerId(), $this->getSessionId());
        } else {
            $mapper->addItem(
                $this->getCustomerId(),
                $this->getSessionId(),
                $this->getStoreId(),
                $productId,
                $quantity,
                $optionData,
                $subscriptionPlanId
            );
        }

        // Invalida o cache em memória para forçar a reconstrução na próxima leitura
        $this->isLoaded = false;
        $this->cachedSubTotal = null;
        $this->cachedWeight = null;
    }

    /**
     * Atualiza a quantidade de um item.
     */
    public function update(int $cartId, int $quantity): void
    {
        $this->getMapper()->updateItem($cartId, $quantity, $this->getCustomerId(), $this->getSessionId());
        $this->isLoaded = false;
        $this->cachedSubTotal = null;
        $this->cachedWeight = null;
    }

    /**
     * Remove um item por completo.
     */
    public function remove(int $cartId): void
    {
        $this->getMapper()->removeItem($cartId, $this->getCustomerId(), $this->getSessionId());
        $this->isLoaded = false;
        $this->cachedSubTotal = null;
        $this->cachedWeight = null;
    }

    /**
     * Verifica se um item específico existe no carrinho.
     */
    public function has(int $cartId): bool
    {
        foreach ($this->getProducts() as $product) {
            if ((int)$product['cart_id'] === $cartId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Limpa o carrinho por completo.
     */
    public function clear(): void
    {
        $this->getMapper()->clearItems($this->getCustomerId(), $this->getSessionId(), $this->getStoreId());
        $this->data = [];
        $this->isLoaded = true;
        $this->cachedSubTotal = null;
        $this->cachedWeight = null;
    }

    /**
     * Alpha Engine: Sincroniza o Endereço de Domínio ativo com a Sessão
     * para garantir a precisão do cálculo de Impostos (Tax Zone) e Frete.
     */
    private function resolveTaxAndShippingZone(): void
    {
        $customer = $this->getCustomer();
        $session = $this->getSession();
        $tax = $this->getTax();

        if (!$session) {
            return;
        }

        if ($customer && $customer->isLogged()) {
            if (empty($session->data['shipping_address']) || empty($session->data['payment_address'])) {
                /** @var \Alpha\Model\Domain\Repositories\CustomerAddressesRepository $addressRepo */
                $addressRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\CustomerAddressesRepository::class);
                $defaultAddressId = $customer->getAddressId();

                if ($defaultAddressId > 0) {
                    $addresses = $addressRepo->getAddresses($this->getCustomerId());
                    $addressDTO = null;
                    foreach ($addresses as $addr) {
                        if ((int)$addr['address_id'] === $defaultAddressId) {
                            $addressDTO = $addr;
                            break;
                        }
                    }
                    if ($addressDTO === null && !empty($addresses)) {
                        $addressDTO = $addresses[0];
                    }

                    if ($addressDTO) {
                        if (empty($session->data['shipping_address'])) {
                            $session->data['shipping_address'] = $addressDTO;
                        }
                        if (empty($session->data['payment_address'])) {
                            $session->data['payment_address'] = $addressDTO;
                        }
                    }
                }
            }
        }

        // Sincroniza com as propriedades tributárias de impostos se houver endereço na sessão (logados ou anônimos)
        if (!empty($session->data['shipping_address'])) {
            $shippingAddr = $session->data['shipping_address'];
            $countryId = (int)($shippingAddr['country_id'] ?? 76);
            $zoneId = (int)($shippingAddr['zone_id'] ?? 0);

            // Se o zone_id for 0 mas tivermos a sigla do estado em 'zone', tenta resolver o zone_id via banco
            if ($zoneId === 0 && !empty($shippingAddr['zone'])) {
                try {
                    $repositoryFactory = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance();
                    /** @var \Alpha\Model\Domain\Repositories\GeoZoneRepository $zoneRepository */
                    $zoneRepository = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\GeoZoneRepository::class);
                    $zoneCode = strtoupper(trim((string)$shippingAddr['zone']));
                    if (!str_contains($zoneCode, '-')) {
                        $zoneCode = 'BR-' . $zoneCode;
                    }
                    $zone = $zoneRepository->findOneBy(['isoCode' => $zoneCode]);
                    if ($zone) {
                        $zoneId = $zone->getId();
                        $countryId = $zone->getCountryId();
                        $session->data['shipping_address']['zone_id'] = $zoneId;
                        $session->data['shipping_address']['country_id'] = $countryId;
                    }
                } catch (\Exception $e) {
                    // Ignora silenciosamente
                }
            }

            if ($tax) {
                $tax->setShippingAddress($countryId, $zoneId);
                $tax->setPaymentAddress($countryId, $zoneId);
            }
        }
    }

    /**
     * Alpha Engine: Recupera e hidrata os produtos do carrinho.
     * Fim do N+1: A hidratação de produtos consome o ProductMapper e aplica descontos.
     */
    public function getProducts(): array
    {
        if ($this->isLoaded) {
            return $this->data;
        }

        // Alpha Engine: Garante que as zonas de frete e impostos estejam resolvidas
        // utilizando a nova arquitetura da Entidade Address antes do processamento.
        $this->resolveTaxAndShippingZone();

        $cartItems = $this->getMapper()->getItems($this->getCustomerId(), $this->getSessionId(), $this->getStoreId());

        if (!$cartItems) {
            $this->isLoaded = true;
            $this->data = [];
            return [];
        }

        // Alpha Engine: Fim do N+1 Query! Carregamos todos os produtos de uma vez.
        $product_ids = array_column($cartItems, 'product_id');

        // Configurações de contexto da Alpha Engine para preços B2B / Varejo
        $config = $this->getConfig();
        $customerGroupId = $config ? (int)$config->get('config_customer_group_id') : 1;
        $customer = $this->getCustomer();
        if ($customer && $customer->isLogged()) {
            $customerGroupId = (int)$customer->getGroupId();
        }

        /** @var \Alpha\Model\Domain\Repositories\PriceRepository $priceRepo */
        $priceRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\PriceRepository::class);
        $priceStatements = $priceRepo->getPriceStatements($customerGroupId);

        /** @var ProductMapper $productMapper */
        $productMapper = $this->mapperFactory->get(ProductMapper::class);
        $productDataMap = $productMapper->getProductsByIds($product_ids, $this->getLanguageId(), $this->getStoreId(), $customerGroupId, $priceStatements);
        $productMap = array_column($productDataMap, null, 'id');

        // Alpha Engine: Identifica e carrega em lote os produtos pai para variações (Evita N+1)
        $parentIdsToFetch = [];
        foreach ($productMap as $prod) {
            $masterId = (int)($prod['master_id'] ?? 0);
            if ($masterId > 0 && !isset($productMap[$masterId])) {
                $parentIdsToFetch[] = $masterId;
            }
        }

        if (!empty($parentIdsToFetch)) {
            $parentDataMap = $productMapper->getProductsByIds($parentIdsToFetch, $this->getLanguageId(), $this->getStoreId(), $customerGroupId, $priceStatements);
            foreach ($parentDataMap as $parentProd) {
                $productMap[$parentProd['id']] = $parentProd;
            }
        }

        // Hidratação e Herança das variações
        foreach ($productMap as $id => &$prod) {
            $masterId = (int)($prod['master_id'] ?? 0);
            if ($masterId > 0 && isset($productMap[$masterId])) {
                $parent = $productMap[$masterId];

                // Preço Base
                if ((float)$prod['price'] <= 0.0) {
                    $prod['price'] = $parent['price'];
                }

                // Preço Promocional (Special e Discount)
                // Se a variação herda o preço do pai, herda também promoções e descontos do pai
                if (!isset($prod['special']) || (float)$prod['special'] <= 0.0) {
                    if ((float)$prod['price'] === (float)$parent['price']) {
                        $prod['special'] = $parent['special'];
                    }
                }
                if (!isset($prod['discount']) || (float)$prod['discount'] <= 0.0) {
                    if ((float)$prod['price'] === (float)$parent['price']) {
                        $prod['discount'] = $parent['discount'];
                    }
                }

                // Classe de Imposto
                if (empty($prod['tax_class_id'])) {
                    $prod['tax_class_id'] = $parent['tax_class_id'];
                }

                // Peso e Classe de Peso
                if ((float)$prod['weight'] <= 0.0) {
                    $prod['weight'] = $parent['weight'];
                    $prod['weight_class_id'] = $parent['weight_class_id'];
                }

                // Imagem
                if (empty($prod['image'])) {
                    $prod['image'] = $parent['image'];
                }

                // Pontos
                if (empty($prod['points'])) {
                    $prod['points'] = $parent['points'];
                }
                if (empty($prod['reward'])) {
                    $prod['reward'] = $parent['reward'] ?? 0;
                }

                // Compra Mínima
                if (empty($prod['minimum']) || (int)$prod['minimum'] <= 1) {
                    $prod['minimum'] = $parent['minimum'];
                }

                // Estoque Subtraível
                if (isset($parent['subtract'])) {
                    $prod['subtract'] = $parent['subtract'];
                }

                // Frete Requerido
                if (isset($parent['shipping'])) {
                    $prod['shipping'] = $parent['shipping'];
                }
            }
        }
        unset($prod);

        $products = [];

        /** @var ProductMapper $productMapper */
        $productMapper = $this->mapperFactory->get(ProductMapper::class);

        // Alpha Engine: Pré-carregamento de Opções (Fim do N+1 nas opções)
        $allOptionValueIds = [];
        foreach ($cartItems as $item) {
            $options = json_decode($item['option'], true) ?: [];
            foreach ($options as $productOptionId => $value) {
                if (is_scalar($value)) {
                    $allOptionValueIds[] = (int)$value;
                }
            }
        }
        $allOptionValueIds = array_unique($allOptionValueIds);

        // Alpha Engine: Consumo Inteligente O(1) das Opções e Descontos via Repositórios de Domínio
        $repositoryFactory = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance();
        $optionRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\ProductOptionValueRepository::class);
        $productRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);
        $discountRepo = $repositoryFactory->get(\Alpha\Model\Domain\Repositories\ProductDiscountRepository::class);

        $optionValuesEntities = $optionRepo->getOptionValuesByIds($allOptionValueIds);
        // Mantém o Mapper legado em memória apenas para a extração do Nome da Opção Traduzida (Visual)
        $legacyOptionValuesMap = !empty($allOptionValueIds) ? $productMapper->getOptionValuesByIds($allOptionValueIds, $this->getLanguageId()) : [];

        foreach ($cartItems as $item) {
            // Busca o produto no mapa em memória (O(1)) em vez de consultar o banco
            $productInfo = $productMap[$item['product_id']] ?? null;

            if ($productInfo) {
                $price = (float)$productInfo['price'];
                $points = (int)$productInfo['points'];
                $weight = (float)$productInfo['weight'];

                $optionData = [];
                $options = json_decode($item['option'], true) ?: [];

                foreach ($options as $productOptionId => $value) {
                    if (is_scalar($value)) {
                        /** @var \Alpha\Model\Domain\Entities\ProductOptionValue $optionEntity */
                        $optionEntity = $optionValuesEntities[(int)$value] ?? null;
                        $legacyOptionInfo = $legacyOptionValuesMap[(int)$value] ?? null;

                        if ($optionEntity) {
                            // Processa Modificador de Preço
                            if ($optionEntity->getPricePrefix() === '+') {
                                $price += (float)$optionEntity->getPrice();
                            } elseif ($optionEntity->getPricePrefix() === '-') {
                                $price -= (float)$optionEntity->getPrice();
                            }

                            // Processa Modificador de Peso
                            if ($optionEntity->getWeightPrefix() === '+') {
                                $weight += (float)$optionEntity->getWeight();
                            } elseif ($optionEntity->getWeightPrefix() === '-') {
                                $weight -= (float)$optionEntity->getWeight();
                            }

                            // Mantemos os metadados da opção para a view
                            $optionData[] = [
                                'name'  => $legacyOptionInfo['option_name'] ?? 'Option',
                                'value' => $legacyOptionInfo['option_value_name'] ?? (string)$optionEntity->getId()
                            ];
                        }
                    }
                }

                // Alpha Engine: Cálculo de Descontos Progressivos estritos (Cruza a Quantidade Real no Carrinho)
                $activeDiscounts = $discountRepo->getActiveDiscounts($productInfo['id'], $customerGroupId);
                $discountPrice = null;

                foreach ($activeDiscounts as $discount) {
                    // O(1) filter pela quantidade exata atualizada no carrinho
                    if ($item['quantity'] >= $discount->getQuantity()) {
                        if ($discountPrice === null || $discount->getPrice() < $discountPrice) {
                            $discountPrice = $discount->getPrice();
                        }
                    }
                }

                if ((float)$productInfo['special']) {
                    $price = (float)$productInfo['special'];
                } elseif ($discountPrice !== null) {
                    $price = (float)$discountPrice;
                }

                // Resolução do Nome do Plano de Assinatura
                $subscriptionName = '';
                $subscriptionPlanId = (int)($item['subscription_plan_id'] ?? 0);
                if ($subscriptionPlanId > 0) {
                    $subscriptions = $productRepo->getSubscriptions($productInfo['id']);
                    foreach ($subscriptions as $sub) {
                        if ((int)$sub['subscription_plan_id'] === $subscriptionPlanId) {
                            $subscriptionName = $sub['name'];
                            break;
                        }
                    }
                }

                // Alpha Engine: Integração da Zona de Imposto (Tax Zone) na exibição de preços
                $taxPrice = $this->getTax() && $config ? $this->getTax()->calculate($price, $productInfo['tax_class_id'], $config->get('config_tax')) : $price;
                $taxTotal = $taxPrice * $item['quantity'];

                // Formatação final do produto protegendo a interface legada
                $products[] = [
                    'cart_id'               => $item['cart_id'] ?? $item['id'], // Interoperabilidade para chaves renomeadas
                    'product_id'            => $productInfo['id'],
                    'name'                  => $productInfo['name'],
                    'model'                 => $productInfo['model'],
                    'shipping'              => $productInfo['shipping'],
                    'image'                 => $productInfo['image'],
                    'option'                => $optionData,
                    'subscription'          => $subscriptionName,
                    'quantity'              => $item['quantity'],
                    'minimum'               => $productInfo['minimum'] ?: 1,
                    'minimum_status'        => $item['quantity'] >= ($productInfo['minimum'] ?: 1),
                    'subtract'              => $productInfo['subtract'],
                    'stock'                 => ($productInfo['quantity'] >= $item['quantity']),
                    'stock_status'          => ($productInfo['quantity'] >= $item['quantity']),
                    'stock_quantity'        => (int)$productInfo['quantity'],
                    'price'                 => $price,
                    'tax_price'             => $taxPrice,
                    'total'                 => $price * $item['quantity'],
                    'tax_total'             => $taxTotal,
                    'reward'                => (int)$productInfo['reward'] * $item['quantity'],
                    'points'                => $points * $item['quantity'],
                    'weight'                => $weight,
                    'weight_class_id'       => $productInfo['weight_class_id'],
                    'tax_class_id'          => $productInfo['tax_class_id']
                ];
            } else {
                // Limpeza autônoma: Produto desativado ou apagado é removido da sessão na hora!
                $this->remove($item['cart_id'] ?? $item['id']);
            }
        }

        $this->data = $products;
        $this->isLoaded = true;

        return $this->data;
    }

    /**
     * Alpha Engine: Calcula o peso total do carrinho de forma isolada.
     */
    public function getWeight(): float
    {
        if ($this->cachedWeight !== null) {
            return $this->cachedWeight;
        }

        $weight = 0.0;
        $weightService = $this->getWeightService();
        $config = $this->getConfig();

        foreach ($this->getProducts() as $product) {
            if ($product['shipping'] && $weightService && $config) {
                $weight += $weightService->convert(
                    $product['weight'] * $product['quantity'],
                    $product['weight_class_id'],
                    $config->get('config_weight_class_id')
                );
            } else {
                $weight += $product['weight'] * $product['quantity'];
            }
        }

        $this->cachedWeight = $weight;
        return $weight;
    }

    /**
     * Alpha Engine: Calcula a consolidação fiscal/impostos dos itens no carrinho.
     */
    public function getTaxes(): array
    {
        $tax_data = [];
        $taxService = $this->getTax();
        foreach ($this->getProducts() as $product) {
            if ($product['tax_class_id'] && $taxService) {
                $tax_rates = $taxService->getRates($product['price'], $product['tax_class_id']);
                foreach ($tax_rates as $tax_rate) {
                    if (!isset($tax_data[$tax_rate['tax_rate_id']])) {
                        $tax_data[$tax_rate['tax_rate_id']] = ($tax_rate['amount'] * $product['quantity']);
                    } else {
                        $tax_data[$tax_rate['tax_rate_id']] += ($tax_rate['amount'] * $product['quantity']);
                    }
                }
            }
        }
        return $tax_data;
    }

    /**
     * Retorna os produtos do carrinho que possuem assinatura ativa.
     */
    public function getSubscriptions(): array
    {
        $subscription_data = [];
        foreach ($this->getProducts() as $product) {
            if (!empty($product['subscription'])) {
                $subscription_data[] = $product;
            }
        }
        return $subscription_data;
    }

    /**
     * Alpha Engine: Retorna o subtotal do carrinho (sem impostos/taxas).
     */
    public function getSubTotal(): float
    {
        if ($this->cachedSubTotal !== null) {
            return $this->cachedSubTotal;
        }

        $total = 0.0;
        foreach ($this->getProducts() as $product) {
            $total += $product['total'];
        }
        $this->cachedSubTotal = $total;
        return $this->cachedSubTotal;
    }

    /**
     * Alpha Engine: Retorna o total geral da mercadoria física + impostos.
     */
    public function getTotal(): float
    {
        $total = $this->getSubTotal();
        foreach ($this->getTaxes() as $tax) {
            $total += $tax;
        }
        return $total;
    }

    /**
     * Conta a quantidade de unidades físicas contidas no carrinho.
     */
    public function countProducts(): int
    {
        $count = 0;
        foreach ($this->getProducts() as $product) {
            $count += $product['quantity'];
        }
        return $count;
    }

    public function hasProducts(): bool
    {
        return count($this->getProducts()) > 0;
    }

    public function hasSubscription(): bool
    {
        foreach ($this->getProducts() as $product) {
            if (!empty($product['subscription'])) {
                return true;
            }
        }
        return false;
    }

    public function hasStock(): bool
    {
        foreach ($this->getProducts() as $product) {
            if (!$product['stock']) {
                return false;
            }
        }
        return true;
    }

    public function hasMinimum(): bool
    {
        foreach ($this->getProducts() as $product) {
            if (!$product['minimum_status']) {
                return false;
            }
        }
        return true;
    }

    public function hasShipping(): bool
    {
        foreach ($this->getProducts() as $product) {
            if ($product['shipping']) {
                return true;
            }
        }
        return false;
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity
    {
        return $this->getMapper()->findById($id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }

    /**
     * Alpha Engine: Adiciona um item ao carrinho e limpa os dados voláteis do checkout.
     */
    public function addAndClearCheckout(int $customerId, string $sessionId, int $productId, int $quantity = 1, array $option = [], int $subscriptionPlanId = 0): void
    {
        // Embora a injeção do repositório já conheça o cliente e a sessão atual, 
        // mantemos os parâmetros para integridade da assinatura com a chamada do BaseController
        $this->add($productId, $quantity, $option, $subscriptionPlanId);
        $this->clearCheckoutSession();
    }

    /**
     * Alpha Engine: Atualiza a quantidade de um item e invalida o checkout atual.
     */
    public function updateAndClearCheckout(int $cartId, int $quantity): void
    {
        $this->update($cartId, $quantity);
        $this->clearCheckoutSession();
    }

    /**
     * Alpha Engine: Remove um item e invalida o checkout atual.
     */
    public function removeAndClearCheckout(int $cartId): void
    {
        $this->remove($cartId);
        $this->clearCheckoutSession();
    }

    /**
     * Limpa as seleções temporárias de frete e pagamento da sessão sempre que o carrinho sofrer alterações.
     */
    private function clearCheckoutSession(): void
    {
        if ($session = $this->getSession()) {
            unset($session->data['shipping_method']);
            unset($session->data['shipping_methods']);
            unset($session->data['payment_method']);
            unset($session->data['payment_methods']);
            unset($session->data['reward']);
        }
    }

    /**
     * Alpha Engine: Orquestra a renderização e o cálculo dos módulos de totalização do carrinho.
     * Substitui completamente o model legado checkout/cart.php
     */
    public function getTotals(array &$totals, array &$taxes, float &$total): void
    {
        /** @var \Alpha\Mappers\EntityMappers\ExtensionMapper $extensionMapper */
        $extensionMapper = $this->mapperFactory->get(\Alpha\Mappers\EntityMappers\ExtensionMapper::class);

        // Fim do N+1: Busca as extensões do tipo 'total' no banco através do DAO
        $results = $extensionMapper->getExtensionsByType('total');

        $config = $this->getConfig();
        $sort_order = [];
        foreach ($results as $key => $value) {
            $sort_order[$key] = $config ? $config->get('total_' . $value['code'] . '_sort_order') : 0;
        }

        // Ordena as extensões (Sub-Total -> Frete -> Cupom -> Impostos -> Total)
        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($config && $config->get('total_' . $result['code'] . '_status')) {

                // Carrega a extensão legada como bridge (até refatorarmos cada uma)
                $file = DIR_EXTENSION . $result['extension'] . '/catalog/model/total/' . $result['code'] . '.php';
                $loader = property_exists($this, 'container') && $this->container->has('load') ? $this->container->get('load') : null;
                $registry = property_exists($this, 'container') && $this->container->has('registry') ? $this->container->get('registry') : null;

                if (is_file($file) && $loader && $registry) {
                    $loader->model('extension/' . $result['extension'] . '/total/' . $result['code']);
                    $modelCode = 'model_extension_' . $result['extension'] . '_total_' . $result['code'];
                    $modelInstance = $registry->get($modelCode);

                    if ($modelInstance) {
                        $modelInstance->getTotal($totals, $taxes, $total);
                    }
                }
            }
        }

        // Alpha Engine: Reordenação final baseada no sort_order interno injetado pelas próprias extensões
        $sort_order = [];
        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $totals);

        // Fallback se nenhuma totalização padrão for gerada
        if (empty($totals)) {
            $subTotal = $this->getSubTotal();
            $currency = property_exists($this, 'container') && $this->container->has('currency') ? $this->container->get('currency') : null;
            $session = $this->getSession();
            $currencyCode = ($session && isset($session->data['currency'])) ? $session->data['currency'] : ($config ? $config->get('config_currency') : 'BRL');

            $totals[] = [
                'code'       => 'sub_total',
                'title'      => 'Sub-Total',
                'value'      => $subTotal,
                'text'       => $currency ? $currency->format($subTotal, $currencyCode) : 'R$ ' . number_format($subTotal, 2, ',', '.'),
                'sort_order' => $config ? (int)$config->get('total_sub_total_sort_order') : 1
            ];

            foreach ($taxes as $tax_rate_id => $value) {
                if ($value > 0) {
                    $totals[] = [
                        'code'       => 'tax',
                        'title'      => 'Impostos',
                        'value'      => $value,
                        'text'       => $currency ? $currency->format($value, $currencyCode) : 'R$ ' . number_format($value, 2, ',', '.'),
                        'sort_order' => $config ? (int)$config->get('total_tax_sort_order') : 9
                    ];
                }
            }

            $grandTotal = $this->getTotal();
            $totals[] = [
                'code'       => 'total',
                'title'      => 'Total',
                'value'      => $grandTotal,
                'text'       => $currency ? $currency->format($grandTotal, $currencyCode) : 'R$ ' . number_format($grandTotal, 2, ',', '.'),
                'sort_order' => $config ? (int)$config->get('total_total_sort_order') : 9
            ];
        }
    }

    /**
     * Alpha Engine: Valida as regras de negócio para adicionar um item ao carrinho.
     */
    public function validateAddition(int $productId, array $option, int $subscriptionPlanId): array
    {
        $result = ['error' => []];

        /** @var \Alpha\Model\Domain\Repositories\ProductRepository $productRepository */
        $productRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);
        $product_info = $productRepository->getProduct($productId);

        if (!$product_info) {
            $result['error']['warning'] = 'error_product';
            return $result;
        }

        $result['product_name'] = $product_info['name'];

        if (!empty($product_info['master_id'])) {
            $productId = $product_info['master_id'];
        }

        $override = $product_info['override']['variant'] ?? [];

        if (!empty($product_info['variant']) && is_array($product_info['variant'])) {
            foreach ($product_info['variant'] as $key => $value) {
                if (array_key_exists($key, $override)) {
                    $option[$key] = $value;
                }
            }
        }

        $result['option_data'] = $option;

        foreach ($productRepository->getOptions($productId) as $product_option) {
            if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                $result['error']['option_' . $product_option['product_option_id']] = 'error_required';
            } elseif (($product_option['type'] == 'text') && !empty($product_option['validation']) && !AlphaString::validateRegex($option[$product_option['product_option_id']], $product_option['validation'])) {
                $result['error']['option_' . $product_option['product_option_id']] = 'error_regex';
            }
        }

        $subscriptions = $productRepository->getSubscriptions($productId);
        if ($subscriptions && (!$subscriptionPlanId || !in_array($subscriptionPlanId, array_column($subscriptions, 'subscription_plan_id')))) {
            $result['error']['subscription'] = 'error_subscription';
        }

        if (!empty($result['error'])) {
            $result['redirect'] = true;
        }

        return $result;
    }
}
