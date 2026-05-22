<?php
namespace Opencart\Catalog\Model\Setting;

use Alpha\Model\Domain\Repositories\EventRepository;

/**
 * Class Event
 *
 * Can be called using $this->load->model('setting/event');
 *
 * @package Opencart\Catalog\Model\Setting
 */
class Event extends \Opencart\System\Engine\Model {
	/**
	 * Get Events
	 *
	 * Get the record of the event records in the database.
	 *
	 * @return array<int, array<string, mixed>> event records
	 *
	 * @example
	 *
	 * $this->load->model('setting/event');
	 *
	 * $results = $this->model_setting_event->getEvents();
	 */
	public function getEvents(): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(EventRepository::class);
		return $repository->getEvents();
	}
}
