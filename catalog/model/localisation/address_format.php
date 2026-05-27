<?php
namespace Opencart\Catalog\Model\Localisation;

use Alpha\Model\Domain\Repositories\AddressFormatRepository;

/**
 * Class Address Format
 *
 * Can be called using $this->load->model('localisation/address_format');
 *
 * @package Opencart\Admin\Model\Localisation
 */
class AddressFormat extends \Opencart\System\Engine\Model {
	/**
	 * Get Address Format
	 *
	 * Get the record of the address format record in the database.
	 *
	 * @param int $address_format_id primary key of the address format record
	 *
	 * @return array<string, mixed> address format record that has address format ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/address_format');
	 *
	 * $address_format_info = $this->model_localisation_address_format->getAddressFormat($address_format_id);
	 */
	public function getAddressFormat(int $address_format_id): array {
		// Alpha Engine: Bridge para acionar o Repository
		$repositoryFactory = $this->registry->get('alpha_repository_factory');
		$repository = $repositoryFactory->get(AddressFormatRepository::class);
		$entity = $repository->find($address_format_id);

		if ($entity) {
			return [
				'address_format_id' => $entity->getId(),
				'name'              => $entity->getName(),
				'address_format'    => $entity->getAddressFormat()
			];
		}

		return [];
	}
}
