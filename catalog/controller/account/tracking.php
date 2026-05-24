<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;

/**
 * Class Tracking
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Tracking extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/tracking', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		if (!$this->config->get('config_affiliate_status')) {
			$this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'], true));
		}

		// Affiliate
		$this->load->model('account/affiliate');

		$affiliate_info = $this->model_account_affiliate->getAffiliate($this->customer->getId());

		if (!$affiliate_info) {
			$this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'], true));
		}

		$this->load->language('account/tracking');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/tracking', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['text_description'] = sprintf($this->language->get('text_description'), $this->config->get('config_name'));

		$data['code'] = $affiliate_info['tracking'];

		$data['continue'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$data['language'] = $this->config->get('config_language');

		$data['customer_token'] = $this->session->data['customer_token'];

		$this->render('account/tracking', $data);
	}

	/**
	 * Autocomplete
	 *
	 * @return void
	 */
	public function autocomplete(): void {
		$json = [];

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['tracking'])) {
			$tracking = $this->request->get['tracking'];
		} else {
			$tracking = '';
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/password', 'language=' . $this->config->get('config_language'));

			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			$filter_data = [
				'filter_search' => $search,
				'start'         => 0,
				'limit'         => 5
			];

			// Product
			$this->load->model('catalog/product');

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$json[] = [
					'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'link' => str_replace('&amp;', '&', $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'] . '&tracking=' . $tracking))
				];
			}
		}

		$this->jsonResponse($json);
	}
}
