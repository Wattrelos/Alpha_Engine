<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\CategoryRepository;

/**
 * Class Search
 * 
 * Refatorado para Alpha Engine: Utiliza ProductRepository e orquestração via BaseController.
 */
class Search extends BaseController {
	
	public function index(): void {
		$this->loadLanguage('product/search');

		$search       = (string)($this->request->get['search'] ?? '');
		$tag          = (string)($this->request->get['tag'] ?? '');
		$description  = (string)($this->request->get['description'] ?? '');
		$category_id  = (int)($this->request->get['category_id'] ?? 0);
		$sub_category = (string)($this->request->get['sub_category'] ?? '');
		$sort         = (string)($this->request->get['sort'] ?? 'p.sort_order');
		$order        = (string)($this->request->get['order'] ?? 'ASC');
		$page         = (int)($this->request->get['page'] ?? 1);
		$limit        = (int)($this->request->get['limit'] ?? $this->config->get('config_pagination'));

		if (isset($this->request->get['search'])) {
			$this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->request->get['search']);
		} elseif (isset($this->request->get['tag'])) {
			$this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_tag') . ' ' . $this->request->get['tag']);
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}

		$data['breadcrumbs'] = $this->getBaseSearchBreadcrumbs();

		$filter_data = [
			'filter_name'         => $search,
			'filter_search'       => $search,
			'filter_tag'          => $tag,
			'filter_description'  => $description,
			'filter_category_id'  => $category_id,
			'filter_sub_category' => $sub_category,
			'sort'                => $sort,
			'order'               => $order,
			'start'               => ($page - 1) * $limit,
			'limit'               => $limit
		];

		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);
		
		// Alpha Engine: Usa o ProductRepository para a busca
		$results = $productRepository->getProducts($filter_data);
		$product_total = $productRepository->getTotalProducts($filter_data);

		$data['products'] = [];
		
		// Processamento de Thumbs (Responsabilidade do Controller)
		foreach ($results as $result) {
			$data['products'][] = $this->load->controller('product/thumb', $result);
		}

		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = $this->getRepository(CategoryRepository::class);

		// Configuração do formulário de busca
		$data['categories'] = [];
		$categories_1 = $categoryRepository->getCategories(0);
		foreach ($categories_1 as $category_1) {
			$data['categories'][] = [
				'category_id' => $category_1['id'],
				'name'        => $category_1['name']
			];
			$categories_2 = $categoryRepository->getCategories((int)$category_1['id']);
			foreach ($categories_2 as $category_2) {
				$data['categories'][] = [
					'category_id' => $category_2['id'],
					'name'        => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $category_2['name']
				];
				$categories_3 = $categoryRepository->getCategories((int)$category_2['id']);
				foreach ($categories_3 as $category_3) {
					$data['categories'][] = [
						'category_id' => $category_3['id'],
						'name'        => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $category_3['name']
					];
				}
			}
		}

		$data['search']       = $search;
		$data['category_id']  = $category_id;
		$data['sub_category'] = $sub_category;
		$data['description']  = $description;

		// URLs de ordenação e paginação
		$url = '';
		if (isset($this->request->get['search'])) $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		if (isset($this->request->get['tag'])) $url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
		if (isset($this->request->get['description'])) $url .= '&description=' . $this->request->get['description'];
		if (isset($this->request->get['category_id'])) $url .= '&category_id=' . $this->request->get['category_id'];
		if (isset($this->request->get['sub_category'])) $url .= '&sub_category=' . $this->request->get['sub_category'];

		$data['sorts'] = [];
		$data['sorts'][] = ['text' => $this->language->get('text_default'), 'value' => 'p.sort_order-ASC', 'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $url . '&sort=p.sort_order&order=ASC')];
		$data['sorts'][] = ['text' => $this->language->get('text_name_asc'), 'value' => 'pd.name-ASC', 'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $url . '&sort=pd.name&order=ASC')];

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $product_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $url . '&sort=' . $sort . '&order=' . $order . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$this->render('product/search', $data);
	}

	/**
	 * Helper privado para breadcrumbs de busca.
	 */
	private function getBaseSearchBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];
		$breadcrumbs[] = [
			'text' => $this->language->get('text_search'),
			'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language'))
		];
		return $breadcrumbs;
	}
}