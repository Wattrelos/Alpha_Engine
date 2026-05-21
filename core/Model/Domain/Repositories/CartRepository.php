<?php

namespace Alpha\Model\Domain\Repositories;

use Opencart\System\Engine\Registry;
use Alpha\Mappers\CartMapper;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Mappers\ProductMapper;
use Alpha\Model\Domain\Repositories\WeightClassRepository;

/**
 * Class CartRepository
 * 
 * Orquestra as regras de negócio do carrinho de compras na Alpha Engine.
 * Atua centralizando a hidratação dos itens através de Mappers e 
 * outros Repositórios (como Product e Tax), isolando o SQL.
 */
class CartRepository
{
    private CartMapper $cartMapper;
    private ProductRepository $productRepository;
    private ProductMapper $productMapper;
    private WeightClassRepository $weightClassRepository;
    private object $session;
    private object $customer;
    private object $config;
    private object $tax;

    /**
     * @var array<int, array<string, mixed>> Cache em memória dos produtos processados
     */
    private array $data = []; 

    public function __construct(Registry $registry)
    {
        $this->session = $registry->get('session');
        $this->customer = $registry->get('customer');
        $this->config = $registry->get('config');
        $this->tax = $registry->get('tax');
        
        // Injeção de Mappers e Repositórios pela Alpha Engine Factory
        $mapperFactory = $registry->get('mapperFactory');
        $this->cartMapper = $mapperFactory->get(CartMapper::class);
        
        $repositoryFactory = $registry->get('repository');
        $this->productRepository = $repositoryFactory->get(ProductRepository::class);
        $this->weightClassRepository = $repositoryFactory->get(WeightClassRepository::class);
        $this->productMapper = $mapperFactory->get(ProductMapper::class);
    }

    /**
     * Inicializa o contexto do carrinho (Visitante vs Cliente Logado).
     */
    public function initializeContext(): void
    {
        $storeId = (int)$this->config->get('config_store_id');
        
        // 1. Limpa carrinhos abandonados de visitantes.
        $this->cartMapper->deleteExpiredCarts($storeId, (int)$this->config->get('config_session_expire'));

        // 2. Se logado, atualiza o ID da sessão dos itens antigos e mescla com os atuais.
        if ($this->customer->isLogged()) {
            $this->cartMapper->mergeCustomerCart(
                (int)$this->customer->getId(), 
                $this->session->getId(), 
                $storeId
            );
        }
    }

    /**
     * Obtém os produtos do carrinho totalmente hidratados.
     */
    public function getProducts(): array
    {
        if (!$this->data) {
            // 1. Busca os itens cruzados do banco via Mapper
            $cartItems = $this->cartMapper->findAllByContext(
                (int)$this->customer->getId(),
                $this->session->getId(),
                (int)$this->config->get('config_store_id')
            );

            $languageId = (int)$this->config->get('config_language_id');
            $storeId = (int)$this->config->get('config_store_id');
            $customerGroupId = $this->customer->isLogged() 
                ? (int)$this->customer->getGroupId() 
                : (int)$this->config->get('config_customer_group_id');
            
            $this->data = [];

            foreach ($cartItems as $item) {
                // Busca o produto no Mapper (Trazendo base, specials e pontos)
                $product = $this->productMapper->getProduct($item['product_id'], $languageId, $storeId, $customerGroupId);

                if (!$product) {
                    $this->remove($item['cart_id']);
                    continue;
                }

                // --- Cálculos de Preço (Isolando a regra do OpenCart) ---
                $price = (float)$product['price'];

                // 1. Aplica descontos por quantidade (Progressivo)
                $discounts = $this->productMapper->getDiscounts($item['product_id'], $customerGroupId);
                foreach ($discounts as $discount) {
                    if ($item['quantity'] >= $discount['quantity']) {
                        $price = (float)$discount['price'];
                        break; // Primeiro que satisfaz (já ordenado por prioridade/quantidade no Mapper)
                    }
                }

                // 2. Aplica o Preço Especial (Se existir e se sobrepor ao desconto)
                if (isset($product['special']) && $product['special'] !== null) {
                    $price = (float)$product['special'];
                }

                // --- Configurações Base do Produto ---
                $weight = (float)$product['weight'];
                $points = (int)($product['points'] ?? 0);
                $stock = ($product['quantity'] >= $item['quantity']);
                $optionData = [];
                $options = json_decode($item['option'], true) ?: [];

                // --- Processamento de Opções (Hidratação via Mapper) ---
                foreach ($options as $productOptionId => $value) {
                    $optionQuery = $this->productMapper->getOption($item['product_id'], $productOptionId, $languageId);

                    if ($optionQuery) {
                        // Trata tipos com seleção de valores pré-definidos (Radio, Select, Image, Checkbox)
                        if (in_array($optionQuery['type'], ['select', 'radio', 'image', 'checkbox'])) {
                            // Se for checkbox, o valor é um array de múltiplos IDs; caso contrário, array com 1 item
                            $optionValues = is_array($value) ? $value : [$value];

                            foreach ($optionValues as $productOptionValueId) {
                                $optionValueQuery = $this->productMapper->getOptionValue($item['product_id'], $productOptionValueId, $languageId);

                                if ($optionValueQuery) {
                                    // Aplica incrementos de Preço
                                    if ($optionValueQuery['price_prefix'] === '+') {
                                        $price += (float)$optionValueQuery['price'];
                                    } elseif ($optionValueQuery['price_prefix'] === '-') {
                                        $price -= (float)$optionValueQuery['price'];
                                    }

                                    // Aplica incrementos de Pontos
                                    if ($optionValueQuery['points_prefix'] === '+') {
                                        $points += (int)$optionValueQuery['points'];
                                    } elseif ($optionValueQuery['points_prefix'] === '-') {
                                        $points -= (int)$optionValueQuery['points'];
                                    }

                                    // Aplica incrementos de Peso
                                    if ($optionValueQuery['weight_prefix'] === '+') {
                                        $weight += (float)$optionValueQuery['weight'];
                                    } elseif ($optionValueQuery['weight_prefix'] === '-') {
                                        $weight -= (float)$optionValueQuery['weight'];
                                    }

                                    // Valida o estoque da opção
                                    if ($optionValueQuery['subtract'] && (!$optionValueQuery['quantity'] || ($optionValueQuery['quantity'] < $item['quantity']))) {
                                        $stock = false;
                                    }

                                    $optionData[] = [
                                        'product_option_id'       => $productOptionId,
                                        'product_option_value_id' => $productOptionValueId,
                                        'option_id'               => $optionQuery['option_id'],
                                        'option_value_id'         => $optionValueQuery['option_value_id'],
                                        'name'                    => $optionQuery['name'],
                                        'value'                   => $optionValueQuery['name'],
                                        'type'                    => $optionQuery['type'],
                                        'quantity'                => $optionValueQuery['quantity'],
                                        'subtract'                => $optionValueQuery['subtract'],
                                        'price'                   => $optionValueQuery['price'],
                                        'price_prefix'            => $optionValueQuery['price_prefix'],
                                        'points'                  => $optionValueQuery['points'],
                                        'points_prefix'           => $optionValueQuery['points_prefix'],
                                        'weight'                  => $optionValueQuery['weight'],
                                        'weight_prefix'           => $optionValueQuery['weight_prefix']
                                    ];
                                }
                            }
                        } else {
                            // Trata tipos de texto, data ou arquivo (Sem incremento financeiro/físico)
                            $optionData[] = [
                                'product_option_id'       => $productOptionId,
                                'product_option_value_id' => '',
                                'option_id'               => $optionQuery['option_id'],
                                'option_value_id'         => '',
                                'name'                    => $optionQuery['name'],
                                'value'                   => $value,
                                'type'                    => $optionQuery['type'],
                                'quantity'                => '',
                                'subtract'                => '',
                                'price'                   => '',
                                'price_prefix'            => '',
                                'points'                  => '',
                                'points_prefix'           => '',
                                'weight'                  => '',
                                'weight_prefix'           => ''
                            ];
                        }
                    }
                }

                $total = $price * $item['quantity'];

                $this->data[] = [
                    'cart_id'         => $item['cart_id'],
                    'product_id'      => $product['id'],
                    'name'            => $product['name'],
                    'model'           => $product['model'],
                    'shipping'        => $product['shipping'],
                    'image'           => $product['image'],
                    'option'          => $optionData,
                    'quantity'        => $item['quantity'],
                    'minimum'         => $product['minimum'],
                    'subtract'        => $product['subtract'],
                    'stock'           => $stock,
                    'price'           => $price,
                    'total'           => $total,
                    'reward'          => (int)($product['reward'] ?? 0) * $item['quantity'],
                    'points'          => $points * $item['quantity'],
                    'tax_class_id'    => $product['tax_class_id'],
                    'weight'          => $weight * $item['quantity'],
                    'weight_class_id' => $product['weight_class_id'],
                    'length'          => $product['length'],
                    'width'           => $product['width'],
                    'height'          => $product['height'],
                    'length_class_id' => $product['length_class_id']
                ];
            }
        }

        return $this->data;
    }

    public function add(int $product_id, int $quantity = 1, array $option = [], int $subscription_plan_id = 0, array $override = []): void
    {
        $optionHash = $option ? json_encode($option) : '';

        // Lógica de verificação se o item já existe para apenas somar a quantidade
        // ocorrerá aqui no Repositório no futuro. Por ora, insere cru:
        $this->cartMapper->insert(
            (int)$this->customer->getId(),
            $this->session->getId(),
            (int)$this->config->get('config_store_id'),
            $product_id,
            $quantity,
            $optionHash,
            $subscription_plan_id
        );

        $this->data = []; // Invalida o cache em memória
    }

    public function update(int $cart_id, int $quantity): void
    {
        $this->cartMapper->updateQuantity($cart_id, $quantity);
        $this->data = [];
    }

    public function has(int $cart_id): bool
    {
        $products = $this->getProducts();
        $cartIds = array_column($products, 'cart_id');
        
        return in_array($cart_id, $cartIds);
    }

    public function remove(int $cart_id): void
    {
        $this->cartMapper->delete($cart_id);
        $this->data = [];
    }

    public function clear(): void
    {
        $this->cartMapper->clearByContext(
            (int)$this->customer->getId(),
            $this->session->getId(),
            (int)$this->config->get('config_store_id')
        );
        
        $this->data = [];
    }

    public function getSubscriptions(): array
    {
        return [];
    }

    public function getWeight(): float
    {
        $weight = 0.0;
        $storeWeightClassId = (int)$this->config->get('config_weight_class_id');

        foreach ($this->getProducts() as $product) {
            if ($product['shipping']) {
                // Normaliza o peso do produto para a unidade de peso oficial configurada na loja
                $weight += $this->weightClassRepository->convert($product['weight'], $product['weight_class_id'], $storeWeightClassId);
            }
        }
        return $weight;
    }

    public function getSubTotal(): float
    {
        $total = 0.0;
        foreach ($this->getProducts() as $product) {
            $total += $product['total'];
        }
        return $total;
    }

    public function getTaxes(): array
    {
        $taxData = [];

        foreach ($this->getProducts() as $product) {
            if ($product['tax_class_id']) {
                // Chama a biblioteca de impostos para calcular alíquotas baseadas na classe
                $taxRates = $this->tax->getRates($product['price'], $product['tax_class_id']);

                foreach ($taxRates as $taxRate) {
                    if (!isset($taxData[$taxRate['tax_rate_id']])) {
                        $taxData[$taxRate['tax_rate_id']] = ($taxRate['amount'] * $product['quantity']);
                    } else {
                        $taxData[$taxRate['tax_rate_id']] += ($taxRate['amount'] * $product['quantity']);
                    }
                }
            }
        }

        return $taxData;
    }

    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->getProducts() as $product) {
            $total += $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];
        }
        return $total;
    }

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
            if (!$product['minimum'] || $product['quantity'] < $product['minimum']) {
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
}