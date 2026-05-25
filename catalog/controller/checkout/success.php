<?php
namespace Opencart\Catalog\Controller\Checkout;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;

/**
 * Class Success
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class Success extends BaseController {
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
		$data = [];
		$this->loadLanguageData('checkout/success', $data);

		if (isset($this->session->data['order_id'])) {
			$this->cartRepository->clear();

			unset($this->session->data['order_id']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['comment']);
			unset($this->session->data['agree']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
		}

		$this->document->setTitle($data['heading_title'] ?? $this->language->get('heading_title'));

		$data['breadcrumbs'] = [
			[
				'text' => $data['text_home'] ?? $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
			],
			[
				'text' => $data['text_basket'] ?? $this->language->get('text_basket'),
				'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
			],
			[
				'text' => $data['text_checkout'] ?? $this->language->get('text_checkout'),
				'href' => $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'))
			],
			[
				'text' => $data['text_success'] ?? $this->language->get('text_success'),
				'href' => $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'))
			]
		];

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($data['text_customer'] ?? $this->language->get('text_customer'), $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']), $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']), $this->url->link('account/download', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']), $this->url->link('information/contact', 'language=' . $this->config->get('config_language')));
		} else {
			$data['text_message'] = sprintf($data['text_guest'] ?? $this->language->get('text_guest'), $this->url->link('information/contact', 'language=' . $this->config->get('config_language')));
		}

		$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		$this->render('common/success', $data);
	}
}
