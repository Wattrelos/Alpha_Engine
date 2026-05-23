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
		$search       = (string)($this->request->get['search'] ?? '');
		$tag          = (string)($this->request->get['tag'] ?? '');
		$description  = (string)($this->request->get['description'] ?? '');
		$category_id  = (int)($this->request->get['category_id'] ?? 0);
		$sub_category = (string)($this->request->get['sub_category'] ?? '');
		$sort         = (string)($this->request->get['sort'] ?? 'p.sort_order');
		$order        = (string)($this->request->get['order'] ?? 'ASC');
		$page         = (int)($this->request->get['page'] ?? 1);

		// Alpha Engine Failsafe: Evita "Division by Zero" na renderização da paginação
		$limit = (int)($this->request->get['limit'] ?? $this->config->get('config_pagination_catalog'));
		$limit = $limit > 0 ? $limit : 10;

		$filter_data = [
			'filter_name'         => $search,
			'filter_search'       => $search,
			'filter_tag'          => $tag,
			'filter_description'  => $description,
			'filter_category_id'  => $category_id,
			'filter_sub_category' => $sub_category,
			'sort'                => $sort,
			'order'               => $order,
			'page'                => $page,
			'limit'               => $limit
		];

		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);
		
		// Alpha Engine: O Repositório encapsula DTO de filtros, limites, sort e subcategorias
		$response = $productRepository->getSearchData($filter_data);
		$data = $response->getData();

		$this->loadLanguageData('product/search', $data); // Injeta UI no DTO
		
		if ($search) {
			$this->document->setTitle($data['heading_title'] . ' - ' . $search);
		} elseif ($tag) {
			$this->document->setTitle($data['heading_title'] . ' - ' . $this->language->get('text_tag') . ' ' . $tag);
		} else {
			$this->document->setTitle($data['heading_title']);
		}

		$data['text_compare'] = sprintf($this->language->get('text_compare') ?? 'Comparar (%s)', isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0);
		$data['compare'] = $this->url->link('product/compare', 'language=' . $this->config->get('config_language'));

		$results = $data['products'] ?? [];
		$data['products'] = [];
		// Alpha Engine: Processamento Loader-Free utilizando o Repositório e o ImagePresenter (Fim do N+1 Controllers)
		foreach ($results as $result) {
			$data['products'][] = $this->load->view('product/thumb', $productRepository->getProductThumbData($result));
		}

		// Alpha Engine: Padroniza variável para View e adiciona Fallback (Failsafe Div zero já aplicado)
		$data['pagination_html'] = $this->load->controller('common/pagination', $data['pagination'] ?? []);
		$data['pagination']      = $data['pagination_html']; // Alias bridge para compatibilidade com Twig antigo
		$data['results']         = sprintf($this->language->get('text_pagination') ?? 'Exibindo %d a %d de %d (%d Páginas)', ($data['product_total']) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($data['product_total'] - $limit)) ? $data['product_total'] : ((($page - 1) * $limit) + $limit), $data['product_total'], ceil($data['product_total'] / $limit));

		$this->render('product/search', $data);
	}
}