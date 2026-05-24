<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;

/**
 * Class Newsletter
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Newsletter extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('account/newsletter');

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/newsletter', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

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
			'text' => $this->language->get('text_newsletter'),
			'href' => $this->url->link('account/newsletter', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['save'] = $this->url->link('account/newsletter.save', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['back'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$data['newsletter'] = $this->customer->getNewsletter();

		$this->render('account/newsletter', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('account/newsletter');

		$json = [];

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/newsletter', 'language=' . $this->config->get('config_language'));

			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			// Customer
			$this->load->model('account/customer');

			$this->model_account_customer->editNewsletter($this->customer->getId(), !empty($this->request->post['newsletter']));

			$json['success'] = $this->language->get('text_success');
		}

		$this->jsonResponse($json);
	}
}
