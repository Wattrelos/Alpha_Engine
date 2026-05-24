<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Model\Domain\Repositories\CustomerRepository;

class Login extends \Opencart\System\Engine\Controller {
	public function index(): void {
		// ... lógicas de exibição de formulário ...
	}

	public function confirm(): void {
		$this->load->language('account/login');

		$json = [];

		if (isset($this->request->post['email']) && isset($this->request->post['password'])) {
			$customerRepository = $this->registry->get('alpha_repository_factory')->get(CustomerRepository::class);
			
			// 1. Check brute force
			$login_attempts = $customerRepository->getLoginAttempts($this->request->post['email']);

			if ($login_attempts >= (int)$this->config->get('config_login_attempts')) {
				$json['error']['warning'] = $this->language->get('error_attempts');
			}

			if (!$json) {
				// 2. Repository Invocation
				$customer = $customerRepository->findByEmail($this->request->post['email']);

				if ($customer && $customer->isStatus() && password_verify($this->request->post['password'], $customer->getPassword())) {
					// Login bem-sucedido
					$customerRepository->deleteLoginAttempts($customer->getEmail());
					
					// Inicia a sessão no objeto Customer do OpenCart (System Library)
					$this->customer->login($customer->getEmail(), $this->request->post['password']);

					$json['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
				} else {
					$json['error']['warning'] = $this->language->get('error_login');

					$customerRepository->addLoginAttempt($this->request->post['email'], oc_get_ip());
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}