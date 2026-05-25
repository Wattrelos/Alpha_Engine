<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerRepository;

class Login extends BaseController {
	public function index(): void {
		// ... lógicas de exibição de formulário ...
	}

	public function confirm(): void {
		$this->loadLanguageData('account/login');

		$json = [];

		if (isset($this->request->post['email']) && isset($this->request->post['password'])) {
			$customerRepository = $this->getRepository(CustomerRepository::class);
			$email = (string)$this->request->post['email'];
			$password = (string)$this->request->post['password'];
			
			// 1. Check brute force
			if ($customerRepository->isLockedOut($email, (int)$this->config->get('config_login_attempts'))) {
				$json['error']['warning'] = $this->language->get('error_attempts');
			}

			if (!$json) {
				// 2. Domain Authentication (Skinny Controller)
				$customer = $customerRepository->authenticate($email, $password);

				if ($customer && $customer->isStatus()) {
					// Login bem-sucedido
					$customerRepository->resetLoginAttempts($email);
					
					// Inicia a sessão no objeto Customer do OpenCart (System Library)
					$this->customer->login($email, $password);

					$json['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
				} else {
					$json['error']['warning'] = $this->language->get('error_login');

					$customerRepository->addLoginAttempt($email, oc_get_ip());
				}
			}
		}

		// Utiliza o auxiliar de resposta nativo da Alpha Engine
		$this->jsonResponse($json);
	}
}