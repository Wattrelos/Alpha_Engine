<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Mappers\EntityMappers\WishlistMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * WishlistRepository - Gerencia a lógica de negócio da lista de desejos.
 */
class WishlistRepository extends AbstractRepository implements BaseRepositoryInterface {

    /**
     * Obtém a lista de produtos da lista de desejos de forma agnóstica à view.
     */
    public function getWishlist(?int $customerId = null): array {
        // No Alpha Engine Slim, resolvemos o cliente dinamicamente se não for passado (ex: via Session AuthContext)
        $customerId = $customerId ?? $this->getCustomerId();
        $storeId = $this->getStoreId();
        
        /** @var WishlistMapper $wishlistMapper */
        $wishlistMapper = $this->mapperFactory->get(WishlistMapper::class);
        /** @var ProductRepository $productRepository */
        $productRepository = $this->alpha_repository_factory->get(ProductRepository::class);

        $results = $wishlistMapper->getWishlist($customerId, $storeId);

        $products = [];
        foreach ($results as $result) {
            $product_info = $productRepository->getProduct($result['product_id']);

            if ($product_info) {
                // Higienizamos o DTO sem formatações legadas (moeda, URLs, redimensionamento de imagens)
                // A formatação de exibição foi delegada para os Controllers/Twig no novo sistema Slim.
                $products[] = [
                    'product_id' => $product_info['product_id'] ?? $product_info['id'],
                    'name'       => $product_info['name'],
                    'model'      => $product_info['model'],
                    'image'      => $product_info['image'] ?? '',
                    'quantity'   => $product_info['quantity'],
                    'price'      => $product_info['price'],
                    'special'    => $product_info['special'] ?? null,
                    'tax_class_id' => $product_info['tax_class_id'] ?? 0,
                    'minimum'    => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1
                ];
            } else {
                $wishlistMapper->deleteWishlist($customerId, $result['product_id'], $storeId);
            }
        }

        return $products;
    }

    /**
     * Retorna o total de itens na lista de desejos do cliente.
     */
    public function getTotalWishlist(?int $customerId = null): int {
        $customerId = $customerId ?? $this->getCustomerId();
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        return $mapper->getTotalWishlist($customerId, $this->getStoreId());
    }

    /**
     * Adiciona um produto à lista de desejos.
     */
    public function addWishlist(int $productId, ?int $customerId = null): void {
        $customerId = $customerId ?? $this->getCustomerId();
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        $mapper->addWishlist($customerId, $productId, $this->getStoreId());
    }

    /**
     * Remove um produto da lista de desejos.
     */
    public function deleteWishlist(int $productId, ?int $customerId = null): void {
        $customerId = $customerId ?? $this->getCustomerId();
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        $mapper->deleteWishlist($customerId, $productId, $this->getStoreId());
    }

    // Métodos obrigatórios da interface BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity {
        return null;
    }

    public function findAll(): array {
        return [];
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        return [];
    }

    public function findOneBy(array $criteria): ?InterfaceEntity {
        return null;
    }
}
