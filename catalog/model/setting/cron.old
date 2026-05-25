<?php
namespace Opencart\Catalog\Model\Setting;

use Alpha\Model\Domain\Repositories\CronRepository;

/**
 * Class Cron
 *
 * Can be called using $this->load->model('setting/cron');
 *
 * @package Opencart\Catalog\Model\Setting
 */
class Cron extends \Opencart\System\Engine\Model {
	/**
	 * Edit Cron
	 *
	 * Edit cron record in the database.
	 *
	 * @param int $cron_id primary key of the cron record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/cron');
	 *
	 * $this->model_setting_cron->editCron($cron_id);
	 */
	public function editCron(int $cron_id): void {
		$repository = $this->registry->get('alpha_repository_factory')->get(CronRepository::class);
		$repository->editCron($cron_id);
	}

	/**
	 * Edit Status
	 *
	 * Edit cron status record in the database.
	 *
	 * @param int  $cron_id primary key of the cron record
	 * @param bool $status
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/cron');
	 *
	 * $this->model_setting_cron->editStatus($cron_id, $status);
	 */
	public function editStatus(int $cron_id, bool $status): void {
		$repository = $this->registry->get('alpha_repository_factory')->get(CronRepository::class);
		$repository->editStatus($cron_id, $status);
	}

	/**
	 * Get Cron
	 *
	 * Get the record of the cron record in the database.
	 *
	 * @param int $cron_id primary key of the cron record
	 *
	 * @return array<string, mixed> cron record that has cron ID
	 *
	 * @example
	 *
	 * $this->load->model('setting/cron');
	 *
	 * $cron_info = $this->model_setting_cron->getCron($cron_id);
	 */
	public function getCron(int $cron_id): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(CronRepository::class);
		return $repository->getCron($cron_id);
	}

	/**
	 * Get Cron By Code
	 *
	 * @param string $code
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/cron');
	 *
	 * $cron_info = $this->model_setting_cron->getCronByCode($code);
	 */
	public function getCronByCode(string $code): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(CronRepository::class);
		return $repository->getCronByCode($code);
	}

	/**
	 * Get Cron(s)
	 *
	 * Get the record of the cron records in the database.
	 *
	 * @return array<int, array<string, mixed>> cron records
	 *
	 * @example
	 *
	 * $this->load->model('setting/cron');
	 *
	 * $results = $this->model_setting_cron->getCrons();
	 */
	public function getCrons(): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(CronRepository::class);
		return $repository->getCrons();
	}

	/**
	 * Get Total Cron(s)
	 *
	 * Get the total number of total cron records in the database.
	 *
	 * @return int total number of cron records
	 *
	 * @example
	 *
	 * $this->load->model('setting/cron');
	 *
	 * $cron_total = $this->model_setting_cron->getTotalCrons();
	 */
	public function getTotalCrons(): int {
		$repository = $this->registry->get('alpha_repository_factory')->get(CronRepository::class);
		return $repository->getTotalCrons();
	}
}
