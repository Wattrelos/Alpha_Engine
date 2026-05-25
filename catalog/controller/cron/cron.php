<?php
namespace Opencart\Catalog\Controller\Cron;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CronRepository;

/**
 * Class Cron
 *
 * @package Opencart\Catalog\Controller\Cron
 */
class Cron extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$time = time();

		$cronRepository = $this->getRepository(CronRepository::class);
		$results = $cronRepository->getCrons();

		$this->load->controller('cron/subscription', 3, 'subscription', 1, '', '');

		foreach ($results as $result) {
			if ($result['status'] && (strtotime('+1 ' . $result['cycle'], strtotime($result['date_modified'])) < ($time + 10))) {
				$this->load->controller($result['action'], $result['cron_id'], $result['code'], $result['cycle'], $result['date_added'], $result['date_modified']);

				$cronRepository->editCron($result['cron_id']);
			}
		}
	}
}
