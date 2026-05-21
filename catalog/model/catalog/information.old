<?php
namespace Opencart\Catalog\Model\Catalog;

use Alpha\Mappers\EntityMappers\InformationMapper;

/**
 * Class Information
 *
 * Can be called using $this->load->model('catalog/information');
 *
 * @package Opencart\Catalog\Model\Catalog
 */
class Information extends \Opencart\System\Engine\Model {
	/**
	 * Get Information
	 *
	 * Get the record of the information record in the database.
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return array<string, mixed> information record that have information ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/information');
	 *
	 * $information_info = $this->model_catalog_information->getInformation($information_id);
	 */
	public function getInformation(int $information_id): array {
		$mapper = new InformationMapper();

		return $mapper->getInformation(
			$information_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Get Information(s)
	 *
	 * Get the record of the information records in the database.
	 *
	 * @return array<int, array<string, mixed>> information records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/information');
	 *
	 * $results = $this->model_catalog_information->getInformations();
	 */
	public function getInformations(): array {
		$mapper = new InformationMapper();

		return $mapper->getInformations(
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Get Layout ID
	 *
	 * Get the record of the information layout record in the database.
	 *
	 * @param int $information_id primary key of the information record
	 *
	 * @return int layout record that has information ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/information');
	 *
	 * $layout_id = $this->model_catalog_information->getLayoutId($information_id);
	 */
	public function getLayoutId(int $information_id): int {
		$mapper = new InformationMapper();

		return $mapper->getLayoutId(
			$information_id,
			(int)$this->config->get('config_store_id')
		);
	}
}
