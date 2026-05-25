<?php
namespace Opencart\Catalog\Controller\Checkout;

/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Mappers\EntityMappers\PaymentMapper;
use Alpha\Model\Domain\Repositories\InformationRepository;

/**
 * Class PaymentMethod
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class PaymentMethod extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$data = [];
		$this->loadLanguageData('checkout/payment_method', $data);

		$data['payment_method'] = $this->session->data['payment_method']['name'] ?? '';
		$data['code']           = $this->session->data['payment_method']['code'] ?? '';
		$data['comment']        = $this->session->data['comment'] ?? '';
		$data['agree']          = $this->session->data['agree'] ?? '';

		// Information
		$information_info = $this->getRepository(InformationRepository::class)->getInformation((int)$this->config->get('config_checkout_id'));

		if ($information_info) {
			$data['text_agree'] = sprintf($data['text_agree'] ?? $this->language->get('text_agree'), $this->url->link('information/information.info', 'language=' . $this->config->get('config_language') . '&information_id=' . $this->config->get('config_checkout_id')), $information_info['title']);
		} else {
			$data['text_agree'] = '';
		}

		$data['language'] = $this->config->get('config_language');

		return $this->load->view('checkout/payment_method', $data);
	}

	/**
	 * Get Methods
	 *
	 * @return void
	 */
	public function getMethods(): void {
		$this->load->language('checkout/payment_method');

		$json = [];

		$cartRepository = $this->getRepository(CartRepository::class);

		// Validate cart has products and has stock.
		if (empty($cartRepository->getProducts()) || (!$cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$cartRepository->hasMinimum()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			// Validate if customer session data is set
			if (!isset($this->session->data['customer'])) {
				$json['error'] = $this->language->get('error_customer');
			}

			if ($this->config->get('config_checkout_payment_address') && !isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
			}

			// Validate shipping
			if ($cartRepository->hasShipping()) {
				// Validate shipping address
				if (!isset($this->session->data['shipping_address']['address_id'])) {
					$json['error'] = $this->language->get('error_shipping_address');
				}

				// Validate shipping method
				if (!isset($this->session->data['shipping_method'])) {
					$json['error'] = $this->language->get('error_shipping_method');
				}
			}
		}

		if (!$json) {
			$payment_address = [];

			if ($this->config->get('config_checkout_payment_address') && isset($this->session->data['payment_address'])) {
				$payment_address = $this->session->data['payment_address'];
			} elseif ($this->config->get('config_checkout_shipping_address') && isset($this->session->data['shipping_address']['address_id'])) {
				$payment_address = $this->session->data['shipping_address'];
			}

			// Alpha Engine: Centralização via PaymentMapper
			$payment_methods = $this->mapper->get(PaymentMapper::class)->getMethods($payment_address);

			if ($payment_methods) {
				$json['payment_methods'] = $this->session->data['payment_methods'] = $payment_methods;
			} else {
				$json['error'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact', 'language=' . $this->config->get('config_language')));
			}
		}

		$this->jsonResponse($json);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('checkout/payment_method');

		$json = [];

		$cartRepository = $this->getRepository(CartRepository::class);

		// Validate cart has products and has stock.
		if (empty($cartRepository->getProducts()) || (!$cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$cartRepository->hasMinimum()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			// Validate has payment address if required
			if ($this->config->get('config_checkout_payment_address') && !isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
			}

			// Validate shipping
			if ($cartRepository->hasShipping()) {
				// Validate shipping address
				if (!isset($this->session->data['shipping_address']['address_id'])) {
					$json['error'] = $this->language->get('error_shipping_address');
				}

				// Validate shipping method
				if (!isset($this->session->data['shipping_method'])) {
					$json['error'] = $this->language->get('error_shipping_method');
				}
			}

			// Validate payment methods
			if (isset($this->request->post['payment_method']) && isset($this->session->data['payment_methods'])) {
				$payment = explode('.', $this->request->post['payment_method']);

				if (!isset($payment[0]) || !isset($payment[1]) || !isset($this->session->data['payment_methods'][$payment[0]]['option'][$payment[1]])) {
					$json['error'] = $this->language->get('error_payment_method');
				}
			} else {
				$json['error'] = $this->language->get('error_payment_method');
			}
		}

		if (!$json) {
			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$payment[0]]['option'][$payment[1]];

			$json['success'] = $this->language->get('text_success');
		}

		$this->jsonResponse($json);
	}

	/**
	 * Comment
	 *
	 * @return void
	 */
	public function comment(): void {
		$this->load->language('checkout/payment_method');

		$json = [];

		$order_id = (int)($this->session->data['order_id'] ?? 0);
		$comment  = (string)($this->request->post['comment'] ?? '');

		$orderRepository = $this->getRepository(OrderRepository::class);
		$order_info = $orderRepository->find($order_id);

		if (!$order_info) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!$json) {
			$this->session->data['comment'] = $comment;

			$orderRepository->editComment($order_id, $comment);

			$json['success'] = $this->language->get('text_comment');
		}

		$this->jsonResponse($json);
	}

	/**
	 * Agree
	 *
	 * @return void
	 */
	public function agree(): void {
		$this->load->language('checkout/payment_method');

		$json = [];

		if (isset($this->request->post['agree'])) {
			$this->session->data['agree'] = $this->request->post['agree'];
		} else {
			unset($this->session->data['agree']);
		}

		$this->jsonResponse($json);
	}
}
