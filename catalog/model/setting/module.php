<?php
namespace Opencart\Catalog\Model\Setting;

use Alpha\Mappers\EntityMappers\ModuleMapper;
/**
 * Class Module
 *
 * Can be called using $this->load->model('setting/module');
 *
 * @package Opencart\Catalog\Model\Setting
 */
class Module extends \Opencart\System\Engine\Model {
	/**
	 * Get Module
	 *
	 * Get the record of the module record in the database.
	 *
	 * @param int $module_id primary key of the module record
	 *
	 * @return array<mixed> module record that has module ID
	 *
	 * @example
	 *
	 * $this->load->model('setting/module');
	 *
	 * $module_info = $this->model_setting_module->getModule($module_id);
	 */
	public function getModule(int $module_id): array {
		// $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `id` = '" . (int)$module_id . "'");
		$mapper = new ModuleMapper();
		$results = $mapper->getModule($module_id);

		if ($results) {
			return $results[0]['setting'] ? json_decode($results[0]['setting'], true) : [];
		}

		return [];
	}
}
