<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\DataTransferObject\ViewResponse;

/**
 * Class Category
 *
 * @package Opencart\Catalog\Controller\Product
 */
class Category extends BaseController {
	/**
	 * Index
	 *
	 * @return \Opencart\System\Engine\Action|null
	 */
	public function index(): ?\Opencart\System\Engine\Action {
		$path   = (string)($this->request->get['path'] ?? '');
		$parts  = explode('_', $path);
		$category_id = (int)array_pop($parts);

		$filter_data = [
			'filter_filter' => (string)($this->request->get['filter'] ?? ''),
			'sort'          => (string)($this->request->get['sort'] ?? 'p.sort_order'),
			'order'         => (string)($this->request->get['order'] ?? 'ASC'),
			'page'          => (int)($this->request->get['page'] ?? 1),
			'limit'         => (int)($this->request->get['limit'] ?? $this->config->get('config_pagination_catalog')),
			'path'          => $path
		];

		/** @var ViewResponse $response */
		$response = $this->categoryRepository->getCategoryData($category_id, $filter_data);

		if (!$response->get('name')) {
			return new \Opencart\System\Engine\Action('error/not_found');
		}

		$data = $response->getData();

		// UI: Configuração do texto de comparação e links de ação
		$data['text_compare'] = sprintf($this->language->get('text_compare'), isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0);
		$data['compare']      = $this->url->link('product/compare', 'language=' . $this->config->get('config_language'));
		$data['continue']     = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		// UI: Renderização da Paginação via Controller legado (Bridge)
		$data['pagination_html'] = $this->load->controller('common/pagination', $data['pagination']);
		$data['results']         = sprintf($this->language->get('text_pagination'), ($data['product_total']) ? (($filter_data['page'] - 1) * $filter_data['limit']) + 1 : 0, ((($filter_data['page'] - 1) * $filter_data['limit']) > ($data['product_total'] - $filter_data['limit'])) ? $data['product_total'] : ((($filter_data['page'] - 1) * $filter_data['limit']) + $filter_data['limit']), $data['product_total'], ceil($data['product_total'] / $filter_data['limit']));

		// Injeção de componentes globais
		$data['header']         = $this->load->controller('common/header');
		$data['footer']         = $this->load->controller('common/footer');
		$data['column_left']    = $this->load->controller('common/column_left');
		$data['column_right']   = $this->load->controller('common/column_right');
		$data['content_top']    = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');

		$this->response->setOutput($this->load->view('product/category', $data));

		return null;
	}
}
