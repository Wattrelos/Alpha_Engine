<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
/**
 * Class BestSeller
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Module
 */
class BestSeller extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting array of filters
	 *
	 * @return string
	 */
	public function index(array $setting): string {
		$this->load->language('extension/opencart/module/bestseller');

		$data['axis'] = $setting['axis'];

		$data['products'] = [];

		// Alpha Engine: Uso do HomeRepository para hidratação eficiente
		/** @var \Alpha\Model\Domain\Repositories\HomeRepository $homeRepository */
		$homeRepository = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\HomeRepository::class);

		// Bestseller
		$this->load->model('extension/opencart/module/bestseller');

		// Image
		$this->load->model('tool/image');

		$results = $this->model_extension_opencart_module_bestseller->getBestSellers($setting['limit']);

		if ($results) {
			// Extrai os IDs da query legada e delega a hidratação em lote para a Alpha Engine
			$productIds = array_column($results, 'product_id');
			$products = $homeRepository->getFeatured($productIds, $setting['limit']);

			foreach ($products as $product) {
				// Bugfix: Verifica existência do arquivo físico
				if (!empty($product['image']) && is_file(DIR_IMAGE . html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				$product_data = [
					'product_id'  => $product['id'],
					'thumb'       => $image,
					'name'        => $product['name'],
					'description' => oc_substr(trim(strip_tags(html_entity_decode($product['description'] ?? '', ENT_QUOTES, 'UTF-8'))), 0, (int)$this->config->get('config_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $product['minimum'] > 0 ? $product['minimum'] : 1,
					'rating'      => (int)$product['rating'],
					// Alpha Engine: Consome o link amigável gerado em lote
					'href'        => $product['href'] ?? $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['id'])
				];

				$data['products'][] = $this->load->controller('product/thumb', $product_data);
			}

			return $this->load->view('extension/opencart/module/bestseller', $data);
		} else {
			return '';
		}
	}
}
