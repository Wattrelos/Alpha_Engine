<?php
namespace Opencart\Catalog\Model\Catalog;

use Alpha\Mappers\CategoryMapper;
use Alpha\Mappers\CollectionToArrayConverter;

/**
 * Class Category
 *
 * Can be called using $this->load->model('catalog/category');
 *
 * @package Opencart\Catalog\Model\Catalog
 */
class Category extends \Opencart\System\Engine\Model {
	/**
	 * Get Category
	 *
	 * Get the record of the category record in the database.
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<string, mixed> category record that has category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $category_info = $this->model_catalog_category->getCategory($category_id);
	 */
	public function getCategory(int $category_id): array {
		$mapper = new CategoryMapper();

		$category = $mapper->getCategory(
			$category_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);

		return $category ? CollectionToArrayConverter::convertEntity($category) : [];
	}

	/**
	 * Get Categories
	 *
	 * Get the record of the category records in the database.
	 *
	 * @param int $parent_id primary key of the parent category record
	 *
	 * @return array<int, array<string, mixed>> category records that have parent ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $categories = $this->model_catalog_category->getCategories();
	 */
	public function getCategories(int $parent_id = 0): array {
		$mapper = new CategoryMapper();

		$results = $mapper->getSubCategories(
			$parent_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);

		// Bridge: Injeta category_id para compatibilidade com controladores legados
		return array_map(fn($item) => ['category_id' => $item['id']] + $item, $results);
	}
/*****************************************************************************************************************************************************************/
/* Personalizado */
/*****************************************************************************************************************************************************************/		
	public function getAllCategories(): array {
		$mapper = new CategoryMapper();

		$results = $mapper->getAllCategories(
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);

		// Bridge: Injeta category_id para compatibilidade com controladores legados
		return array_map(fn($item) => ['category_id' => $item['id']] + $item, $results);
	}
/*****************************************************************************************************************************************************************/

	/**
	 * Get Filters
	 *
	 * Get the record of the category filter records in the database.
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<int, array<string, mixed>> filter records that have category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $results = $this->model_catalog_category->getFilters($category_id);
	 */
	public function getFilters(int $category_id): array {
		$mapper = new CategoryMapper();
		$language_id = (int)$this->config->get('config_language_id');
		
		$filter_groups = $mapper->getCategoryFilters($category_id, $language_id);
		$filter_group_data = [];

		foreach ($filter_groups as $filter_group) {
			$filter_query = $this->db->query("SELECT DISTINCT `f`.`filter_id`, `fd`.`name` FROM `" . DB_PREFIX . "filter` `f` LEFT JOIN `" . DB_PREFIX . "filter_description` `fd` ON (`f`.`filter_id` = `fd`.`filter_id`) WHERE `f`.`filter_group_id` = '" . (int)$filter_group['id'] . "' AND `fd`.`language_id` = '" . $language_id . "' ORDER BY `f`.`sort_order`, LCASE(`fd`.`name`)");

			if ($filter_query->num_rows) {
				// Bridge: Injeta filter_group_id para compatibilidade
				$filter_group_data[] = ['filter' => $filter_query->rows, 'filter_group_id' => $filter_group['id']] + $filter_group;
			}
		}

		return $filter_group_data;
	}

	/**
	 * Get Layout ID
	 *
	 * Get the record of the category layout record in the database.
	 *
	 * @param int $category_id primary key of the category record
	 *
	 * @return int layout record that has category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/category');
	 *
	 * $layout_id = $this->model_catalog_category->getLayoutId($category_id);
	 */
	public function getLayoutId(int $category_id): int {
		$mapper = new CategoryMapper();

		return $mapper->getLayoutId(
			$category_id,
			(int)$this->config->get('config_store_id')
		);
	}
}
