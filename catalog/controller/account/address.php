<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

/**
 * Address Controller - Modernizado para Alpha Engine.
 */
class Address extends BaseController {

	private AddressRepository $addressRepository;

    public function __construct(\Opencart\System\Engine\Registry $registry) {
        parent::__construct($registry);
        $repositoryFactory = new RepositoryFactory($this->mapper, $registry);
        $this->addressRepository = $repositoryFactory->get(AddressRepository::class);
    }

	/**
	 * Index
	 */
	public function index(): void {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', 'language=' . $this->config->get('config_language'));
			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		// Alpha Engine: Uma única chamada ao Repositório para abastecer a View
		$data = $this->addressRepository->getIndexData($this->customer->getId(), $this->session->data['success'] ?? '')->toArray();
		
		unset($this->session->data['success']);

		$data['list'] = $this->getTemplate('account/address_list', $data);

		$this->render('account/address', $data);
	}

	/**
	 * List
	 */
	public function list(): void {
		if ($this->customer->isLogged()) {
			$data['addresses'] = $this->addressRepository->getFormattedAddresses($this->customer->getId());
			$this->response->setOutput($this->getTemplate('account/address_list', $data)); 
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
		
		// Alpha Engine: Uma única chamada ao Repositório para preparar o formulário
		$data = $this->addressRepository->getFormData($this->customer->getId(), $address_id)->toArray();

		$this->render('account/address_form', $data);
	}

	/**
	 * Save
	 */
	public function save(): void {
		$json = [];
		$this->addressRepository->loadLanguage('account/address');

		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('account/login', '', true);
		}

		if (!$json) {
			// Alpha Engine: Validação movida para o Repositório
			$errors = $this->addressRepository->validate($this->request->post);
			if ($errors) {
				$json['error'] = $errors;
			}
		}

		if (!$json) {
			$address_id = (int)($this->request->get['address_id'] ?? 0);

			if (!$address_id) {
				$this->addressRepository->addAddress($this->customer->getId(), $this->request->post);
				$this->session->data['success'] = $this->language->get('text_add');
			} else {
				$this->addressRepository->editAddress($this->customer->getId(), $address_id, $this->request->post);
				$this->session->data['success'] = $this->language->get('text_edit');
			}

			$json['redirect'] = $this->url->link('account/address', '', true);
		}

		$this->jsonResponse($json);
	}

	public function delete(): void {
		$this->addressRepository->loadLanguage('account/address');
		
		$json = [];
		$address_id = (int)($this->request->get['address_id'] ?? 0);

		// Alpha Engine: Regras de negócio de deleção no Repositório
		$errors = $this->addressRepository->validateDelete($this->customer->getId(), $address_id);
		if ($errors) {
			$json['error'] = $errors['warning'] ?? current($errors);
		}

		if (!$json) {
			$this->addressRepository->deleteAddress($this->customer->getId(), $address_id);
			$json['success'] = $this->language->get('text_delete');
		}

		$this->jsonResponse($json);
	}
}
