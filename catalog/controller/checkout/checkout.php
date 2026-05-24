<?php
namespace Opencart\Catalog\Controller\Checkout;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;
use Opencart\Catalog\Controller\Checkout\Register;
use Opencart\Catalog\Controller\Checkout\PaymentAddress;
use Opencart\Catalog\Controller\Checkout\ShippingAddress;
use Opencart\Catalog\Controller\Checkout\ShippingMethod;
use Opencart\Catalog\Controller\Checkout\PaymentMethod;
use Opencart\Catalog\Controller\Checkout\Confirm;

/**
 * Class Checkout
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class Checkout extends BaseController {
	private CartRepository $cartRepository;

	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
		$this->cartRepository = $this->registry->get('alpha_repository_factory')->get(CartRepository::class);
	}

	/**
	 * Index
	 *
	 * @return \Opencart\System\Engine\Action|null
	 */
	public function index(): ?\Opencart\System\Engine\Action {
		// Validate cart to see if it has products and has stock.
		if (!$this->cartRepository->hasProducts() || (!$this->cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$this->cartRepository->hasMinimum()) {
			$this->response->redirect($this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true));
		}

		$data = [];
		$this->loadLanguageData('checkout/checkout', $data); // Alpha Engine: Unifica traduções automaticamente no array $data

		$this->document->setTitle($data['heading_title']);

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $data['text_home'],
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $data['text_cart'],
			'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $data['heading_title'],
			'href' => $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'))
		];

		if (!$this->customer->isLogged()) {
			$data['register'] = (new Register($this->registry))->index();
		} else {
			$data['register'] = '';
		}

		if ($this->customer->isLogged() && $this->config->get('config_checkout_payment_address')) {
			$data['payment_address'] = (new PaymentAddress($this->registry))->index();
		} else {
			$data['payment_address'] = '';
		}

		if ($this->customer->isLogged() && $this->cartRepository->hasShipping()) {
			$data['shipping_address'] = (new ShippingAddress($this->registry))->index();
		} else {
			$data['shipping_address'] = '';
		}

		if ($this->cartRepository->hasShipping()) {
			$data['shipping_method'] = (new ShippingMethod($this->registry))->index();
		} else {
			$data['shipping_method'] = '';
		}

		// Alpha Engine: Injeção Loader-Free dos sub-controladores
		$data['payment_method'] = (new PaymentMethod($this->registry))->index();
		$data['confirm']        = (new Confirm($this->registry))->index();

		$this->render('checkout/checkout', $data);

		return null;
	}
}
