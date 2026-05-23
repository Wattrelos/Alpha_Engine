<?php
namespace Opencart\Catalog\Controller\Information;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\InformationRepository;

/**
 * Class Information
 *
 * @package Opencart\Catalog\Controller\Information
 * 
 * Refatorado para Alpha Engine: Skinny Controller, consome o Repository e utiliza BaseController.
 */
class Information extends BaseController {
	/**
	 * Index
	 *
	 * @return ?\Opencart\System\Engine\Action
	 */
	public function index(): ?\Opencart\System\Engine\Action {
		$information_id = (int)($this->request->get['information_id'] ?? 0);

		/** @var InformationRepository $repository */
		$repository = $this->getRepository(InformationRepository::class);
		
		// Alpha Engine: Repositório orquestra o DTO completo, incluindo Breadcrumbs e Segurança.
		$response = $repository->getInformationDisplayData($information_id);

		if ($response) {
			$data = $response->getData();
			
			$this->loadLanguageData('information/information', $data); // Injeta traduções estáticas

			// Assets e SEO
			$this->document->setTitle($data['meta_title'] ?: $data['title']);
			$this->document->setDescription($data['meta_description']);
			$this->document->setKeywords($data['meta_keyword']);
			
			$data['heading_title'] = $data['title'];

			// Alpha Engine: render() resolve layout, módulos e views nativamente
			$this->render('information/information', $data);
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}

		return null;
	}

	/**
	 * Info
	 *
	 * @return void
	 */
	public function info(): void {
		$information_id = (int)($this->request->get['information_id'] ?? 0);

		/** @var InformationRepository $repository */
		$repository = $this->getRepository(InformationRepository::class);
		
		// Utiliza a mesma lógica de DTO para a view isolada (info)
		$response = $repository->getInformationDisplayData($information_id);

		if ($response) {
			$data = $response->getData();
				
			$this->response->addHeader('X-Robots-Tag: noindex');
			$this->response->setOutput($this->load->view('information/information_info', $data));
		}
	}
}
