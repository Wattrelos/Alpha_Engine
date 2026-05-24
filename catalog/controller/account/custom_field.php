<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;

/**
 * Class Custom Field
 *
 * @package Opencart\Catalog\Controller\Account
 */
class CustomField extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		// Customer Group
		if (isset($this->request->get['customer_group_id']) && in_array((int)$this->request->get['customer_group_id'], (array)$this->config->get('config_customer_group_display'))) {
			$customer_group_id = (int)$this->request->get['customer_group_id'];
		} else {
			$customer_group_id = (int)$this->config->get('config_customer_group_id');
		}

		/** @var CustomFieldRepository $repository */
		$repository = $this->getRepository(CustomFieldRepository::class);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($repository->getCustomFields($customer_group_id)));
	}
}
