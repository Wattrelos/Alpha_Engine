<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CustomerWishlistMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
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
                 ->set('cart_add', $this->url->link('checkout/cart.add'))
                 ->set('products', $this->getFormattedWishlistProducts($customerId))
                 ->set('continue', $this->url->link('account/account', 'customer_token=' . ($this->session->data['customer_token'] ?? '')));
    }

    /**
     * Obtém a lista de produtos da lista de desejos formatados para exibição.
     */
    public function getFormattedWishlistProducts(int $customerId): array {
        /** @var CustomerWishlistMapper $wishlistMapper */
        $wishlistMapper = $this->mapperFactory->get(CustomerWishlistMapper::class);
        /** @var ProductMapper $productMapper */
        $productMapper = $this->mapperFactory->get(ProductMapper::class);
        /** @var StockStatusMapper $stockStatusMapper */
        $stockStatusMapper = $this->mapperFactory->get(StockStatusMapper::class);

        $results = $wishlistMapper->getWishlist($customerId);

        $products = [];
        foreach ($results as $result) {
            $product_info = $productMapper->getProduct($result['product_id']);

            if ($product_info) {
                $image = '';
                if ($product_info['image'] && is_file(DIR_IMAGE . html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'))) {
                    // Assuming $this->model_tool_image is available via registry or a dedicated ImageService
                    // For now, direct call to resize, ideally this would be an ImageService
                    $image = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_wishlist_width'), $this->config->get('config_image_wishlist_height'));
                }

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
                $wishlistMapper->deleteWishlist($customerId, $result['product_id']);
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
        return $mapper->getTotalWishlist($customerId);
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