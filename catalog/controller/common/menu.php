<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\MenuRepository;

class Menu extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$menuRepository = $this->repository->get(MenuRepository::class);
		$menuData = $menuRepository->getMenuData();
		
		return $this->render('common/menu', $menuData->toArray());
	}
}