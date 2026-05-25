<?php
namespace Opencart\Catalog\Controller\Startup;

use Alpha\Model\Domain\Repositories\ExtensionRepository;

/**
 * Class Extension
 *
 * @package Opencart\Catalog\Controller\Startup
 */
class Extension extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		/** @var ExtensionRepository $extensionRepo */
		$extensionRepo = $this->registry->get('alpha_repository_factory')->get(ExtensionRepository::class);
		$extensions = $extensionRepo->findAll();

		foreach ($extensions as $result) {
			$extensionCode = $result->getExtension();
			$extensionClass = str_replace(['_', '/'], ['', '\\'], ucwords($extensionCode, '_/'));

			// Register controllers, models and system extension folders
			$this->autoloader->register('Opencart\Catalog\Controller\Extension\\' . $extensionClass, DIR_EXTENSION . $extensionCode . '/catalog/controller/');
			$this->autoloader->register('Opencart\Catalog\Model\Extension\\' . $extensionClass, DIR_EXTENSION . $extensionCode . '/catalog/model/');
			$this->autoloader->register('Opencart\System\Library\Extension\\' . $extensionClass, DIR_EXTENSION . $extensionCode . '/system/library/');

			// Template directory
			$this->template->addPath('extension/' . $extensionCode, DIR_EXTENSION . $extensionCode . '/catalog/view/template/');

			// Language directory
			$this->language->addPath('extension/' . $extensionCode, DIR_EXTENSION . $extensionCode . '/catalog/language/');

			// Config directory
			$this->config->addPath('extension/' . $extensionCode, DIR_EXTENSION . $extensionCode . '/system/config/');
		}

		// Register OCMOD
		$this->autoloader->register('Opencart\Catalog\Controller\Extension\Ocmod', DIR_EXTENSION . 'ocmod/catalog/controller/');
		$this->autoloader->register('Opencart\Catalog\Model\Extension\Ocmod', DIR_EXTENSION . 'ocmod/catalog/model/');
		$this->autoloader->register('Opencart\System\Library\Extension\Ocmod', DIR_EXTENSION . 'ocmod/system/library/');
		$this->template->addPath('extension/ocmod', DIR_EXTENSION . 'ocmod/catalog/view/template/');
		$this->language->addPath('extension/ocmod', DIR_EXTENSION . 'ocmod/catalog/language/');
		$this->config->addPath('extension/ocmod', DIR_EXTENSION . 'ocmod/system/config/');
	}
}
