<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
/**
 * Class Latest
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Module
 */
class Latest extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting array of filters
	 *
	 * @return string
	 */
	public function index(array $setting): string {
		$this->load->language('extension/opencart/module/latest');

		$data['axis'] = $setting['axis'];

		$data['products'] = [];

		// Alpha Engine: Uso do HomeRepository (Batch Loading e SEO unificado)
		/** @var \Alpha\Model\Domain\Repositories\HomeRepository $homeRepository */
		$homeRepository = $this->registry->get('alpha_repository_factory')->get(\Alpha\Model\Domain\Repositories\HomeRepository::class);

		// Image
		$this->load->model('tool/image');

		$products = $homeRepository->getLatest($setting['limit']);

		if ($products) {
			foreach ($products as $product) {
				// Bugfix: Verifica se o arquivo físico existe para evitar fallback silencioso (src="")
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

			return $this->load->view('extension/opencart/module/latest', $data);
		} else {
			return '';
		}
	}
}
