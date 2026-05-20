<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Mappers\CustomerMapper;

class Login extends \Opencart\System\Engine\Controller {
	public function index(): void {
		// ... lógicas de exibição de formulário ...
	}

	public function confirm(): void {
		$this->load->language('account/login');

		$json = [];

		if (isset($this->request->post['email']) && isset($this->request->post['password'])) {
			$customerMapper = new CustomerMapper();
			
			// 1. Check brute force
			$login_attempts = $customerMapper->getLoginAttempts($this->request->post['email']);

			if ($login_attempts >= (int)$this->config->get('config_login_attempts')) {
				$json['error']['warning'] = $this->language->get('error_attempts');
			}

			if (!$json) {
				// 2. Direct Mapper Invocation
				$customer = $customerMapper->getCustomerByEmail($this->request->post['email']);

				if ($customer && $customer->isStatus() && password_verify($this->request->post['password'], $customer->getPassword())) {
					// Login bem-sucedido
					$customerMapper->deleteLoginAttempts($customer->getEmail());
					
					// Inicia a sessão no objeto Customer do OpenCart (System Library)
					$this->customer->login($customer->getEmail(), $this->request->post['password']);

					$json['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
				} else {
					$json['error']['warning'] = $this->language->get('error_login');

					$customerMapper->addLoginAttempt($this->request->post['email'], oc_get_ip());
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}