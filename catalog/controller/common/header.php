<?php
namespace Opencart\Catalog\Controller\Common;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\HeaderRepository;
use Opencart\Catalog\Controller\Common\Language;
use Opencart\Catalog\Controller\Common\Currency;
use Opencart\Catalog\Controller\Common\Search;
use Opencart\Catalog\Controller\Common\Cart;
use Opencart\Catalog\Controller\Common\Menu;
/**
 * Class Header
 *
 * Can be called from $this->load->controller('common/header');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Header extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$route = (string)($this->request->get['route'] ?? 'common/home');

		// Alpha Engine: Injeção do repositório de domínio para dados de identidade e navegação
		$headerRepository = $this->repository->get(HeaderRepository::class);
		$headerData = $headerRepository->getHeaderData();

		$data = $headerData->toArray();

		// Alpha Engine: Uso do LayoutRepository (injetado globalmente na BaseController)
		// Resolvemos os módulos de analytics e assets baseados na rota atual
		$data['analytics'] = $this->layout->getModulesByRoute($route, 'analytics');
		
		// Alpha Engine: Sincronização de Assets via Motor Alpha
		// Substitui as chamadas legadas ao $this->document->getStyles/Scripts
		$data['styles']    = $this->layout->getStylesByRoute($route);
		$data['scripts']   = $this->layout->getScriptsByRoute($route, 'header');
		$data['links']     = $this->layout->getLinksByRoute($route);

		// Alpha Engine: Injeção Loader-Free de sub-componentes do Header
		$data['language'] = (new Language($this->registry))->index();
		$data['currency'] = (new Currency($this->registry))->index();
		$data['search']   = (new Search($this->registry))->index();
		$data['cart']     = (new Cart($this->registry))->index();
		$data['menu']     = (new Menu($this->registry))->index();

		return $this->render('common/header', $data);
	}
}
