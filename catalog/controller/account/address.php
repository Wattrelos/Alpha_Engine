<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;
use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;

/**
 * Address Controller - Modernizado para Alpha Engine.
 */
class Address extends BaseController {

	/**
	 * Index
	 */
	public function index(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', 'language=' . $this->config->get('config_language'));
			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$data = [];
		$this->loadLanguageData('account/address', $data);
		
		$data['success'] = $this->session->data['success'] ?? '';
		unset($this->session->data['success']);
		
		$data['addresses'] = $this->getRepository(AddressRepository::class)->getAddresses((int)$this->customer->getId());
		
		$data['add'] = $this->url->link('account/address.form', 'language=' . $this->config->get('config_language'));
		$data['back'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language'));

		$data['list'] = $this->load->view('account/address_list', $data);

		$this->render('account/address', $data);
	}

	/**
	 * List
	 */
	public function list(): void {
		if ($this->customer->isLogged()) {
			$data['addresses'] = $this->getRepository(AddressRepository::class)->getAddresses((int)$this->customer->getId());
			$this->response->setOutput($this->load->view('account/address_list', $data)); 
		}
	}

	/**
	 * Form
	 */
	public function form(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', 'language=' . $this->config->get('config_language'));
			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$address_id = (int)($this->request->get['address_id'] ?? 0);
		
		$data = [];
		$this->loadLanguageData('account/address', $data);
		
		if ($address_id) {
			$address_info = $this->getRepository(AddressRepository::class)->getAddress($address_id);
			// Alpha Engine: Prevenção de IDOR na visualização do formulário
			if ($address_info && $address_info['customer_id'] == $this->customer->getId()) {
				$data['address'] = $address_info;
			} else {
				$data['address'] = [];
				$address_id = 0; // Reseta para evitar salvar em ID inválido
			}
		} else {
			$data['address'] = [];
		}
		
		$data['language'] = $this->config->get('config_language');
		
		// Countries
		$countryRepo = $this->getRepository(CountryRepository::class);
		$data['countries'] = $countryRepo->getCountries();
		
		// Custom Fields
		$customFieldRepo = $this->getRepository(CustomFieldRepository::class);
		$data['custom_fields'] = [];
		$custom_fields = $customFieldRepo->getCustomFields($this->customer->getGroupId());
		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'address') {
				$data['custom_fields'][] = $custom_field;
			}
		}
		
		$data['back'] = $this->url->link('account/address', 'language=' . $this->config->get('config_language'));
		$data['save'] = $this->url->link('account/address.save', 'language=' . $this->config->get('config_language') . ($address_id ? '&address_id=' . $address_id : ''));

		$this->render('account/address_form', $data);
	}

	/**
	 * Save
	 */
	public function save(): void {
		$json = [];
		$this->load->language('account/address');

		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			$errors = $this->getRepository(AddressRepository::class)->validate($this->request->post);
			if ($errors) {
				$json['error'] = $errors;
			}
		}

		if (!$json) {
			$address_id = (int)($this->request->get['address_id'] ?? 0);

			// Alpha Engine: Prevenção de IDOR antes de autorizar a persistência
			if ($address_id) {
				$address_info = $this->getRepository(AddressRepository::class)->getAddress($address_id);
				if (!$address_info || $address_info['customer_id'] != $this->customer->getId()) {
					$json['redirect'] = $this->url->link('account/address', 'language=' . $this->config->get('config_language'), true);
					$this->jsonResponse($json);
					return;
				}
			}

			$post_data = $this->request->post;
			if ($address_id) {
				$post_data['id'] = $address_id;
			}
			
			$this->getRepository(AddressRepository::class)->save($post_data, (int)$this->customer->getId());
			$this->session->data['success'] = $address_id ? $this->language->get('text_edit') : $this->language->get('text_add');

			// Alpha Engine: Invalidação dos métodos de envio/pagamento cacheados que dependem da geografia
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);

			$json['redirect'] = $this->url->link('account/address', 'language=' . $this->config->get('config_language'), true);
		}

		$this->jsonResponse($json);
	}

	public function delete(): void {
		$this->load->language('account/address');
		
		$json = [];
		$address_id = (int)($this->request->get['address_id'] ?? 0);

		$errors = $this->getRepository(AddressRepository::class)->validateDelete($this->customer->getId(), $address_id);
		if ($errors) {
			$json['error'] = $errors['warning'] ?? current($errors);
		}

		if (!$json) {
			$this->getRepository(AddressRepository::class)->delete($address_id, (int)$this->customer->getId());
			
			// Alpha Engine: Invalidação do checkout na deleção
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);

			$json['success'] = $this->language->get('text_delete');
		}

		$this->jsonResponse($json);
	}
}
