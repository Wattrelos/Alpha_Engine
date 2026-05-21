<?php
namespace Opencart\Catalog\Controller\Event;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\TranslationRepository;

/**
 * Class Translation
 *
 * @package Opencart\Catalog\Controller\Event
 */
class Translation extends BaseController {
	/**
	 * Index
	 *
	 * @param string $route
	 * @param string $prefix
	 *
	 * @return void
	 */
	public function index(string &$route, string &$prefix): void {
		/** @var TranslationRepository $translationRepository */
		$translationRepository = $this->getRepository(TranslationRepository::class);

		$results = $translationRepository->getTranslations($route);

		foreach ($results as $result) {
			if (!$prefix) {
				$this->language->set($result['key'], html_entity_decode($result['value'], ENT_QUOTES, 'UTF-8'));
			} else {
				$this->language->set($prefix . '_' . $result['key'], html_entity_decode($result['value'], ENT_QUOTES, 'UTF-8'));
			}
		}
	}
}
