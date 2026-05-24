<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;

/**
 * Class Transaction
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Transaction extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('account/transaction');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/transaction', 'language=' . $this->config->get('config_language'));

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
			'text' => $this->language->get('text_transaction'),
			'href' => $this->url->link('account/transaction', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));

		$limit = 10;

		// Transaction
		$data['transactions'] = [];

		$filter_data = [
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		];

		$this->load->model('account/transaction');

		$results = $this->model_account_transaction->getTransactions($this->customer->getId(), $filter_data);

		foreach ($results as $result) {
			$data['transactions'][] = [
				'amount'     => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			] + $result;
		}

		$transaction_total = $this->model_account_transaction->getTotalTransactions($this->customer->getId());

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $transaction_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('account/transaction', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($transaction_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($transaction_total - $limit)) ? $transaction_total : ((($page - 1) * $limit) + $limit), $transaction_total, ceil($transaction_total / $limit));

		$data['total'] = $this->currency->format($this->customer->getBalance(), $this->session->data['currency']);

		$data['continue'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		// Alpha Engine: Renderização envelopada
		$this->render('account/transaction', $data);
	}
}
