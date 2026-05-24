<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;

/**
 * Edit Controller - Modernizado para Alpha Engine.
 */
class Edit extends BaseController {
	private CustomerRepository $customerRepository;
	private CustomFieldRepository $customFieldRepository;

	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
		$this->customerRepository = $this->registry->get('alpha_repository_factory')->get(CustomerRepository::class);
		$this->customFieldRepository = $this->registry->get('alpha_repository_factory')->get(CustomFieldRepository::class);
	}

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->load->language('account/edit');
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
			'text' => $this->language->get('text_edit'),
			'href' => $this->url->link('account/edit', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), $this->config->get('config_file_max_size'));

		$data['config_file_max_size'] = ((int)$this->config->get('config_file_max_size') * 1024 * 1024);
		$data['config_telephone_display'] = $this->config->get('config_telephone_display');
		$data['config_telephone_required'] = $this->config->get('config_telephone_required');

		$data['save'] = $this->url->link('account/edit.save', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$this->session->data['upload_token'] = oc_token(32);

		$data['upload'] = $this->url->link('tool/upload', 'language=' . $this->config->get('config_language') . '&upload_token=' . $this->session->data['upload_token']);

		// Alpha Engine: Entidade Cliente do Domínio
		$customer = $this->customerRepository->find($this->customer->getId());

		$data['firstname'] = $customer ? $customer->getFirstname() : $this->customer->getFirstName();
		$data['lastname'] = $customer ? $customer->getLastname() : $this->customer->getLastName();
		$data['email'] = $customer ? $customer->getEmail() : $this->customer->getEmail();
		$data['telephone'] = $customer ? $customer->getTelephone() : $this->customer->getTelephone();
		$data['cpf_cnpj'] = $customer ? $customer->getCpfCnpj() : '';
		$data['persontype'] = $customer ? $customer->getPersontype() : 'F';

		// Custom Fields
		$data['custom_fields'] = [];

		$custom_fields = $this->customFieldRepository->getCustomFields($this->customer->getGroupId());

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'account') {
				$data['custom_fields'][] = $custom_field;
			}
		}

		$data['account_custom_field'] = $customer ? $customer->getCustomFieldArray() : ($this->session->data['customer']['custom_field'] ?? []);

		$data['back'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$data['language'] = $this->config->get('config_language');

		// Alpha Engine: Renderização Otimizada via BaseController
		$this->render('account/edit', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('account/edit');

		$json = [];

		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			$required = [
				'firstname' => '',
				'lastname'  => '',
				'email'     => '',
				'telephone' => '',
				'cpf_cnpj'  => '',
				'persontype'=> ''
			];
			$post_info = $this->request->post + $required;

			// Alpha Engine: Validações injetadas pelo Repositório
			$errors = $this->customerRepository->validateEditData($post_info, $this->customer->getId());
			
			// Custom field validation
			$custom_fields = $this->customFieldRepository->getCustomFields($this->customer->getGroupId());

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'account') {
					if ($custom_field['required'] && empty($post_info['custom_field'][$custom_field['custom_field_id']])) {
						$errors['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
					} elseif ($custom_field['type'] == 'text' && !empty($custom_field['validation']) && !oc_validate_regex($post_info['custom_field'][$custom_field['custom_field_id']], $custom_field['validation'])) {
						$errors['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
					}
				}
			}
			
			if ($errors) {
				$json['error'] = $errors;
			}
		}

		if (!$json) {
			// Update customer in db via Domain
			$customer = $this->customerRepository->find($this->customer->getId());
			
			if ($customer) {
				$customer->setFirstname($post_info['firstname'])
						 ->setLastname($post_info['lastname'])
						 ->setEmail($post_info['email'])
						 ->setTelephone($post_info['telephone'])
						 ->setCpfCnpj($post_info['cpf_cnpj'])
						 ->setPersontype($post_info['persontype']);
				
				if (isset($post_info['custom_field'])) {
					$customer->setCustomFieldArray($post_info['custom_field']);
				}
				
				$this->customerRepository->updateProfile($customer);
			}

			$json['success'] = $this->language->get('text_success');

			// Update customer session details
			$this->session->data['customer'] = [
				'customer_id'       => $this->customer->getId(),
				'customer_group_id' => $this->customer->getGroupId(),
				'firstname'         => $post_info['firstname'],
				'lastname'          => $post_info['lastname'],
				'email'             => $post_info['email'],
				'telephone'         => $post_info['telephone'],
				'cpf_cnpj'          => $post_info['cpf_cnpj'],
				'persontype'        => $post_info['persontype'],
				'custom_field'      => $post_info['custom_field'] ?? []
			];

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->jsonResponse($json);
	}
}
