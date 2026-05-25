<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\InformationRepository;
use Alpha\Model\Domain\Repositories\CustomerGroupRepository;
use Alpha\Model\Domain\Repositories\ExtensionRepository;

/**
 * Class Register
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Register extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$data = [];
		$this->loadLanguageData('account/register', $data);
		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'], true));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_register'),
			'href' => $this->url->link('account/register', 'language=' . $this->config->get('config_language'))
		];

		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')));

		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), $this->config->get('config_file_max_size'));

		$data['config_file_max_size'] = ((int)$this->config->get('config_file_max_size') * 1024 * 1024);
		$data['config_telephone_display'] = $this->config->get('config_telephone_display');
		$data['config_telephone_required'] = $this->config->get('config_telephone_required');

		// Create form token
		$this->session->data['register_token'] = oc_token(26);

		$data['register'] = $this->url->link('account/register.register', 'language=' . $this->config->get('config_language') . '&register_token=' . $this->session->data['register_token']);

		$this->session->data['upload_token'] = oc_token(32);

		$data['upload'] = $this->url->link('tool/upload', 'language=' . $this->config->get('config_language') . '&upload_token=' . $this->session->data['upload_token']);

		// Customer Groups
		$data['customer_groups'] = [];

		if (is_array($this->config->get('config_customer_group_display'))) {
			$customerGroupRepository = $this->getRepository(CustomerGroupRepository::class);
			$customer_groups = $customerGroupRepository->getCustomerGroups((int)$this->config->get('config_language_id'));

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['id'], (array)$this->config->get('config_customer_group_display'))) {
					$customer_group['customer_group_id'] = $customer_group['id']; // Interoperabilidade DTO
					$data['customer_groups'][] = $customer_group;
				}
			}
		}

		$data['customer_group_id'] = (int)$this->config->get('config_customer_group_id');

		// Custom Fields
		$data['custom_fields'] = [];

		$customFieldRepository = $this->getRepository(CustomFieldRepository::class);
		$custom_fields = $customFieldRepository->getCustomFields($data['customer_group_id']);

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'account') {
				$data['custom_fields'][] = $custom_field;
			}
		}

		// Captcha
		$extension_info = null;
		$extensionRepository = $this->getRepository(ExtensionRepository::class);
		$extensions = $extensionRepository->findAll();
		foreach ($extensions as $ext) {
			$ext_type = is_object($ext) ? $ext->getType() : ($ext['type'] ?? '');
			$ext_code = is_object($ext) ? $ext->getCode() : ($ext['code'] ?? '');
			
			if ($ext_type === 'captcha' && $ext_code === $this->config->get('config_captcha')) {
				$extension_info = [
					'extension' => is_object($ext) ? $ext->getExtension() : ($ext['extension'] ?? ''),
					'code'      => $ext_code
				];
				break;
			}
		}

		if ($extension_info && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/' . $extension_info['extension'] . '/captcha/' . $extension_info['code']);
		} else {
			$data['captcha'] = '';
		}

		// Information
		$informationRepository = $this->getRepository(InformationRepository::class);
		$information_info = $informationRepository->getInformation((int)$this->config->get('config_account_id'));

		if ($information_info) {
			$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information.info', 'language=' . $this->config->get('config_language') . '&information_id=' . $this->config->get('config_account_id')), $information_info['title']);
		} else {
			$data['text_agree'] = '';
		}

		$data['language'] = $this->config->get('config_language');

		// Alpha Engine: Renderização envelopada
		$this->render('account/register', $data);
	}

	/**
	 * Register
	 *
	 * @return void
	 */
	public function register(): void {
		$data = [];
		$this->loadLanguageData('account/register', $data);

		$json = [];

		$required = [
			'customer_group_id' => 0,
			'firstname'         => '',
			'lastname'          => '',
			'email'             => '',
			'telephone'         => '',
			'cpf_cnpj'          => '',
			'persontype'        => '',
			'custom_field'      => [],
			'password'          => '',
			'agree'             => 0
		];

		$post_info = $this->request->post + $required;

		if (!isset($this->request->get['register_token']) || !isset($this->session->data['register_token']) || ($this->session->data['register_token'] != $this->request->get['register_token'])) {
			$json['redirect'] = $this->url->link('account/register', 'language=' . $this->config->get('config_language'), true);
		}

		// Captcha first to prevent probing for registered emails
		$extension_info = null;
		$extensionRepository = $this->getRepository(ExtensionRepository::class);
		$extensions = $extensionRepository->findAll();
		foreach ($extensions as $ext) {
			$ext_type = is_object($ext) ? $ext->getType() : ($ext['type'] ?? '');
			$ext_code = is_object($ext) ? $ext->getCode() : ($ext['code'] ?? '');
			
			if ($ext_type === 'captcha' && $ext_code === $this->config->get('config_captcha')) {
				$extension_info = [
					'extension' => is_object($ext) ? $ext->getExtension() : ($ext['extension'] ?? ''),
					'code'      => $ext_code
				];
				break;
			}
		}

		if ($extension_info && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/' . $extension_info['extension'] . '/captcha/' . $extension_info['code'] . '.validate');

			if ($captcha) {
				$json['error']['captcha'] = $captcha;
			}
		}

		if (!$json) {
			$customerRepository = $this->getRepository(CustomerRepository::class);
			
			// Alpha Engine: Encapsulamento total do registro via Domínio (Skinny Controller)
			$result = $customerRepository->registerCustomer($post_info);

			if (!empty($result['errors'])) {
				$json['error'] = $result['errors'];
			} else {
				$customer_group_info = $result['customer_group_info'];
				$customer_id = $result['customer_id'];

				// Login if requires approval
				if (!$customer_group_info['approval']) {
					$this->customer->login($post_info['email'], $post_info['password']);

					// Add customer details into session
					$this->session->data['customer'] = [
						'customer_id'       => $customer_id,
						'customer_group_id' => $result['customer_group_id'],
						'firstname'         => $post_info['firstname'],
						'lastname'          => $post_info['lastname'],
						'email'             => $post_info['email'],
						'telephone'         => $post_info['telephone'],
						'cpf_cnpj'          => $post_info['cpf_cnpj'],
						'persontype'        => $post_info['persontype'],
						'custom_field'      => $post_info['custom_field']
					];

					// Create customer token
					$this->session->data['customer_token'] = oc_token(26);
				}

				// Remove form token
				unset($this->session->data['register_token']);

				// Clear any previous login attempts for unregistered accounts.
				$customerRepository->resetLoginAttempts($post_info['email']);

				// Clear old session data
				unset($this->session->data['guest']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				$json['redirect'] = $this->url->link('account/success', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''), true);
			}
		}

		$this->jsonResponse($json);
	}
}
