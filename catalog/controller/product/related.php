<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Mappers\ProductMapper;
use Alpha\Mappers\CollectionToArrayConverter;

/**
 * Class Related
 *
 * Can be loaded using $this->load->controller('product/related');
 *
 * @package Opencart\Catalog\Controller\Product
 */
class Related extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return ?\Opencart\System\Engine\Action
	 */
	public function index(): string {
		$this->load->language('product/related');

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$data['products'] = [];

		$productMapper = new ProductMapper();

		// Alpha Engine: Recebe coleção de Entidades Product
		$product_entities = $productMapper->getRelated(
			$product_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id'),
			(int)$this->config->get('config_customer_group_id')
		);

		$language_id = (int)$this->config->get('config_language_id');
		$customer_group_id = (int)$this->config->get('config_customer_group_id');

		foreach ($product_entities as $product) {
			// Converte para array para compatibilidade com o controlador de thumb e Twig
			$result = CollectionToArrayConverter::convertEntity($product);

			// Extração da descrição ativa e preço especial do Grafo da Entidade
			$result['name'] = '';
			$result['description'] = '';
			foreach ($product->getDescriptions() as $desc) {
				if ($desc->getLanguageId() === $language_id) {
					$result['name'] = $desc->getName();
					$result['description'] = $desc->getDescription();
					break;
				}
			}

			$result['special'] = 0;
			foreach ($product->getSpecials() as $special_entity) {
				if ($special_entity->getCustomerGroupId() == $customer_group_id) {
					$result['special'] = $special_entity->getPrice();
					break;
				}
			}

			$description = trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')));

			if (oc_strlen($description) > $this->config->get('config_product_description_length')) {
				$description = oc_substr($description, 0, $this->config->get('config_product_description_length')) . '..';
			}

			if ($result['image'] && is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $result['image'];
			} else {
				$image = 'placeholder.png';
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}

			$product_data = [
				'thumb'       => $this->model_tool_image->resize($image, $this->config->get('config_image_related_width'), $this->config->get('config_image_related_height')),
				'description' => $description,
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
				'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['id'])
			] + $result;

			$data['products'][] = $this->load->controller('product/thumb', $product_data);
		}

		return $this->load->view('product/related', $data);

	}
}
