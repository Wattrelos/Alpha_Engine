<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CartMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\DataTransferObject\ViewResponse;

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

    protected function getMapper(): CartMapper
    {
        return $this->mapperFactory->get(CartMapper::class);
    }

    private function getSessionId(): string
    {
        return $this->session->getId();
    }

    private function getCustomerId(): int
    {
        return $this->customer->isLogged() ? (int)$this->customer->getId() : 0;
    }

    /**
     * Alpha Engine: Inicializa o contexto do carrinho.
     * Mescla carrinhos de sessão com carrinhos de cliente (se logado).
     */
    public function initializeContext(): void
    {
        if ($this->customer->isLogged() && $this->getSessionId()) {
            $this->getMapper()->mergeCartOnLogin($this->getCustomerId(), $this->getSessionId(), $this->store_id);
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
        $cartItems = $mapper->getItems($this->getCustomerId(), $this->getSessionId(), $this->store_id);
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
                $this->store_id,
                $productId,
                $quantity,
                $optionData,
                $subscriptionPlanId
            );
        }

        // Invalida o cache em memória para forçar a reconstrução na próxima leitura
        $this->isLoaded = false;
    }

    /**
     * Atualiza a quantidade de um item.
     */
    public function update(int $cartId, int $quantity): void
    {
        $this->getMapper()->updateItem($cartId, $quantity, $this->getCustomerId(), $this->getSessionId());
        $this->isLoaded = false;
    }

    /**
     * Remove um item por completo.
     */
    public function remove(int $cartId): void
    {
        $this->getMapper()->removeItem($cartId, $this->getCustomerId(), $this->getSessionId());
        $this->isLoaded = false;
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
        $this->getMapper()->clearItems($this->getCustomerId(), $this->getSessionId(), $this->store_id);
        $this->data = [];
        $this->isLoaded = true;
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

        $cartItems = $this->getMapper()->getItems($this->getCustomerId(), $this->getSessionId(), $this->store_id);
        
        if (!$cartItems) {
            $this->isLoaded = true;
            $this->data = [];
            return [];
        }

        // Alpha Engine: Fim do N+1 Query! Carregamos todos os produtos de uma vez.
        $product_ids = array_column($cartItems, 'product_id');

        /** @var ProductMapper $productMapper */
        $productMapper = $this->mapperFactory->get(ProductMapper::class);
        $productDataMap = $productMapper->getProductsByIds($product_ids, $this->language_id, $this->store_id, $this->getCustomerId());
        $productMap = array_column($productDataMap, null, 'id');

        $products = [];
        
        /** @var ProductMapper $productMapper */
        $productMapper = $this->mapperFactory->get(ProductMapper::class);
        
        // Configurações de contexto da Alpha Engine para preços B2B / Varejo
        $customerGroupId = (int)$this->config->get('config_customer_group_id');
        if ($this->customer->isLogged()) {
            $customerGroupId = (int)$this->customer->getGroupId();
        }

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

        $optionValuesMap = [];
        if (!empty($allOptionValueIds)) {
            if (method_exists($productMapper, 'getOptionValuesByIds')) {
                // O cenário ideal: O Mapper resolve tudo em apenas 1 query
                $optionValuesMap = $productMapper->getOptionValuesByIds($allOptionValueIds, $this->language_id);
            } else {
                // Fallback legado: Cache em memória para evitar queries idênticas até o Mapper ser atualizado
                foreach ($cartItems as $item) {
                    $productInfo = $productMap[$item['product_id']] ?? null;
                    if (!$productInfo) continue;

                    $options = json_decode($item['option'], true) ?: [];
                    foreach ($options as $productOptionId => $value) {
                        if (is_scalar($value) && !isset($optionValuesMap[(int)$value])) {
                            $optionValuesMap[(int)$value] = $productMapper->getOptionValue((int)$productInfo['id'], (int)$value, $this->language_id);
                        }
                    }
                }
            }
        }

        foreach ($cartItems as $item) {
            // Busca o produto no mapa em memória (O(1)) em vez de consultar o banco
            $productInfo = $productMap[$item['product_id']] ?? null;
            
            if ($productInfo) {
                $price = (float)$productInfo['price'];
                $points = (int)$productInfo['points'];
                $weight = (float)$productInfo['weight'];
                
                // Processamento de Opções Dinâmicas (Fim do // TODO)
                $optionData = [];
                $options = json_decode($item['option'], true) ?: [];
                
                foreach ($options as $productOptionId => $value) {
                    // Nota: O OpenCart trata checkbox/múltiplos como array. Simplificado para valores singulares aqui.
                    if (is_scalar($value)) {
                        // Consome a opção do cache em memória previamente carregado O(1)
                        $optionValueInfo = $optionValuesMap[(int)$value] ?? null;
                        
                        if ($optionValueInfo) {
                            // Processa Modificador de Preço
                            if ($optionValueInfo['price_prefix'] === '+') {
                                $price += (float)$optionValueInfo['price'];
                            } elseif ($optionValueInfo['price_prefix'] === '-') {
                                $price -= (float)$optionValueInfo['price'];
                            }
                            
                            // Processa Modificador de Peso
                            if ($optionValueInfo['weight_prefix'] === '+') {
                                $weight += (float)$optionValueInfo['weight'];
                            } elseif ($optionValueInfo['weight_prefix'] === '-') {
                                $weight -= (float)$optionValueInfo['weight'];
                            }
                            
                            // Mantemos os metadados da opção para a view
                            $optionData[] = ['name' => $optionValueInfo['name'], 'value' => $optionValueInfo['name']];
                        }
                    }
                }

                // Isolamento das regras de prioridade de descontos da Alpha Engine
                if ((float)$productInfo['special']) {
                    $price = (float)$productInfo['special'];
                } elseif ((float)$productInfo['discount']) {
                    $price = (float)$productInfo['discount'];
                }

                // Formatação final do produto protegendo a interface legada
                $products[] = [
                    'cart_id'               => $item['cart_id'] ?? $item['id'], // Interoperabilidade para chaves renomeadas
                    'product_id'            => $productInfo['id'],
                    'name'                  => $productInfo['name'],
                    'model'                 => $productInfo['model'],
                    'shipping'              => $productInfo['shipping'],
                    'image'                 => $productInfo['image'],
                    'option'                => $optionData,
                    'subscription'          => '',
                    'quantity'              => $item['quantity'],
                    'minimum'               => $productInfo['minimum'] ?: 1,
                    'minimum_status'        => $item['quantity'] >= ($productInfo['minimum'] ?: 1),
                    'subtract'              => $productInfo['subtract'],
                    'stock'                 => ($productInfo['quantity'] >= $item['quantity']),
                    'stock_status'          => ($productInfo['quantity'] >= $item['quantity']),
                    'price'                 => $price,
                    'price_text'            => $this->currency->format($price, $this->session->data['currency']),
                    'total'                 => $price * $item['quantity'],
                    'total_text'            => $this->currency->format($price * $item['quantity'], $this->session->data['currency']),
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
        $weight = 0.0;

        foreach ($this->getProducts() as $product) {
            if ($product['shipping']) {
                // Delega à library nativa do OpenCart (que agora já usa seu WeightClassMapper internamente)
                $weight += $this->weight->convert(
                    $product['weight'] * $product['quantity'], 
                    $product['weight_class_id'], 
                    $this->config->get('config_weight_class_id')
                );
            }
        }

        return $weight;
    }

    /**
     * Alpha Engine: Calcula a consolidação fiscal/impostos dos itens no carrinho.
     */
    public function getTaxes(): array
    {
        $tax_data = [];
        foreach ($this->getProducts() as $product) {
            if ($product['tax_class_id']) {
                $tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);
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
     * Retorna as assinaturas no carrinho (stub temporário).
     */
    public function getSubscriptions(): array
    {
        return [];
    }

    /**
     * Alpha Engine: Retorna o subtotal do carrinho (sem impostos/taxas).
     */
    public function getSubTotal(): float
    {
        $total = 0.0;
        foreach ($this->getProducts() as $product) {
            $total += $product['total'];
        }
        return $total;
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

    public function hasDownload(): bool
    {
        return false;
    }

    // Métodos obrigatórios da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { 
        return $this->getMapper()->findById($id); 
    }
    
    public function findAll(): array { 
        return $this->getMapper()->findAll(); 
    }
    
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { 
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset); 
    }
    
    public function findOneBy(array $criteria): ?InterfaceEntity { 
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
        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['reward']);
    }

    /**
     * Alpha Engine: Prepara o DTO (Data Transfer Object) para exibição do carrinho (Header/Mini-Cart).
     */
    public function getCartDisplayData(): object
    {
        $this->loadLanguage('common/cart');

        $products = [];
        
        // Alpha Engine: Utiliza o novo Presenter para processamento padronizado de imagens
        $imagePresenter = new \Alpha\Support\Presenters\ImagePresenter($this->registry);

        foreach ($this->getProducts() as $product) {
            $thumb = $imagePresenter->resize($product['image'], (int)$this->config->get('config_image_cart_width') ?: 47, (int)$this->config->get('config_image_cart_height') ?: 47);

            $products[] = [
                'cart_id'      => $product['cart_id'],
                'thumb'        => $thumb,
                'name'         => $product['name'],
                'model'        => $product['model'],
                'option'       => $product['option'],
                'subscription' => $product['subscription'],
                'quantity'     => $product['quantity'],
                'price'        => $product['price_text'],
                'total'        => $product['total_text'],
                'href'         => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
            ];
        }

        $data = [
            'text_items' => sprintf($this->language->get('text_items'), $this->countProducts(), $this->currency->format($this->getTotal(), $this->session->data['currency'])),
            'products'   => $products,
            'vouchers'   => [], // Stub para interoperabilidade com Vouchers futuros
            'cart'       => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language')),
            'checkout'   => $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'))
        ];

        // Retorna um Proxy Class (DTO) que atende a conversão "toArray()" usada no BaseController
        return new class($data) {
            private array $data;
            public function __construct(array $data) { $this->data = $data; }
            public function toArray(): array { return $this->data; }
            public function get(string $key): mixed { return $this->data[$key] ?? null; }
        };
    }

    /**
     * Alpha Engine: Consolida o DTO completo para a listagem assíncrona do Carrinho.
     * Centraliza a formatação de opções, alertas de estoque e links (ViewResponse).
     */
    public function getCartListDisplayData(): ViewResponse
    {
        $this->loadLanguage('checkout/cart');

        $data = [];

        $data['error_warning'] = $this->session->data['error'] ?? '';
        unset($this->session->data['error']);

        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        if (!$this->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
            $data['error_stock'] = $this->language->get('error_stock');
        } else {
            $data['error_stock'] = '';
        }

        if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
            $data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
        } else {
            $data['attention'] = '';
        }

        if ($this->config->get('config_cart_weight')) {
            $data['weight'] = $this->weight->format($this->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
        } else {
            $data['weight'] = '';
        }

        $data['edit'] = $this->url->link('checkout/cart.edit', 'language=' . $this->config->get('config_language'));

        $price_status = $this->customer->isLogged() || !$this->config->get('config_customer_price');

        $data['products'] = [];
        $imagePresenter = new \Alpha\Support\Presenters\ImagePresenter($this->registry);

        foreach ($this->getProducts() as $product) {
            $optionData = [];
            if (!empty($product['option'])) {
                foreach ($product['option'] as $option) {
                    $value = (string)($option['value'] ?? '');
                    $optionData[] = [
                        'name'  => $option['name'],
                        'value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value)
                    ];
                }
            }

            $data['products'][] = [
                'cart_id'      => $product['cart_id'],
                'thumb'        => $imagePresenter->resize($product['image'], (int)$this->config->get('config_image_cart_width') ?: 47, (int)$this->config->get('config_image_cart_height') ?: 47),
                'name'         => $product['name'],
                'model'        => $product['model'],
                'option'       => $optionData,
                'subscription' => $product['subscription'] ?? '',
                'quantity'     => $product['quantity'],
                'stock'        => $product['stock_status'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'minimum'      => !$product['minimum_status'] ? sprintf($this->language->get('error_minimum'), $product['minimum']) : 0,
                'price'        => $price_status ? $product['price_text'] : '',
                'total'        => $price_status ? $product['total_text'] : '',
                'href'         => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id']),
                'remove'       => $this->url->link('checkout/cart.remove', 'language=' . $this->config->get('config_language') . '&key=' . $product['cart_id'])
            ];
        }

        $data['totals'] = [];
        if ($price_status) {
            $totals = [];
            $taxes = $this->getTaxes();
            $total = 0;
            $this->getTotals($totals, $taxes, $total);
            foreach ($totals as $result) {
                $data['totals'][] = [
                    'title' => $result['title'],
                    'text'  => $this->currency->format($result['value'], $this->session->data['currency'])
                ] + $result;
            }
        }

        if ($this->hasProducts()) {
            $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));
            $data['checkout'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'));
        } else {
            $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));
        }

        return new ViewResponse($data);
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

        $sort_order = [];
        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
        }

        // Ordena as extensões (Sub-Total -> Frete -> Cupom -> Impostos -> Total)
        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                
                // Carrega a extensão legada como bridge (até refatorarmos cada uma)
                $file = DIR_EXTENSION . $result['extension'] . '/catalog/model/total/' . $result['code'] . '.php';
                if (is_file($file)) {
                    $this->load->model('extension/' . $result['extension'] . '/total/' . $result['code']);
                    
                    // Evoca a função getTotal nativamente
                    ($this->{'model_extension_' . $result['extension'] . '_total_' . $result['code']}->getTotal)($totals, $taxes, $total);
                }
            }
        }

        // Alpha Engine: Reordenação final baseada no sort_order interno injetado pelas próprias extensões
        $sort_order = [];
        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $totals);
    }
}