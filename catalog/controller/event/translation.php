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
	 * @return void
	 */
	public function index(string &$route, string &$prefix, string &$code): void {
		$translationRepository = $this->getRepository(TranslationRepository::class);
		$results = $translationRepository->getTranslations($route);
		foreach ($results as $result) {
			$this->language->set($result['key'], $result['value']);
		}
	}
}