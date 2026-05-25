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
		$response = $this->getRepository(CartRepository::class)->getCartPageData();
		$data = $response->getData();

		// Alpha Engine: Injeta as variáveis de tradução diretamente no array $data da view principal
		$this->loadLanguageData('checkout/cart', $data);

		// Alpha Engine: Fim do overhead do Loader para métodos da mesma classe
		$data['list'] = $this->getList();

		// Alpha Engine: O BaseController já orquestra os Layouts e gerencia o Response automaticamente (Void)
		$this->render('checkout/cart', $data);
	}

	/**
	 * List
	 *
	 * @return void
	 */
	public function list(): void {
		$this->load->language('checkout/cart');

		$this->response->setOutput($this->getList());
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	public function getList(): string {
		// Alpha Engine: O DTO gerado encapsula validação, formatação de imagens e alertas da sessão.
		$response = $this->getRepository(CartRepository::class)->getCartListDisplayData();
		
		$data = $response->getData();

		// Alpha Engine: Injeta as variáveis de tradução do carrinho no array do template
		$this->loadLanguageData('checkout/cart', $data);

		$data['modules'] = [];

		foreach ($data['total_extensions'] ?? [] as $extension) {
			$result = $this->load->controller('extension/' . $extension['extension'] . '/checkout/' . $extension['code']);

			if (!$result instanceof \Exception) {
				$data['modules'][] = $result;
			}
		}
		unset($data['total_extensions']);

		return $this->load->view('checkout/cart_list', $data);
	}

	/**
	 * Add
	 *
	 * @return void
	 */
	public function add(): void {
		$this->load->language('checkout/cart');

		$json = [];

		$product_id = (int)($this->request->post['product_id'] ?? 0);
		$quantity   = (int)($this->request->post['quantity'] ?? 1);
		$option     = array_filter((array)($this->request->post['option'] ?? []));
		$subscription_plan_id = (int)($this->request->post['subscription_plan_id'] ?? 0);
		
		$cartRepository = $this->getRepository(CartRepository::class);

		// Alpha Engine: Validação integral isolada no domínio
		$validation = $cartRepository->validateAddition($product_id, $option, $subscription_plan_id);

		if (!empty($validation['error'])) {
			$json['error'] = $validation['error'];
			if (isset($validation['redirect'])) {
				$json['redirect'] = $validation['redirect'];
			}
		} else {
			$cartRepository->addAndClearCheckout(
				(int)$this->customer->getId(),
				$this->session->getId(),
				$product_id, 
				$quantity, 
				$validation['option_data'] ?? $option, 
				$subscription_plan_id
			);

			$json['success'] = sprintf(
				$this->language->get('text_success'), 
				$this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id), 
				$validation['product_name'], 
				$this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
			);
		}

		$this->jsonResponse($json);
	}

	/**
	 * Edit
	 *
	 * @return void
	 */
	public function edit(): void {
		$this->load->language('checkout/cart');

		$json = [];

		$key = (int)($this->request->post['key'] ?? 0);
		$quantity = (int)($this->request->post['quantity'] ?? 1);

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
		$this->load->language('checkout/cart');

		$json = [];

		$key = (int)($this->request->post['key'] ?? $this->request->get['key'] ?? 0);

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
