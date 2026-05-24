<?php
namespace Opencart\Catalog\Controller\Checkout;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Mappers\EntityMappers\ShippingMapper;
/**
 * Class ShippingMethod
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class ShippingMethod extends BaseController {
	private CartRepository $cartRepository;
	private ShippingMapper $shippingMapper;

	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
		$this->cartRepository = $this->registry->get('alpha_repository_factory')->get(CartRepository::class);
		$this->shippingMapper = $this->registry->get('mapperFactory')->get(ShippingMapper::class);
	}

	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$data = [];
		$this->loadLanguageData('checkout/shipping_method', $data);

		if (isset($this->session->data['shipping_method'])) {
			$data['shipping_method'] = $this->session->data['shipping_method']['name'];
			$data['code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['shipping_method'] = '';
			$data['code'] = '';
		}

		$data['language'] = $this->config->get('config_language');

		return $this->getTemplate('checkout/shipping_method', $data);
	}

	/**
	 * Quote
	 *
	 * @return void
	 */
	public function quote(): void {
		$this->load->language('checkout/shipping_method');

		$json = [];

		// Validate cart has products and has stock.
		if (empty($this->cartRepository->getProducts()) || (!$this->cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$this->cartRepository->hasMinimum()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			if (!$this->customer->isLogged()) {
				$json['error'] = $this->language->get('error_customer');
			}

			// Validate if payment address is set if required in settings
			if ($this->config->get('config_checkout_payment_address') && !isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
			}

			// Validate if shipping not required. If not the customer should not have reached this page.
			if ($this->cartRepository->hasShipping() && !isset($this->session->data['shipping_address']['address_id'])) {
				$json['error'] = $this->language->get('error_shipping_address');
			}
		}

		if (!$json) {
			// Alpha Engine: Instanciação nativa do ShippingMapper via Factory
			$shipping_methods = $this->shippingMapper->getMethods($this->session->data['shipping_address']);

			if ($shipping_methods) {
				$json['shipping_methods'] = $this->session->data['shipping_methods'] = $shipping_methods;
			} else {
				$json['error'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact', 'language=' . $this->config->get('config_language')));
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
		$this->load->language('checkout/shipping_method');

		$json = [];

		// Validate cart has products and has stock.
		if (empty($this->cartRepository->getProducts()) || (!$this->cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$this->cartRepository->hasMinimum()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			if (!$this->customer->isLogged()) {
				$json['error'] = $this->language->get('error_customer');
			}

			// Validate if payment address is set if required in settings
			if ($this->config->get('config_checkout_payment_address') && !isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
			}

			// Validate if shipping not required. If not the customer should not have reached this page.
			if ($this->cartRepository->hasShipping() && !isset($this->session->data['shipping_address']['address_id'])) {
				$json['error'] = $this->language->get('error_shipping_address');
			}

			if (isset($this->request->post['shipping_method'])) {
				$shipping = explode('.', $this->request->post['shipping_method']);

				if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
					$json['error'] = $this->language->get('error_shipping_method');
				}
			} else {
				$json['error'] = $this->language->get('error_shipping_method');
			}
		}

		if (!$json) {
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

			$json['success'] = $this->language->get('text_success');

			// Clear payment methods
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->jsonResponse($json);
	}
}
