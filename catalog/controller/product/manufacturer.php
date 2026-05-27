<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;

/**
 * Class Manufacturer
 * 
 * Refatorado para Alpha Engine: Utiliza ManufacturerRepository e injeção automática de layout.
 */
class Manufacturer extends BaseController {
	
	public function index(): void {
		$this->load->language('product/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_brand'),
			'href' => $this->url->link('product/manufacturer', 'language=' . $this->config->get('config_language'))
		];

		// Alpha Engine: O repositório cuida do agrupamento alfabético (A-Z, 0-9)
		$data['categories'] = $this->getRepository(ManufacturerRepository::class)->getAllGrouped();

		$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		$this->render('product/manufacturer_list', $data);
	}

	public function info(): void {
		$this->load->language('product/manufacturer');

		$manufacturer_id = (int)($this->request->get['manufacturer_id'] ?? 0);

		$filter_data = [
			'sort'  => $this->request->get['sort'] ?? 'p.sort_order',
			'order' => $this->request->get['order'] ?? 'ASC',
			'page'  => (int)($this->request->get['page'] ?? 1),
			'limit' => (int)($this->request->get['limit'] ?? $this->config->get('config_pagination'))
		];

		// Alpha Engine: Resgate de dados enriquecidos via Repositório
		$manufacturerData = $this->getRepository(ManufacturerRepository::class)->getManufacturerData($manufacturer_id, $filter_data);

		if ($manufacturerData->get('name')) {
			$this->document->setTitle($manufacturerData->get('name'));
			
			$data = $manufacturerData->toArray();
			
			// Os breadcrumbs base já vêm do repositório, adicionamos o Home aqui no controller
			array_unshift($data['breadcrumbs'], ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')]);

			$this->render('product/manufacturer_info', $data);
		} else {
			$this->response->redirect($this->url->link('error/not_found'));
		}
	}
}