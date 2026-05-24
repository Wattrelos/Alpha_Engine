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
	private CartRepository $cartRepository;

	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
		$this->cartRepository = $this->registry->get('alpha_repository_factory')->get(CartRepository::class);
	}

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$response = $this->cartRepository->getCartPageData();
		$data = $response->getData();

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
		$this->loadLanguage('checkout/cart');

		$this->response->setOutput($this->getList());
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	public function getList(): string {
		// Alpha Engine: O DTO gerado encapsula validação, formatação de imagens e alertas da sessão.
		$response = $this->cartRepository->getCartListDisplayData();
		
		$data = $response->getData();
		$data['modules'] = [];

		foreach ($data['total_extensions'] ?? [] as $extension) {
			$result = $this->load->controller('extension/' . $extension['extension'] . '/checkout/' . $extension['code']);

			if (!$result instanceof \Exception) {
				$data['modules'][] = $result;
			}
		}
		unset($data['total_extensions']);

		return $this->getTemplate('checkout/cart_list', $data);
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
		
		// Alpha Engine: Validação integral isolada no domínio
		$validation = $this->cartRepository->validateAddition($product_id, $option, $subscription_plan_id);

		if (!empty($validation['error'])) {
			$json['error'] = $validation['error'];
			if (isset($validation['redirect'])) {
				$json['redirect'] = $validation['redirect'];
			}
		} else {
			$this->cartRepository->addAndClearCheckout(
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
		$this->loadLanguage('checkout/cart');

		$json = [];

		$key = (int)($this->request->post['key'] ?? 0);
		$quantity = (int)($this->request->post['quantity'] ?? 1);

		// Alpha Engine: Repository aplica a alteração e limpa módulos dependentes
		$this->cartRepository->updateAndClearCheckout($key, $quantity);

		if ($this->cartRepository->hasProducts()) {
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

		// Alpha Engine: Remoção segura pelo repositório
		$this->cartRepository->removeAndClearCheckout($key);

		if ($this->cartRepository->hasProducts()) {
			$json['success'] = $this->language->get('text_remove');
		} else {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		$this->jsonResponse($json);
	}
}
