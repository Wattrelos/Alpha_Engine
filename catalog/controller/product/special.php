<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Mappers\ProductMapper;
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ProductRepository;

/**
 * Class Special
 *
 * @package Opencart\Catalog\Controller\Product
 */
class Special extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('product/special');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit']) && (int)$this->request->get['limit']) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_pagination');
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . $url)
		];

		$data['text_compare'] = sprintf($this->language->get('text_compare'), isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0);

		$data['compare'] = $this->url->link('product/compare', 'language=' . $this->config->get('config_language'));

		// Product
		$data['products'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		];

		$productMapper = new ProductMapper();

		$results = $productMapper->getSpecials(
			$filter_data,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id'),
			(int)$this->config->get('config_customer_group_id')
		);

		$results = array_map(fn($item) => ['product_id' => $item['id']] + $item, $results);

		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);

		foreach ($results as $result) {
			// Alpha Engine: Renderização de Thumbnail sem Controller N+1
			$data['products'][] = $this->viewRenderer->render('product/thumb', $productRepository->getProductThumbData($result));
		}

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['sorts'] = [];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_default'),
			'value' => 'p.sort_order-ASC',
			'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=p.sort_order&order=ASC' . $url)
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=pd.name&order=ASC' . $url)
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=pd.name&order=DESC' . $url)
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'ps.price-ASC',
			'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=ps.price&order=ASC' . $url)
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'ps.price-DESC',
			'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=ps.price&order=DESC' . $url)
		];

		if ($this->config->get('config_review_status')) {
			$data['sorts'][] = [
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=rating&order=DESC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=rating&order=ASC' . $url)
			];
		}

		$data['sorts'][] = [
			'text'  => $this->language->get('text_model_asc'),
			'value' => 'p.model-ASC',
			'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=p.model&order=ASC' . $url)
		];

		$data['sorts'][] = [
			'text'  => $this->language->get('text_model_desc'),
			'value' => 'p.model-DESC',
			'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&sort=p.model&order=DESC' . $url)
		];

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['limits'] = [];

		$limits = array_unique([$this->config->get('config_pagination'), 25, 50, 75, 100]);

		sort($limits);

		foreach ($limits as $value) {
			$data['limits'][] = [
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . $url . '&limit=' . $value)
			];
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$product_total = $productMapper->getTotalSpecials(
			(int)$this->config->get('config_customer_group_id'),
			(int)$this->config->get('config_store_id')
		);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $product_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('product/special', 'language=' . $this->config->get('config_language') . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

		// https://developers.google.com/search/blog/2011/09/pagination-with-relnext-and-relprev
		if ($page == 1) {
			$this->document->addLink($this->url->link('product/special', 'language=' . $this->config->get('config_language')), 'canonical');
		} else {
			$this->document->addLink($this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&page=' . $page), 'canonical');
		}

		if ($page > 1) {
			$this->document->addLink($this->url->link('product/special', 'language=' . $this->config->get('config_language') . (($page - 2) ? '&page=' . ($page - 1) : '')), 'prev');
		}

		if ($limit && ceil($product_total / $limit) > $page) {
			$this->document->addLink($this->url->link('product/special', 'language=' . $this->config->get('config_language') . '&page=' . ($page + 1)), 'next');
		}

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		// Alpha Engine: Renderização envelopada Anti-WSOD
		$this->render('product/special', $data);
	}
}
