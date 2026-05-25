<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerAffiliateRepository;

/**
 * Class Account
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Account extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$data = [];
		$this->loadLanguageData('account/account', $data); // Alpha Engine: Carregamento unificado e injeção automática de dicionário

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['edit']           = $this->url->link('account/edit', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['password']       = $this->url->link('account/password', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['address']        = $this->url->link('account/address', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['payment_method'] = $this->url->link('account/payment_method', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['wishlist']       = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['order']          = $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$data['subscription'] = $this->url->link('account/subscription', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['download'] = $this->url->link('account/download', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		if ($this->config->get('total_reward_status')) {
			$data['reward'] = $this->url->link('account/reward', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		} else {
			$data['reward'] = '';
		}

		$data['return'] = $this->url->link('account/returns', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['transaction'] = $this->url->link('account/transaction', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['newsletter'] = $this->url->link('account/newsletter', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		if ($this->config->get('config_affiliate_status')) {
			$data['affiliate'] = $this->url->link('account/affiliate', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

			// Alpha Engine: Uso do Repository em vez da camada de Mapper direta
			$affiliateRepository = $this->getRepository(CustomerAffiliateRepository::class);
			$affiliate_info = $affiliateRepository->findOneBy(['customer_id' => $this->customer->getId()]);

			if ($affiliate_info) {
				$data['tracking'] = $this->url->link('account/tracking', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			} else {
				$data['tracking'] = '';
			}
		} else {
			$data['affiliate'] = '';
		}
		
		// Alpha Engine: Renderização padronizada
		$this->render('account/account', $data);
	}
}
