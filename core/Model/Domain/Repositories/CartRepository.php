<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CartMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\InterfaceEntity;

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
            $this->getMapper()->updateSessionToCustomer($this->getSessionId(), $this->getCustomerId());
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

        $mapper->addItem(
            $this->getCustomerId(),
            $this->getSessionId(),
            $productId,
            $quantity,
            $optionData,
            $subscriptionPlanId
        );

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
        $this->getMapper()->clearItems($this->getCustomerId(), $this->getSessionId());
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

        $cartItems = $this->getMapper()->getItems($this->getCustomerId(), $this->getSessionId());
        
        $products = [];
        
        /** @var ProductMapper $productMapper */
        $productMapper = $this->mapperFactory->get(ProductMapper::class);
        
        // Configurações de contexto da Alpha Engine para preços B2B / Varejo
        $customerGroupId = (int)$this->config->get('config_customer_group_id');
        if ($this->customer->isLogged()) {
            $customerGroupId = (int)$this->customer->getGroupId();
        }

        foreach ($cartItems as $item) {
            // O ProductMapper traz o array com os cálculos brutos preparados
            $productInfo = $productMapper->getProduct(
                (int)$item['product_id'], 
                $this->language_id, 
                $this->store_id, 
                $customerGroupId
            );

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
                        $optionValueInfo = $productMapper->getOptionValue((int)$productInfo['id'], (int)$value, $this->language_id);
                        
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
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}