<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;

/**
 * Class Logout
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Logout extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if ($this->customer->isLogged()) {
			$this->customer->logout();

			unset($this->session->data['customer']);
			unset($this->session->data['customer_token']);
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_address']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);

			$this->response->redirect($this->url->link('account/logout', 'language=' . $this->config->get('config_language'), true));
		}

		$data = [];
		$this->loadLanguageData('account/logout', $data);

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_logout'),
			'href' => $this->url->link('account/logout', 'language=' . $this->config->get('config_language'))
		];

		$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		$this->render('common/success', $data);
	}
}
