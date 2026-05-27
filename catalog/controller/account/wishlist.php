<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\WishlistRepository;

/**
 * Class Wish List
 *
 * @package Opencart\Catalog\Controller\Account
 */
class WishList extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		// Alpha Engine: Uma única chamada ao Repositório para abastecer a View
		$data = $this->getRepository(WishlistRepository::class)->getWishlistViewData($this->customer->getId(), $this->session->data['success'] ?? '')->toArray();
		
		unset($this->session->data['success']);

		$this->document->setTitle($data['heading_title'] ?? '');

		$data['list'] = $this->getTemplate('account/wishlist_list', ['products' => $data['products']]); // Pass only products to the list template

		$this->render('account/wishlist', $data);
	}

	/**
	 * List
	 *
	 * @return void
	 */
	public function list(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language'));


			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$data['products'] = $this->getRepository(WishlistRepository::class)->getFormattedWishlistProducts($this->customer->getId());
		$this->response->setOutput($this->getTemplate('account/wishlist_list', $data));
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	// Removed getList() as its logic is now in WishlistRepository
	protected function getList(): string {
		// This method is now obsolete. The logic has been moved to WishlistRepository.
		// The 'list' action directly calls getFormattedWishlistProducts and renders the template.
		return '';
	}

	/**
	 * Add
	 *
	 * @return void
	 */
	public function add(): void {
		$this->load->language('account/wishlist');
		$wishlistRepository = $this->getRepository(WishlistRepository::class);

		$json = [];

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		} 

		// Product
		$productRepository = $this->getRepository(\Alpha\Model\Domain\Repositories\ProductRepository::class);
		$product_info = $productRepository->getProduct($product_id);

		if (!$product_info) {
			$json['error'] = $this->language->get('error_product');
		}

		if (!$json) {
			if (!isset($this->session->data['wishlist'])) {
				$this->session->data['wishlist'] = [];
			}

			$this->session->data['wishlist'][] = $product_id;

			$this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);

			// Logged in. We store the product ID into the wishlist
			if ($this->customer->isLogged()) {
				$wishlistRepository->addWishlist($this->customer->getId(), $product_id);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_id), $product_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), $wishlistRepository->getTotalWishlist($this->customer->getId()));
			} else {
				$json['error'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'), $this->url->link('product/product', 'product_id=' . $product_id), $product_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			}
		}

		$this->jsonResponse($json);
	}

	/**
	 * Remove
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->load->language('account/wishlist');
		$wishlistRepository = $this->getRepository(WishlistRepository::class);

		$json = [];

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$json['error'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'), $this->url->link('product/product', 'product_id=' . (int)$product_id), $this->url->link('account/wishlist'));
		}

		if (!$json) {
			$wishlistRepository->deleteWishlist($this->customer->getId(), $product_id);

			$json['success'] = $this->language->get('text_remove');
		}

		$this->jsonResponse($json);
	}
}
