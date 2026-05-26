<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerAffiliateRepository;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;
use Alpha\Model\Domain\Repositories\InformationRepository;

/**
 * Class Affiliate
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Affiliate extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$data = [];
		$this->loadLanguageData('account/affiliate', $data);

		if (!$this->customer->isLogged()) {
			$this->customer->logout();

			$this->session->data['redirect'] = $this->url->link('account/affiliate', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), $this->config->get('config_file_max_size'));

		$data['config_file_max_size'] = ((int)$this->config->get('config_file_max_size') * 1024 * 1024);

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
			'text' => $this->language->get('text_affiliate'),
			'href' => $this->url->link('account/affiliate', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['save'] = $this->url->link('account/affiliate.save', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$this->session->data['upload_token'] = oc_token(32);

		$data['upload'] = $this->url->link('tool/upload', 'language=' . $this->config->get('config_language') . '&upload_token=' . $this->session->data['upload_token']);

		// Alpha Engine: Affiliate Domain
		$affiliate_info = $this->getRepository(CustomerAffiliateRepository::class)->find($this->customer->getId());

		$data['company']             = $affiliate_info ? $affiliate_info->getCompany() : '';
		$data['website']             = $affiliate_info ? $affiliate_info->getWebsite() : '';
		$data['tax']                 = $affiliate_info ? $affiliate_info->getTax() : '';
		$data['payment_method']      = $affiliate_info ? $affiliate_info->getPaymentMethod() : 'cheque';
		$data['cheque']              = $affiliate_info ? $affiliate_info->getCheque() : '';
		$data['paypal']              = $affiliate_info ? $affiliate_info->getPaypal() : '';
		$data['bank_name']           = $affiliate_info ? $affiliate_info->getBankName() : '';
		$data['bank_branch_number']  = $affiliate_info ? $affiliate_info->getBankBranchNumber() : '';
		$data['bank_swift_code']     = $affiliate_info ? $affiliate_info->getBankSwiftCode() : '';
		$data['bank_account_name']   = $affiliate_info ? $affiliate_info->getBankAccountName() : '';
		$data['bank_account_number'] = $affiliate_info ? $affiliate_info->getBankAccountNumber() : '';

		// Custom Field
		$customFieldRepository = $this->getRepository(CustomFieldRepository::class);
		$custom_fields = $customFieldRepository->getCustomFields((int)$this->config->get('config_customer_group_id'));

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'affiliate') {
				$data['custom_fields'][] = $custom_field;
			}
		}

		$data['affiliate_custom_field'] = $affiliate_info ? $affiliate_info->getCustomFieldArray() : [];

		if (!$affiliate_info && $this->config->get('config_affiliate_id')) {
			// Information
			$informationRepository = $this->getRepository(InformationRepository::class);
			$information_info = $informationRepository->getInformation((int)$this->config->get('config_affiliate_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information.info', 'language=' . $this->config->get('config_language') . '&information_id=' . $this->config->get('config_affiliate_id')), $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		$data['back'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$data['language'] = $this->config->get('config_language');

		$this->render('account/affiliate', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('account/affiliate');

		$json = [];

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/affiliate', 'language=' . $this->config->get('config_language'));

			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$this->config->get('config_affiliate_status')) {
			$json['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'], true);
		}

		if (!$json) {
			$required = [
				'payment_method'      => '',
				'cheque'              => '',
				'paypal'              => '',
				'bank_account_name'   => '',
				'bank_account_number' => '',
				'agree'               => 0
			];

			$post_info = $this->request->post + $required;

			// Payment validation
			if (empty($post_info['payment_method'])) {
				$json['error']['payment_method'] = $this->language->get('error_payment_method');
			}

			if ($post_info['payment_method'] == 'cheque' && !$post_info['cheque']) {
				$json['error']['cheque'] = $this->language->get('error_cheque');
			} elseif ($post_info['payment_method'] == 'paypal' && ((oc_strlen($post_info['paypal']) > 96) || !filter_var($post_info['paypal'], FILTER_VALIDATE_EMAIL))) {
				$json['error']['paypal'] = $this->language->get('error_paypal');
			} elseif ($post_info['payment_method'] == 'bank') {
				if ($post_info['bank_account_name'] == '') {
					$json['error']['bank_account_name'] = $this->language->get('error_bank_account_name');
				}

				if ($post_info['bank_account_number'] == '') {
					$json['error']['bank_account_number'] = $this->language->get('error_bank_account_number');
				}
			}

			// Custom field validation
			$this->load->model('account/custom_field');

			$custom_fields = $this->model_account_custom_field->getCustomFields((int)$this->config->get('config_customer_group_id'));

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'affiliate') {
					if ($custom_field['required'] && empty($post_info['custom_field'][$custom_field['custom_field_id']])) {
						$json['error']['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
					} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !oc_validate_regex($post_info['custom_field'][$custom_field['custom_field_id']], $custom_field['validation'])) {
						$json['error']['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
					}
				}
			}

			// Validate agree only if customer not already an affiliate
			$affiliate_info = $this->getRepository(CustomerAffiliateRepository::class)->find($this->customer->getId());

			if (!$affiliate_info) {
				// Information
				$informationRepository = $this->getRepository(InformationRepository::class);
				$information_info = $informationRepository->getInformation((int)$this->config->get('config_affiliate_id'));

				if ($information_info && !$post_info['agree']) {
					$json['error']['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}
		}

		if (!$json) {
			$this->getRepository(CustomerAffiliateRepository::class)->processSave($this->customer->getId(), $post_info);

			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'], true);
		}

		$this->jsonResponse($json);
	}
}
