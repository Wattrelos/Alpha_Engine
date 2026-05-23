<?php
namespace Opencart\Catalog\Controller\Checkout;
/**
 * Alpha Engine: Imports
 */
use Alpha\Mappers\EntityMappers\ExtensionMapper;
use Alpha\Controller\BaseController;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Model\Domain\Repositories\CartRepository;

/**
 * Class Cart
 *
 * Can be loaded using $this->load->controller('checkout/cart');
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class Cart extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('checkout/cart');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
		];

		// Alpha Engine: Fim do overhead do Loader para métodos da mesma classe
		$data['list'] = $this->getList();

		$data['language'] = $this->config->get('config_language');

		// Alpha Engine: O BaseController já orquestra os Layouts e gerencia o Response automaticamente (Void)
		$this->render('checkout/cart', $data);
	}

	/**
	 * List
	 *
	 * @return void
	 */
	public function list(): void {
		$this->loadLanguage('checkout/cart');

		$this->response->setOutput($this->getList());
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	public function getList(): string {
		/** @var CartRepository $cartRepository */
		$cartRepository = $this->getRepository(CartRepository::class);
		
		// Alpha Engine: O DTO gerado encapsula validação, formatação de imagens e alertas da sessão.
		$response = $cartRepository->getCartListDisplayData();
		
		$data = $response->getData();
		$data['modules'] = [];

		/** @var \Alpha\Mappers\EntityMappers\ExtensionMapper $extensionMapper */
		$extensionMapper = $this->getMapper(\Alpha\Mappers\EntityMappers\ExtensionMapper::class);
		$extensions = $extensionMapper->getExtensionsByType('total');

		foreach ($extensions as $extension) {
			$result = $this->load->controller('extension/' . $extension['extension'] . '/checkout/' . $extension['code']);

			if (!$result instanceof \Exception) {
				$data['modules'][] = $result;
			}
		}

		return $this->load->view('checkout/cart_list', $data);
	}

	/**
	 * Add
	 *
	 * @return void
	 */
	public function add(): void {
		$this->loadLanguage('checkout/cart');

		$json = [];

		$product_id = (int)($this->request->post['product_id'] ?? 0);
		$quantity   = (int)($this->request->post['quantity'] ?? 1);
		$option     = array_filter((array)($this->request->post['option'] ?? []));
		$subscription_plan_id = (int)($this->request->post['subscription_plan_id'] ?? 0);
		
		// Alpha Engine: Acessamos o Repository (Domain) para hidratação automática em vez do Mapper
		/** @var \Alpha\Model\Domain\Repositories\ProductRepository $productRepository */
		$productRepository = $this->getRepository(\Alpha\Model\Domain\Repositories\ProductRepository::class);
		$product_info = $productRepository->getProduct($product_id);

		if ($product_info) {
			// If variant get master product
			if (!empty($product_info['master_id'])) {
				$product_id = $product_info['master_id'];
			}

			// Only use values in the override
			if (isset($product_info['override']['variant'])) {
				$override = $product_info['override']['variant'];
			} else {
				$override = [];
			}

			// Merge variant code with options
			if (!empty($product_info['variant']) && is_array($product_info['variant'])) {
				foreach ($product_info['variant'] as $key => $value) {
					if (array_key_exists($key, $override)) {
						$option[$key] = $value;
					}
				}
			}

			// Validate options
			$product_options = $productRepository->getOptions($product_id);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option_' . $product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				} elseif (($product_option['type'] == 'text') && !empty($product_option['validation']) && !oc_validate_regex($option[$product_option['product_option_id']], $product_option['validation'])) {
					$json['error']['option_' . $product_option['product_option_id']] = sprintf($this->language->get('error_regex'), $product_option['name']);
				}
			}

			// Validate subscription products
			$subscriptions = $productRepository->getSubscriptions($product_id);

			if ($subscriptions && (!$subscription_plan_id || !in_array($subscription_plan_id, array_column($subscriptions, 'subscription_plan_id')))) {
				$json['error']['subscription'] = $this->language->get('error_subscription');
			}
		} else {
			$json['error']['warning'] = $this->language->get('error_product');
		}

		if (!$json) {
			/** @var CartRepository $cartRepository */
			$cartRepository = $this->getRepository(CartRepository::class);
			
			// Alpha Engine: Orquestração e limpeza de sessão isolados no domínio
			$cartRepository->addAndClearCheckout(
				(int)$this->customer->getId(),
				$this->session->getId(),
				$product_id, 
				$quantity, 
				$option, 
				$subscription_plan_id
			);

			$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id), $product_info['name'], $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language')));
		} else {
			$json['redirect'] = $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id, true);
		}

		$this->jsonResponse($json);
	}

	/**
	 * Edit
	 *
	 * @return void
	 */
	public function edit(): void {
		$this->loadLanguage('checkout/cart');

		$json = [];

		$key = (int)($this->request->post['key'] ?? 0);
		$quantity = (int)($this->request->post['quantity'] ?? 1);

		/** @var CartRepository $cartRepository */
		$cartRepository = $this->getRepository(CartRepository::class);
		
		// Alpha Engine: Repository aplica a alteração e limpa módulos dependentes
		$cartRepository->updateAndClearCheckout($key, $quantity);

		if ($cartRepository->hasProducts()) {
			$json['success'] = $this->language->get('text_edit');
		} else {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		$this->jsonResponse($json);
	}

	/**
	 * Remove
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->loadLanguage('checkout/cart');

		$json = [];

		$key = (int)($this->request->post['key'] ?? $this->request->get['key'] ?? 0);

		/** @var CartRepository $cartRepository */
		$cartRepository = $this->getRepository(CartRepository::class);
		
		// Alpha Engine: Remoção segura pelo repositório
		$cartRepository->removeAndClearCheckout($key);

		if ($cartRepository->hasProducts()) {
			$json['success'] = $this->language->get('text_remove');
		} else {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		$this->jsonResponse($json);
	}
}
