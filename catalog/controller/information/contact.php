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
	 * @return ?\Opencart\System\Engine\Action
	 */
	public function index(): ?\Opencart\System\Engine\Action {
		$data = [];
		
		// Alpha Engine: Carregamento unificado das traduções (Corrige Fatal Error)
		$this->loadLanguageData('information/contact', $data);

		// Alpha Engine: Padronização da Injeção de Repositório
		$informationRepository = $this->getRepository(InformationRepository::class);
		
		// Alpha Engine: Coleta de dados via repositório para manter o controlador fino
		$contactData = $informationRepository->getContactPageData();
		
		$data = array_merge($data, $contactData->toArray());

		// Alpha Engine: Injeção de Meta Tags (SEO) faltantes
		$this->document->setTitle($data['heading_title'] ?? $this->language->get('heading_title'));

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact', 'language=' . $this->config->get('config_language'))
		];

		$data['action'] = $this->url->link('information/contact.save', 'language=' . $this->config->get('config_language'));

		$this->render('information/contact', $data);
		
		return null;
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('information/contact'); // Callback nativo sem injeção automática em array

		$json = [];

		$informationRepository = $this->getRepository(InformationRepository::class);

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