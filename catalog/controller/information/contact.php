<?php
namespace Opencart\Catalog\Controller\Information;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\InformationRepository;

/**
 * Class Contact
 * 
 * Controlador de contato refatorado seguindo os princípios da Alpha Engine.
 */
class Contact extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$this->loadLanguage('information/contact');

		$informationRepository = $this->repository->get(InformationRepository::class);
		
		// Alpha Engine: Coleta de dados via repositório para manter o controlador fino
		$contactData = $informationRepository->getContactPageData();
		
		$data = $contactData->toArray();

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		];

		$data['action'] = $this->url->link('information/contact.save', 'language=' . $this->config->get('config_language'));

		return $this->render('information/contact', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->loadLanguage('information/contact');

		$json = [];

		$informationRepository = $this->repository->get(InformationRepository::class);

		// Alpha Engine: Validação centralizada no domínio
		$errors = $informationRepository->validateContactForm($this->request->post);

		if ($errors) {
			$json['error'] = $errors;
		}

		if (!$json) {
			// Alpha Engine: Delegação do processamento de envio para o Repository
			$informationRepository->sendEnquiry($this->request->post);
			
			$json['success'] = $this->language->get('text_success');
		}

		$this->jsonResponse($json);
	}
}