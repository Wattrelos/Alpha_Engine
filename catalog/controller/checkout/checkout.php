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
	/**
	 * Index
	 *
	 * @return \Opencart\System\Engine\Action|null
	 */
	public function index(): ?\Opencart\System\Engine\Action {
		$cartRepository = $this->getRepository(CartRepository::class);

		// Validate cart to see if it has products and has stock.
		if (!$cartRepository->hasProducts() || (!$cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$cartRepository->hasMinimum()) {
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

		$data['register']         = !$this->customer->isLogged() ? (new Register($this->registry))->index() : '';
		$data['payment_address']  = ($this->customer->isLogged() && $this->config->get('config_checkout_payment_address')) ? (new PaymentAddress($this->registry))->index() : '';
		$data['shipping_address'] = ($this->customer->isLogged() && $cartRepository->hasShipping()) ? (new ShippingAddress($this->registry))->index() : '';
		$data['shipping_method']  = $cartRepository->hasShipping() ? (new ShippingMethod($this->registry))->index() : '';

		// Alpha Engine: Injeção Loader-Free dos sub-controladores
		$data['payment_method'] = (new PaymentMethod($this->registry))->index();
		$data['confirm']        = (new Confirm($this->registry))->index();

		$this->render('checkout/checkout', $data);

		return null;
	}
}
