<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\MaintenanceRepository;

/**
 * Class Maintenance
 *
 * Can be called from $this->load->controller('common/maintenance');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Maintenance extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$maintenanceRepository = $this->repository->get(MaintenanceRepository::class);
		$maintenanceData = $maintenanceRepository->getMaintenanceData();

		$this->document->setTitle($maintenanceData->getTitle());

		if ($this->request->server['SERVER_PROTOCOL'] == 'HTTP/1.1') {
			$this->response->addHeader('HTTP/1.1 503 Service Unavailable');
		} else {
			$this->response->addHeader('HTTP/1.0 503 Service Unavailable');
		}

		$this->response->addHeader('Retry-After: 3600');

		// Alpha Engine: render() já lida com Header/Footer automáticos e setOutput()
		$this->render('common/maintenance', $maintenanceData->toArray());
	}
}
