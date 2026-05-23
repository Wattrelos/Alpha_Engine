<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\DataTransferObject\ViewResponse;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;

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

		// Alpha Engine Failsafe: Evita "Division by Zero" na renderização da paginação
		$limit = (int)($this->request->get['limit'] ?? $this->config->get('config_pagination_catalog'));
		$limit = $limit > 0 ? $limit : 10; // Força limite mínimo seguro

		$filter_data = [
			'filter_filter' => (string)($this->request->get['filter'] ?? ''),
			'sort'          => (string)($this->request->get['sort'] ?? 'p.sort_order'),
			'order'         => (string)($this->request->get['order'] ?? 'ASC'),
			'page'          => (int)($this->request->get['page'] ?? 1),
			'limit'         => $limit,
			'path'          => $path
		];

		$categoryRepository = $this->getRepository(CategoryRepository::class);

		/** @var ViewResponse $response */
		$response = $categoryRepository->getCategoryData($category_id, $filter_data);

		if (!$response->get('name')) {
			return new \Opencart\System\Engine\Action('error/not_found');
		}

		$data = $response->getData();
        
		$this->loadLanguageData('product/category', $data); // Injeta variáveis de linguagem
		$data['heading_title'] = $data['name']; // Substitui o título genérico pelo nome da categoria

		// UI: Configuração do texto de comparação e links de ação
		$data['text_compare'] = sprintf($this->language->get('text_compare'), isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0);
		$data['compare']      = $this->url->link('product/compare', 'language=' . $this->config->get('config_language'));
		$data['continue']     = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));


		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);

		// Alpha Engine: Processamento Loader-Free utilizando o Repositório e o ImagePresenter (Fim do N+1 Controllers)
		$results = $data['products'] ?? [];
		$data['products'] = [];
		foreach ($results as $result) {
			$data['products'][] = $this->load->view('product/thumb', $productRepository->getProductThumbData($result));
		}

		// UI: Renderização da Paginação via Controller legado (Bridge)
		$data['pagination_html'] = $this->load->controller('common/pagination', $data['pagination'] ?? []);
		$data['results']         = sprintf($this->language->get('text_pagination'), ($data['product_total']) ? (($filter_data['page'] - 1) * $filter_data['limit']) + 1 : 0, ((($filter_data['page'] - 1) * $filter_data['limit']) > ($data['product_total'] - $filter_data['limit'])) ? $data['product_total'] : ((($filter_data['page'] - 1) * $filter_data['limit']) + $filter_data['limit']), $data['product_total'], ceil($data['product_total'] / $filter_data['limit']));

		// Alpha Engine: Orquestração nativa de Layout e Response (Fim da Injeção Manual de Header/Footer)
		$this->render('product/category', $data);

		return null;
	}
}
