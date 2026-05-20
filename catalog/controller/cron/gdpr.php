<?php
namespace Opencart\Catalog\Controller\Cron;

use Alpha\Mappers\EntityMappers\GdprMapper;
use Alpha\Mappers\EntityMappers\CustomerMapper;
use Alpha\Model\DataAccessObject\UnitOfWork;

/**
 * Class Gdpr
 *
 * @package Opencart\Catalog\Controller\Cron
 */
class Gdpr extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param int    $cron_id
	 * @param string $code
	 * @param string $cycle
	 * @param string $date_added
	 * @param string $date_modified
	 *
	 * @return void
	 */
	public function index(int $cron_id, string $code, string $cycle, string $date_added, string $date_modified): void {
		// GDPR
		// Alpha Engine: Instancia o UnitOfWork e os Mappers
		$unitOfWork = new UnitOfWork();
		$gdprMapper = new GdprMapper();
		$customerMapper = new CustomerMapper();

		// Busca as solicitações de exclusão expiradas
		$results = $gdprMapper->getExpires();

		foreach ($results as $result) {
			try {
				$unitOfWork->begin();

				// 1. Atualiza o status da solicitação para "Processado/Removido" (ID 3)
				$gdprMapper->editStatus((int)$result['gdpr_id'], 3);

				// 2. Busca e remove o cliente associado
				$customer_info = $customerMapper->getCustomerByEmail((string)$result['email']);

				if ($customer_info) {
					$customerMapper->deleteCustomer((int)$customer_info['customer_id']);
				}

				$unitOfWork->commit();
			} catch (\Exception $e) {
				$unitOfWork->rollback();
				// Alpha Engine: O erro pode ser logado aqui via LogMapper no futuro
			}
		}
	}
}
