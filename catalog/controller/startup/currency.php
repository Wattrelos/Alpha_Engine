<?php
namespace Opencart\Catalog\Controller\Startup;
/**
 * Class Currency
 *
 * @package Opencart\Catalog\Controller\Startup
 */
class Currency extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$code = '';

		// Alpha Engine: Instancia a biblioteca que já consome a nova arquitetura
		$this->registry->set('currency', new \Opencart\System\Library\Cart\Currency($this->registry));

		if (isset($this->session->data['currency'])) {
			$code = $this->session->data['currency'];
		}

		if (isset($this->request->cookie['currency']) && !$this->currency->has($code)) {
			$code = $this->request->cookie['currency'];
		}

		if (!$this->currency->has($code)) {
			$code = $this->config->get('config_currency');
		}

		if (!isset($this->session->data['currency']) || $this->session->data['currency'] != $code) {
			$this->session->data['currency'] = $code;
		}

		// Set a new currency cookie if the code does not match the current one
		if (!isset($this->request->cookie['currency']) || $this->request->cookie['currency'] != $code) {
			$option = [
				'expires'  => time() + 60 * 60 * 24 * 30,
				'path'     => '/',
				'SameSite' => 'Lax'
			];

			setcookie('currency', $code, $option);
		}
	}
}
