<?php
namespace Opencart\Catalog\Controller\Event;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\LanguageRepository;

/**
 * Class Language
 *
 * @package Opencart\Catalog\Controller\Event
 */
class Language extends BaseController {
	/**
	 * Index
	 *
	 * @param string $route
	 * @param array $args
	 * @param mixed $output
	 *
	 * @return void
	 */
	public function index(string &$route, array &$args, mixed &$output): void {
		$languageRepository = $this->getRepository(LanguageRepository::class);
		$languages = $languageRepository->getLanguages();

		if (isset($this->request->post['language_code'])) {
			$language_code = (string)$this->request->post['language_code'];
		} elseif (isset($this->session->data['language'])) {
			$language_code = $this->session->data['language'];
		} elseif (isset($this->request->cookie['language'])) {
			$language_code = $this->request->cookie['language'];
		} else {
			$language_code = $this->config->get('config_language');
		}

		if (isset($languages[$language_code])) {
			$lang = $languages[$language_code];
			
			// Fallback de retrocompatibilidade: Suporta instâncias da Alpha Engine (Entities) e arrays (Modernos ou Legados)
			$langId = is_object($lang) && method_exists($lang, 'getId') ? $lang->getId() : ($lang['id'] ?? $lang['language_id'] ?? null);
			if ($langId) {
				$this->config->set('config_language_id', (int)$langId);
			}
		}

		$language = new \Opencart\System\Library\Language($language_code);
		$language->addPath(DIR_LANGUAGE);
		$language->load($language_code);
		$this->registry->set('language', $language);

		$this->config->set('config_language', $language_code);
	}
}