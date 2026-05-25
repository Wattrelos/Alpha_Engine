<?php
namespace Opencart\Catalog\Controller\Startup;

use Alpha\Model\Domain\Repositories\StartupRepository;

class Startup extends \Opencart\System\Engine\Controller {
	public function index(): void {
		/** @var StartupRepository $startupRepo */
		$startupRepo = $this->registry->get('alpha_repository_factory')->get(StartupRepository::class);
		$startups = $startupRepo->getStartups();

		foreach ($startups as $startup) {
			$this->load->controller($startup->getAction());
		}
	}
}
