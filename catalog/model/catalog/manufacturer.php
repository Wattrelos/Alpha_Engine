<?php
namespace Opencart\Catalog\Model\Catalog;

use Alpha\Mappers\ManufacturerMapper;

/**
 * Class Manufacturer
 *
 * Can be called using $this->load->model('catalog/manufacturer');
 *
 * @package Opencart\Catalog\Model\Catalog
 */
class Manufacturer extends \Opencart\System\Engine\Model {
	/**
	 * Get Manufacturer
	 *
	 * Get the record of the manufacturer record in the database.
	 *
	 * @param int $manufacturer_id primary key of the manufacturer record
	 *
	 * @return array<string, mixed> manufacturer record that has manufacturer ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);
	 */
	public function getManufacturer(int $manufacturer_id): array {
		$mapper = new ManufacturerMapper();
		$row = $mapper->getManufacturer(
			$manufacturer_id,
			(int)$this->config->get('config_store_id')
		);

		// Bridge: Injeta manufacturer_id para compatibilidade legada
		return $row ? ['manufacturer_id' => $row['id']] + $row : [];
	}

	/**
	 * Get Manufacturer(s)
	 *
	 * Get the record of the manufacturer records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> manufacturer records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $results = $this->model_catalog_manufacturer->getManufacturers();
	 */
	public function getManufacturers(array $data = []): array {
		$mapper = new ManufacturerMapper();
		$results = $mapper->getManufacturers(
			$data,
			(int)$this->config->get('config_store_id')
		);

		// Bridge: Injeta manufacturer_id para compatibilidade legada
		return array_map(fn($item) => ['manufacturer_id' => $item['id']] + $item, $results);
	}

	/**
	 * Get Layout ID
	 *
	 * Get the record of the manufacturer layout record in the database.
	 *
	 * @param int $manufacturer_id primary key of the manufacturer record
	 *
	 * @return int layout record that has manufacturer ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/manufacturer');
	 *
	 * $layout_id = $this->model_catalog_manufacturer->getLayoutId($manufacturer_id);
	 */
	public function getLayoutId(int $manufacturer_id): int {
		$mapper = new ManufacturerMapper();

		return $mapper->getLayoutId(
			$manufacturer_id,
			(int)$this->config->get('config_store_id')
		);
	}
}
