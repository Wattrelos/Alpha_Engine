<?php
namespace Opencart\Catalog\Model\Setting;

use Alpha\Model\Domain\Repositories\ExtensionRepository;

/**
 * Class Extension
 *
 * Can be called using $this->load->model('setting/extension');
 *
 * @package Opencart\Catalog\Model\Setting
 */
class Extension extends \Opencart\System\Engine\Model {
	/**
	 * Get Extensions
	 *
	 * Get the record of the extension records in the database.
	 *
	 * @return array<int, array<string, mixed>> extension records
	 *
	 * @example
	 *
	 * $this->load->model('setting/extension');
	 *
	 * $extensions = $this->model_setting_extension->getExtensions();
	 */
	public function getExtensions(): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(ExtensionRepository::class);
		return $repository->getDistinctExtensions();
	}

	/**
	 * Get Extensions By Type
	 *
	 * @param string $type
	 *
	 * @return array<int, array<string, mixed>>
	 *
	 * @example
	 *
	 * $this->load->model('setting/extension');
	 *
	 * $extensions = $this->model_setting_extension->getExtensionsByType($type);
	 */
	public function getExtensionsByType(string $type): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(ExtensionRepository::class);
		$entities = $repository->getExtensionsByType($type);
		
		$rows = [];
		foreach ($entities as $entity) {
			$rows[] = [
				'extension_id' => $entity->getId(),
				'extension'    => $entity->getExtension(),
				'type'         => $entity->getType(),
				'code'         => $entity->getCode()
			];
		}
		
		return $rows;
	}

	/**
	 * Get Extension By Code
	 *
	 * @param string $type
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/extension');
	 *
	 * $extension_info = $this->model_setting_extension->getExtensionByCode($type, $code);
	 */
	public function getExtensionByCode(string $type, string $code): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(ExtensionRepository::class);
		$entity = $repository->getExtensionByCode($type, $code);
		
		if ($entity) {
			return [
				'extension_id' => $entity->getId(),
				'extension'    => $entity->getExtension(),
				'type'         => $entity->getType(),
				'code'         => $entity->getCode()
			];
		}
		
		return [];
	}
}
