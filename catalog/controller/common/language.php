<?php
namespace Opencart\Catalog\Controller\Common;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\LanguageRepository;
/**
 * Class Language
 *
 * Can be called from $this->load->controller('common/language');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Language extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$languageRepository = $this->getRepository(LanguageRepository::class);
		$languageData = $languageRepository->getLanguageDisplayData();
		
		$data = $languageData->toArray();
		// Alpha Engine: Redirect URL agora é o único parâmetro contextual extra
		$data['redirect'] = $languageRepository->getRedirectUrl($this->request->get);

		return $this->load->view('common/language', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$json = [];

		$required = [
			'code'     => (string)($this->request->post['code'] ?? ''),
			'redirect' => ''
		];

		$post_info = $this->request->post + $required;

		$languageRepository = $this->getRepository(LanguageRepository::class);
		
		if (!$languageRepository->isValid($post_info['code'])) {
			$json['error'] = $this->language->get('error_language');
		}

		if (!$json) {
			// Alpha Engine: Delegação de lógica de negócio para o Repository
			$languageRepository->clearLanguageContext();
			
			$json['redirect'] = $languageRepository->processSaveRedirect($post_info['redirect'], $post_info['code']);
		}

		$this->jsonResponse($json);
	}
}
