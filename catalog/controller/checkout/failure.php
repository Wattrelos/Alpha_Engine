<?php
namespace Opencart\Catalog\Controller\Checkout;

use Alpha\Controller\BaseController;

/**
 * Class Failure
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class Failure extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$data = [];
		$this->loadLanguageData('checkout/failure', $data);

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
				'text' => $data['text_failure'] ?? $this->language->get('text_failure'),
				'href' => $this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'))
			]
		];

		$data['text_message'] = sprintf($data['text_message'] ?? $this->language->get('text_message'), $this->url->link('information/contact', 'language=' . $this->config->get('config_language')));

		$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		$this->render('common/success', $data);
	}
}
