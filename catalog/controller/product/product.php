<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\CategoryRepository;
use Alpha\Model\Domain\Repositories\ManufacturerRepository;
use Opencart\Catalog\Controller\Product\Review;
use Opencart\Catalog\Controller\Product\Related;

/**
 * Class Product
 *
 * @package Opencart\Catalog\Controller\Product
 */
class Product extends BaseController {
	/**
	 * Index
	 *
	 * @return ?\Opencart\System\Engine\Action
	 */
	public function index(): ?\Opencart\System\Engine\Action {
		$product_id = (int)($this->request->get['product_id'] ?? 0);

		// Alpha Engine: Utilização do Repositório de Domínio para o Produto
		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);

		// Alpha Engine: O Repositório agora orquestra o DTO completo da página (Batch Loading)
		$product_info = $productRepository->getProductDisplayData($product_id);

		if ($product_info) {
			$data = $product_info;
			$this->loadLanguageData('product/product', $data); // Alpha Engine: Unifica traduções automaticamente
			
			// Assets e SEO (Responsabilidade restrita do Controller)
			$this->document->setTitle($data['meta_title'] ?: $data['name']);
			$this->document->setDescription($data['meta_description'] ?? '');
			$this->document->setKeywords($data['meta_keyword'] ?? '');
			$this->document->addLink($this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			
			$data['breadcrumbs'] = $this->buildBreadcrumbs($data, $product_id);

			// Tokens e Links Utilitários
			if (!isset($this->session->data['upload_token'])) {
				$this->session->data['upload_token'] = oc_token(32);
			}
			$data['upload'] = $this->url->link('tool/upload', 'language=' . $this->config->get('config_language') . '&upload_token=' . $this->session->data['upload_token']);
			$data['manufacturers'] = $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . ($data['manufacturer_id'] ?? 0));
			$data['share'] = $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id);
			$data['wishlist_add'] = $this->url->link('account/wishlist.add', 'language=' . $this->config->get('config_language'));
			$data['compare_add'] = $this->url->link('product/compare.add', 'language=' . $this->config->get('config_language'));
			
			// Alpha Engine: Instanciação Loader-Free de Componentes Sub-Widgets
			$data['review']  = (new Review($this->registry))->index();
			$data['related'] = (new Related($this->registry))->index(['product_id' => $product_id]);

			if ($this->config->get('config_product_report_status')) {
				$productRepository->addReport($product_id, oc_get_ip());
			}

			$data['language'] = $this->config->get('config_language');

			// Alpha Engine: render() injeta Header/Footer automaticamente
			$this->render('product/product', $data);
			
			return null;
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}

		return null;
	}

	/**
	 * Constrói de forma isolada e limpa as migalhas de pão (Breadcrumbs) da navegação
	 */
	private function buildBreadcrumbs(array $product_info, int $product_id): array {
		$breadcrumbs = [];
		$breadcrumbs[] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		// Resolução da Árvore de Categorias
		$categoryRepository = $this->getRepository(\Alpha\Model\Domain\Repositories\CategoryRepository::class);
		if (isset($this->request->get['path'])) {
			$path = '';
			$parts = explode('_', (string)$this->request->get['path']);
			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				$path = !$path ? $path_id : $path . '_' . $path_id;
				$category_info = $categoryRepository->getCategory((int)$path_id);
				if ($category_info) {
					$breadcrumbs[] = [
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $path)
					];
				}
			}

			$category_info = $categoryRepository->getCategory($category_id);
			if ($category_info) {
				$url = '';
				foreach (['sort', 'order', 'page', 'limit'] as $key) {
					if (isset($this->request->get[$key])) {
						$url .= '&' . $key . '=' . $this->request->get[$key];
					}
				}
				$breadcrumbs[] = [
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . $url)
				];
			}
		}

		// Resolução da Marca (Fabricante)
		$manufacturerRepository = $this->getRepository(\Alpha\Model\Domain\Repositories\ManufacturerRepository::class);
		if (isset($this->request->get['manufacturer_id'])) {
			$breadcrumbs[] = [
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer', 'language=' . $this->config->get('config_language'))
			];
			$url = '';
			foreach (['sort', 'order', 'page', 'limit'] as $key) {
				if (isset($this->request->get[$key])) {
					$url .= '&' . $key . '=' . $this->request->get[$key];
				}
			}
			$manufacturer_info = $manufacturerRepository->getManufacturer((int)$this->request->get['manufacturer_id']);
			if ($manufacturer_info) {
				$breadcrumbs[] = [
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				];
			}
		}

		// Resolução de Pesquisas
		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';
			foreach (['search', 'tag', 'description', 'category_id', 'sub_category', 'sort', 'order', 'page', 'limit'] as $key) {
				if (isset($this->request->get[$key])) {
					$url .= '&' . $key . '=' . $this->request->get[$key];
				}
			}
			$breadcrumbs[] = [
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $url)
			];
		}

		$url = '';
		foreach (['path', 'filter', 'manufacturer_id', 'search', 'tag', 'description', 'category_id', 'sub_category', 'sort', 'order', 'page', 'limit'] as $key) {
			if (isset($this->request->get[$key])) {
				$url .= '&' . $key . '=' . $this->request->get[$key];
			}
		}
		$breadcrumbs[] = [
			'text' => $product_info['name'],
			'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . $url . '&product_id=' . $product_id)
		];

		return $breadcrumbs;
	}
}
