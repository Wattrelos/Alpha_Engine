<?php
namespace Opencart\Catalog\Model\Setting;

use Alpha\Model\Domain\Repositories\StartupRepository;

/**
 * Class Startup
 *
 * Can be called using $this->load->model('setting/startup');
 *
 * @package Opencart\Catalog\Model\Setting
 */
class Startup extends \Opencart\System\Engine\Model {
	/**
	 * Get Startups
	 *
	 * Get the record of the startup records in the database.
	 *
	 * @return array<int, array<string, mixed>> startup records
	 *
	 * @example
	 *
	 * $this->load->model('setting/startup');
	 *
	 * $startups = $this->model_setting_startup->getStartups();
	 */
	public function getStartups(): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(StartupRepository::class);
		return $repository->getStartups();
	}
}
