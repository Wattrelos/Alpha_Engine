<?php
namespace Opencart\Catalog\Controller\Product;

use Alpha\Mappers\ProductMapper;
use Alpha\Mappers\CollectionToArrayConverter;
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ProductRepository;

/**
 * Class Related
 *
 * Can be loaded using $this->load->controller('product/related');
 *
 * @package Opencart\Catalog\Controller\Product
 */
class Related extends BaseController {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting
	 * @return string
	 */
	public function index(array $setting = []): string {
		$this->load->language('product/related');

		$product_id = (int)($setting['product_id'] ?? $this->request->get['product_id'] ?? 0);

		$data['products'] = [];

		$productMapper = new ProductMapper();

		// Alpha Engine: Recebe coleção de Entidades Product
		$product_entities = $productMapper->getRelated(
			$product_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id'),
			(int)$this->config->get('config_customer_group_id')
		);

		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);

		foreach ($product_entities as $product) {
			// Converte para array para compatibilidade com o controlador de thumb e Twig
			$result = CollectionToArrayConverter::convertEntity($product);

			// Garante ID para a Thumb
			$result['product_id'] = $result['id'] ?? 0;
			
			// Alpha Engine: Renderização de Thumbnail delegada ao Repositório sem N+1
			$data['products'][] = $this->viewRenderer->render('product/thumb', $productRepository->getProductThumbData($result));
		}

		return $this->load->view('product/related', $data);

	}
}
