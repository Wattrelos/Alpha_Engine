<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Mappers\EntityMappers\StockStatusMapper;
use Alpha\Mappers\EntityMappers\WishlistMapper;
use Alpha\Model\DataTransferObject\ViewResponse;

/**
 * WishlistRepository - Gerencia a lógica de negócio da lista de desejos.
 */
class WishlistRepository extends AbstractRepository implements BaseRepositoryInterface {

    /**
     * Consolida todos os dados para a visualização da listagem da lista de desejos (index).
     */
    public function getWishlistViewData(int $customerId, string $successMessage = ''): ViewResponse {
        $response = new ViewResponse($this->loadLanguage('account/wishlist'));

        return $response->addBreadcrumbs($this->getBaseBreadcrumbs())
                 ->addBreadcrumb($response->heading_title, $this->url->link('account/wishlist', 'customer_token=' . ($this->session->data['customer_token'] ?? '')))
                 ->setSuccess($successMessage)
                 ->set('cart', $this->url->link('common/cart.info'))
                 ->set('cart_add', '/' . $this->config->get('config_language') . '/carrinho/adicionar')
                 ->set('products', $this->getFormattedWishlistProducts($customerId))
                 ->set('continue', $this->url->link('account', 'customer_token=' . ($this->session->data['customer_token'] ?? '')));
    }

    /**
     * Retorna os breadcrumbs base para páginas da conta do cliente (Home > Conta).
     */
    protected function getBaseBreadcrumbs(): array {
        return [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
            ],
            [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . ($this->session->data['customer_token'] ?? ''))
            ]
        ];
    }

    /**
     * Obtém a lista de produtos da lista de desejos formatados para exibição.
     */
    public function getFormattedWishlistProducts(int $customerId): array {
        /** @var WishlistMapper $wishlistMapper */
        $wishlistMapper = $this->mapperFactory->get(WishlistMapper::class);
        /** @var ProductRepository $productRepository */
        $productRepository = $this->alpha_repository_factory->get(ProductRepository::class);
        /** @var StockStatusMapper $stockStatusMapper */
        $stockStatusMapper = $this->mapperFactory->get(StockStatusMapper::class);

        $storeId = (int)$this->config->get('config_store_id');
        $results = $wishlistMapper->getWishlist($customerId, $storeId);

        $products = [];
        foreach ($results as $result) {
            $product_info = $productRepository->getProduct($result['product_id']);

            if ($product_info) {
                if (!isset($product_info['product_id']) && isset($product_info['id'])) {
                    $product_info['product_id'] = $product_info['id'];
                }
                $imagePresenter = new \Alpha\Support\Presenters\ImagePresenter($this->registry);
                $image = $imagePresenter->resize(
                    $product_info['image'] ?? '',
                    (int)$this->config->get('config_image_wishlist_width'),
                    (int)$this->config->get('config_image_wishlist_height'),
                    false
                );

                $stock = '';
                if ($product_info['quantity'] <= 0) {
                    $stock_status_id = $product_info['stock_status_id'];
                } elseif (!$this->config->get('config_stock_display')) {
                    $stock_status_id = (int)$this->config->get('config_stock_status_id'); // Corrected from stock_status_id to config_stock_status_id
                } else {
                    $stock_status_id = 0; // No specific stock status if quantity > 0 and displayed
                }

                if ($stock_status_id) {
                    $stock_status_info = $stockStatusMapper->getStockStatus($stock_status_id);
                    if ($stock_status_info) {
                        $stock = $stock_status_info['name'];
                    }
                } else {
                    $stock = $product_info['quantity'];
                }

                $price = false;
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                }

                $special = false;
                if ((float)$product_info['special']) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                }

                $products[] = [
                    'product_id' => $product_info['product_id'],
                    'name'       => $product_info['name'],
                    'model'      => $product_info['model'],
                    'thumb'      => $image,
                    'stock'      => $stock,
                    'price'      => $price,
                    'special'    => $special,
                    'minimum'    => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
                    'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'remove'     => $this->url->link('account/wishlist.remove', 'product_id=' . $product_info['product_id'])
                ];
            } else {
                // If product no longer exists, remove it from the wishlist
                $wishlistMapper->deleteWishlist($customerId, $result['product_id'], $storeId);
            }
        }

        return $products;
    }

    /**
     * Retorna o total de itens na lista de desejos do cliente.
     */
    public function getTotalWishlist(int $customerId): int {
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        $storeId = (int)$this->config->get('config_store_id');
        return $mapper->getTotalWishlist($customerId, $storeId);
    }

    /**
     * Adiciona um produto à lista de desejos.
     */
    public function addWishlist(int $customerId, int $productId): void {
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        $storeId = (int)$this->config->get('config_store_id');
        $mapper->addWishlist($customerId, $productId, $storeId);
    }

    /**
     * Remove um produto da lista de desejos.
     */
    public function deleteWishlist(int $customerId, int $productId): void {
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        $storeId = (int)$this->config->get('config_store_id');
        $mapper->deleteWishlist($customerId, $productId, $storeId);
    }

    // Métodos obrigatórios da interface BaseRepositoryInterface (placeholders)
    public function find(int $id): ?\Alpha\Model\Domain\InterfaceEntity {
        return null;
    }

    public function findAll(): array {
        return [];
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        return [];
    }

    public function findOneBy(array $criteria): ?\Alpha\Model\Domain\InterfaceEntity {
        return null;
    }
}
