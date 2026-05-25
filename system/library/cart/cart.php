<?php
namespace Opencart\System\Library\Cart;

use Alpha\Model\Domain\Repositories\CartRepository;

/**
 * Class Cart (Legacy Bridge)
 *
 * Refatorado para Alpha Engine. Esta classe agora atua apenas como um Proxy
 * para o CartRepository, mantendo a compatibilidade com o ecossistema legado.
 * @package Opencart\System\Library\Cart
 */
class Cart {
	private CartRepository $cartRepository;

	/**
	 * Constructor
	 *
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct(\Opencart\System\Engine\Registry $registry) {
		// Resolução via Repository Factory no Registry da Alpha Engine
		$repositoryFactory = $registry->get('alpha_repository_factory');
		$this->cartRepository = $repositoryFactory->get(CartRepository::class);

		// O CartRepository assume a responsabilidade de limpar sessões antigas
		// e mesclar o carrinho do visitante com o do cliente logado.
		$this->cartRepository->initializeContext();
	}

	/**
	 * Get Products
	 */
	public function getProducts(): array {
		return $this->cartRepository->getProducts();
	}

	/**
	 * Add
	 */
	public function add(int $product_id, int $quantity = 1, array $option = [], int $subscription_plan_id = 0, array $override = []): void {
		$this->cartRepository->add($product_id, $quantity, $option, $subscription_plan_id, $override);
	}

	/**
	 * Update
	 */
	public function update(int $cart_id, int $quantity): void {
		$this->cartRepository->update($cart_id, $quantity);
	}

	/**
	 * Has
	 */
	public function has(int $cart_id): bool {
		return $this->cartRepository->has($cart_id);
	}

	/**
	 * Remove
	 */
	public function remove(int $cart_id): void {
		$this->cartRepository->remove($cart_id);
	}

	/**
	 * Clear
	 */
	public function clear(): void {
		$this->cartRepository->clear();
	}

	/**
	 * Get Subscriptions
	 */
	public function getSubscriptions(): array {
		return $this->cartRepository->getSubscriptions();
	}

	/**
	 * Get Weight
	 */
	public function getWeight(): float {
		return $this->cartRepository->getWeight();
	}

	/**
	 * Get Sub Total
	 */
	public function getSubTotal(): float {
		return $this->cartRepository->getSubTotal();
	}

	/**
	 * Get Taxes
	 */
	public function getTaxes(): array {
		return $this->cartRepository->getTaxes();
	}

	/**
	 * Get Total
	 */
	public function getTotal(): float {
		return $this->cartRepository->getTotal();
	}

	/**
	 * Count Products
	 */
	public function countProducts(): int {
		return $this->cartRepository->countProducts();
	}

	/**
	 * Has Products
	 */
	public function hasProducts(): bool {
		return $this->cartRepository->hasProducts();
	}

	/**
	 * Has Subscription
	 */
	public function hasSubscription(): bool {
		return $this->cartRepository->hasSubscription();
	}

	/**
	 * Has Stock
	 */
	public function hasStock(): bool {
		return $this->cartRepository->hasStock();
	}

	/**
	 * Has Minimum
	 */
	public function hasMinimum(): bool {
		return $this->cartRepository->hasMinimum();
	}

	/**
	 * Has Shipping
	 */
	public function hasShipping(): bool {
		return $this->cartRepository->hasShipping();
	}

	/**
	 * Has Download
	 */
	public function hasDownload(): bool {
		return $this->cartRepository->hasDownload();
	}
}
