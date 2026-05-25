<?php
namespace Opencart\Catalog\Controller\Startup;

use Alpha\Model\Domain\Repositories\EventRepository;

class Event extends \Opencart\System\Engine\Controller {
	public function index(): void {
		/** @var EventRepository $eventRepo */
		$eventRepo = $this->registry->get('alpha_repository_factory')->get(EventRepository::class);
		$events = $eventRepo->getEvents();

		foreach ($events as $eventEntity) {
			$trigger = substr($eventEntity->getTrigger(), strpos($eventEntity->getTrigger(), '/') + 1);
			$this->event->register($trigger, new \Opencart\System\Engine\Action($eventEntity->getAction()), $eventEntity->getSortOrder(), $eventEntity->getDescription());
		}
	}
}
