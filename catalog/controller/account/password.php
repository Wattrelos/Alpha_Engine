<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerRepository;

/**
 * Password Controller - Modernizado para Alpha Engine.
 */
class Password extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->load->language('account/password');
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
			'href' => $this->url->link('account/password', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['save'] = $this->url->link('account/password.save', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['back'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$this->render('account/password', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('account/password');

		$json = [];

		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			$required = [
				'password' => '',
				'confirm'  => ''
			];

			$post_info = $this->request->post + $required;

			// Alpha Engine: Validações de Força e Padrão delegadas ao Domínio
			$errors = $this->getRepository(CustomerRepository::class)->validatePasswordData($post_info);
			if ($errors) {
				$json['error'] = $errors;
			}
		}

		if (!$json) {
			$this->getRepository(CustomerRepository::class)->updatePassword($this->customer->getId(), $this->request->post['password']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->jsonResponse($json);
	}
}
