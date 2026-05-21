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
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		// Alpha Engine: Utilização do Repositório de Domínio para o Produto
		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);

		// Delega a resolução dos contextos (idioma, loja, grupo) para o repositório
		$product_info = $productRepository->getDetailedProduct($product_id);

		if ($product_info) {
			$data = $product_info;
			$this->loadLanguageData('product/product', $data); // Alpha Engine: Unifica traduções automaticamente
			
			$this->document->setTitle($data['meta_title']);
			$this->document->addLink($this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id), 'canonical');

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
			];

			// Alpha Engine: Utilização do Repositório de Categoria
			/** @var CategoryRepository $categoryRepository */
			$categoryRepository = $this->getRepository(CategoryRepository::class);

			if (isset($this->request->get['path'])) {
				$path = '';

				$parts = explode('_', (string)$this->request->get['path']);

				$category_id = (int)array_pop($parts);

				foreach ($parts as $path_id) {
					if (!$path) {
						$path = $path_id;
					} else {
						$path .= '_' . $path_id;
					}

					$category_info = $categoryRepository->getCategory((int)$path_id);

					if ($category_info) {
						$data['breadcrumbs'][] = [
							'text' => $category_info['name'],
							'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $path)
						];
					}
				}

				// Set the last category breadcrumb
				$category_info = $categoryRepository->getCategory($category_id);

				if ($category_info) {
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
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . $url)
					];
				}
			}

			// Alpha Engine: Utilização do Repositório de Fabricante
			/** @var ManufacturerRepository $manufacturerRepository */
			$manufacturerRepository = $this->getRepository(ManufacturerRepository::class);

			if (isset($this->request->get['manufacturer_id'])) {
				
				$data['breadcrumbs'][] = [
					'text' => $this->language->get('text_brand'),
					'href' => $this->url->link('product/manufacturer', 'language=' . $this->config->get('config_language'))
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

				$manufacturer_info = $manufacturerRepository->getManufacturer((int)$this->request->get['manufacturer_id']);

				if ($manufacturer_info) {
					$data['breadcrumbs'][] = [
						'text' => $manufacturer_info['name'],
						'href' => $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
					];
				}
			}

			if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
				$url = '';

				if (isset($this->request->get['search'])) {
					$url .= '&search=' . $this->request->get['search'];
				}

				if (isset($this->request->get['tag'])) {
					$url .= '&tag=' . $this->request->get['tag'];
				}

				if (isset($this->request->get['description'])) {
					$url .= '&description=' . $this->request->get['description'];
				}

				if (isset($this->request->get['category_id'])) {
					$url .= '&category_id=' . $this->request->get['category_id'];
				}

				if (isset($this->request->get['sub_category'])) {
					$url .= '&sub_category=' . $this->request->get['sub_category'];
				}

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
					'text' => $this->language->get('text_search'),
					'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $url)
				];
			}

			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

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
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . $url . '&product_id=' . $product_id)
			];

			// Assets e SEO (Responsabilidade do Controller)
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->setDescription($data['meta_description']);
			$this->document->setKeywords($data['meta_keyword']);

			$data['heading_title'] = $data['name'];
			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $data['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
			$data['text_reviews'] = sprintf($this->language->get('text_reviews'), (int)($data['reviews'] ?? 0));

			$this->session->data['upload_token'] = oc_token(32);
			$data['upload'] = $this->url->link('tool/upload', 'language=' . $this->config->get('config_language') . '&upload_token=' . $this->session->data['upload_token']);
			$data['manufacturers'] = $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $data['manufacturer_id']);
			
			// Product Codes (Vêm prontos do Mapper)
			$data['product_codes'] = [];
			foreach ($data['codes'] as $result) {
				if ($result['status']) {
					$data['product_codes'][] = $result;
				}
			}

			$data['stock'] = $data['stock_status_text'] ?: $data['quantity'];
			
			// Alpha Engine: Instanciação Loader-Free do Componente de Avaliações
			$data['review'] = (new Review($this->registry))->index();

			$data['wishlist_add'] = $this->url->link('account/wishlist.add', 'language=' . $this->config->get('config_language'));
			$data['compare_add'] = $this->url->link('product/compare.add', 'language=' . $this->config->get('config_language'));

			// Formatação de Imagens (Controller utiliza model_tool_image)
			$this->load->model('tool/image');

			if ($data['image'] && is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
				$data['popup'] = $this->model_tool_image->resize($data['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'));
				$data['thumb'] = $this->model_tool_image->resize($data['image'], $this->config->get('config_image_thumb_width'), $this->config->get('config_image_thumb_height'));
			} else {
				$data['popup'] = '';
				$data['thumb'] = '';
			}

			$data['images'] = [];
			foreach ($data['product_images'] as $result) {
				if ($result['image'] && is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
					$data['images'][] = [
						'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')),
						'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
					];
				}
			}

			// Preços e Descontos (Controller utiliza Currency e Tax)
			$data['price'] = ($this->customer->isLogged() || !$this->config->get('config_customer_price')) 
				? $this->currency->format($this->tax->calculate($data['price'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) 
				: false;

			$data['special'] = ((float)$data['special']) 
				? $this->currency->format($this->tax->calculate($data['special'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) 
				: false;

			$data['tax'] = ($this->config->get('config_tax')) 
				? $this->currency->format((float)$data['special'] ? $data['special'] : $data['price'], $this->session->data['currency']) 
				: false;

			$data['template_discounts'] = [];
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				foreach ($data['discounts'] as $discount) {
					$data['template_discounts'][] = ['price' => $this->currency->format($this->tax->calculate($discount['price'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])] + $discount;
				}
			}
			$data['discounts'] = $data['template_discounts'];

			// Opções (Resolução de imagens e preços de opções)
			$data['template_options'] = [];
			foreach ($data['options'] as $option) {
				if (!isset($data['override']['variant'][$option['product_option_id']])) {
					$product_option_value_data = [];
					foreach ($option['product_option_value'] as $option_value) {
						if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
							$price = ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price'])
								? $this->currency->format($this->tax->calculate($option_value['price'], $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
								: false;
							$image = ($option_value['image'] && is_file(DIR_IMAGE . html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'))) ? $option_value['image'] : '';
							
							$product_option_value_data[] = [
								'image' => $this->model_tool_image->resize($image, 50, 50),
								'price' => $price
							] + $option_value;
						}
					}
					$data['template_options'][] = ['product_option_value' => $product_option_value_data] + $option;
				}
			}
			$data['options'] = $data['template_options'];

			// Assinaturas (Cálculo de parcelas)
			$data['template_subscriptions'] = [];
			foreach ($data['subscriptions'] as $result) {
				$description = '';
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = ($data['special'] ?: $data['price']) / ($result['duration'] ?: 1);
					$price_formatted = $this->currency->format($this->tax->calculate($price, $data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$description = $result['duration'] 
						? sprintf($this->language->get('text_subscription_duration'), $price_formatted, $result['cycle'], $this->language->get('text_' . $result['frequency']), $result['duration'])
						: sprintf($this->language->get('text_subscription_cancel'), $price_formatted, $result['cycle'], $this->language->get('text_' . $result['frequency']));
				}
				$data['template_subscriptions'][] = ['description' => $description] + $result;
			}
			$data['subscription_plans'] = $data['template_subscriptions'];

			$data['share'] = $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id);
			
			// Alpha Engine: Instanciação Loader-Free do Componente de Produtos Relacionados
			$data['related'] = (new Related($this->registry))->index(['product_id' => $product_id]);

			$data['tags'] = [];
			if ($data['tag']) {
				$tags = explode(',', $data['tag']);
				foreach ($tags as $tag) {
					$data['tags'][] = [
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'language=' . $this->config->get('config_language') . '&tag=' . trim($tag))
					];
				}
			}

			if ($this->config->get('config_product_report_status')) {
				$productRepository->addReport((int)$this->request->get['product_id'], oc_get_ip());
			}

			$data['language'] = $this->config->get('config_language');

			// Alpha Engine: render() injeta Header/Footer automaticamente
			return $this->render('product/product', $data);
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}

		return null;
	}
}
