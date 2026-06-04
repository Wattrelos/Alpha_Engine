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
        
        /** @var WishlistMapper $wishlistMapper */
        $wishlistMapper = $this->mapperFactory->get(WishlistMapper::class);
        /** @var ProductRepository $productRepository */
        $productRepository = $this->alpha_repository_factory->get(ProductRepository::class);

        $results = $wishlistMapper->getWishlist($customerId, $this->store_id);
        
        if (!$results) {
            return [];
        }

        // Otimização N+1 (Leitura): Coleta todos os IDs e busca os produtos em uma única query.
        $productIds = array_column($results, 'product_id');
        $productsData = $productRepository->getProducts(['filter_product_id' => $productIds]);

        // Re-indexa os produtos por ID para acesso O(1) no loop.
        $productsById = [];
        foreach ($productsData as $product) {
            $productsById[$product['product_id']] = $product;
        }

        $products = [];
        $productsToRemove = [];

        foreach ($results as $result) {
            $productId = $result['product_id'];
            $product_info = $productsById[$productId] ?? null;

            if ($product_info) {
                // O produto existe e está ativo. O DTO já vem higienizado do ProductRepository.
                $products[] = $product_info;
            } else {
                // O produto não foi encontrado (inativo, deletado), marca para remoção da wishlist.
                $productsToRemove[] = $productId;
            }
        }

        // Remoção dos produtos inválidos da wishlist utilizando o método existente.
        if (!empty($productsToRemove)) {
            foreach ($productsToRemove as $productIdToRemove) {
                $wishlistMapper->deleteWishlist($customerId, $productIdToRemove, $this->store_id);
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
        return $mapper->getTotalWishlist($customerId, $this->store_id);
    }

    /**
     * Adiciona um produto à lista de desejos.
     */
    public function addWishlist(int $productId, ?int $customerId = null): void {
        $customerId = $customerId ?? $this->getCustomerId();
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        $mapper->addWishlist($customerId, $productId, $this->store_id);
    }

    /**
     * Remove um produto da lista de desejos.
     */
    public function deleteWishlist(int $productId, ?int $customerId = null): void {
        $customerId = $customerId ?? $this->getCustomerId();
        /** @var WishlistMapper $mapper */
        $mapper = $this->mapperFactory->get(WishlistMapper::class);
        $mapper->deleteWishlist($customerId, $productId, $this->store_id);
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

    private function getCustomerId(): int {
        $customer = $this->container && $this->container->has('customer') ? $this->container->get('customer') : null;
        return ($customer && $customer->isLogged()) ? (int)$customer->getId() : 0;
    }
}
