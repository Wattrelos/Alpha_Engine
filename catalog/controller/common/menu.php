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
		// Alpha Engine: O MenuRepository cuida da hidratação recursiva e do Cache
		/** @var MenuRepository $menuRepository */
		$menuRepository = $this->getRepository(MenuRepository::class);

		// Injeta a string HTML renderizada do menu na variável aguardada pelo Twig
		$data['categorias'] = $menuRepository->getMenuHtml();

		// Renderiza o template common/menu.twig
		return $this->render('common/menu', $data);
	}
}