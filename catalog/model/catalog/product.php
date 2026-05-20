<?php
namespace Opencart\Catalog\Model\Catalog;

use Alpha\Mappers\EntityMappers\ProductMapper;
/**
 * Class Product
 *
 * Can be called using $this->load->model('catalog/product');
 *
 * @package Opencart\Catalog\Model\Catalog
 */
class Product extends \Opencart\System\Engine\Model {
	/**
	 * Constructor
	 *
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
	}

	/**
	 * Edit Product Quantity
	 *
	 * Edit product quantity record in the database.
	 *
	 * @param int                  $product_id primary key of the product record
	 * @param int                  $quantity
	 * @param array<string, mixed> $data       array of data
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $this->model_catalog_product->editQuantity($product_id, $quantity);
	 */
	public function editQuantity(int $product_id, int $quantity): void {
		$mapper = new ProductMapper();
		$mapper->updateQuantity($product_id, $quantity);
	}

	/**
	 * Get Product
	 *
	 * Get the record of the product record in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<string, mixed> product record that has product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $product_info = $this->model_catalog_product->getProduct($product_id);
	 */
	public function getProduct(int $product_id): array {
		$mapper = new ProductMapper();
		
		return $mapper->getProduct(
			$product_id, 
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id'),
			(int)$this->config->get('config_customer_group_id')
		);
	}

	/**
	 * Get Products
	 *
	 * Get the record of the product records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> product records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $products = $this->model_catalog_product->getProducts();
	 */
	public function getProducts(array $data = []): array {
		$mapper = new ProductMapper();
		
		return $mapper->getProducts(
			$data,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id'),
			(int)$this->config->get('config_customer_group_id')
		);
	}

	/**
	 * Get Total Products
	 *
	 * Get the total number of total product records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of product records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $product_total = $this->model_catalog_product->getTotalProducts();
	 */
	public function getTotalProducts(array $data = []): int {
		$mapper = new ProductMapper();
		
		return $mapper->getTotalProducts(
			$data,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Get Categories
	 *
	 * Get the record of the product category records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> category records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $categories = $this->model_catalog_product->getCategories($product_id);
	 */
	public function getCategories(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getCategories($product_id);
	}

	/**
	 * Get Categories By Category ID
	 *
	 * Get the record of the product categories by category records in the database.
	 *
	 * @param int $product_id  primary key of the product record
	 * @param int $category_id primary key of the category record
	 *
	 * @return array<string, mixed> category record that has product ID, category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $categories = $this->model_catalog_product->getCategoriesByCategoryId($product_id, $category_id);
	 */
	public function getCategoriesByCategoryId(int $product_id, int $category_id): array {
		$mapper = new ProductMapper();
		return $mapper->getCategoriesByCategoryId($product_id, $category_id);
	}

	/**
	 * Get Total Categories By Category ID
	 *
	 * Get the total number of total product categories by category records in the database.
	 *
	 * @param int $product_id  primary key of the product record
	 * @param int $category_id primary key of the category record
	 *
	 * @return int total number of product category records that have product ID, category ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $category_total = $this->model_catalog_product->getTotalCategoriesByCategoryId($product_id, $category_id);
	 */
	public function getTotalCategoriesByCategoryId(int $product_id, int $category_id): int {
		$mapper = new ProductMapper();
		return $mapper->getTotalCategoriesByCategoryId($category_id);
	}

	/**
	 * Get Codes
	 *
	 * Get the record of the product code records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> code records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $codes = $this->model_catalog_product->getCodes($product_id);
	 */
	public function getCodes(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getCodes($product_id);
	}

	/**
	 * Get Attributes
	 *
	 * Get the record of the product attribute records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> attribute records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $attribute_groups = $this->model_catalog_product->getAttributes($product_id);
	 */
	public function getAttributes(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getAttributes($product_id, (int)$this->config->get('config_language_id'));
	}

	/**
	 * Edit Option Quantity
	 *
	 * Edit product option record in the database.
	 *
	 * @param int $product_id              primary key of the product record
	 * @param int $product_option_id       primary key of the product option record
	 * @param int $product_option_value_id primary key of the product option value record
	 * @param int $quantity
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $this->model_catalog_product->editOptionQuantity($product_id, $product_option_id, $product_option_value_id, $quantity);
	 */
	public function editOptionQuantity(int $product_id, int $product_option_id, int $product_option_value_id, int $quantity): void {
		$mapper = new ProductMapper();
		$mapper->updateOptionQuantity($product_id, $product_option_id, $product_option_value_id, $quantity);
	}

	/**
	 * Get Option
	 *
	 * Get the record of the product option record in the database.
	 *
	 * @param int $product_id        primary key of the product record
	 * @param int $product_option_id primary key of the product option record
	 *
	 * @return array<string, mixed> option record that has product ID, product option ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $product_option = $this->model_catalog_product->getOption($product_id, $product_option_id);
	 */
	public function getOption(int $product_id, int $product_option_id): array {
		$mapper = new ProductMapper();
		return $mapper->getOption($product_id, $product_option_id, (int)$this->config->get('config_language_id'));
	}

	/**
	 * Get Options
	 *
	 * Get the record of the product option records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> option records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $product_options = $this->model_catalog_product->getOptions($product_id);
	 */
	public function getOptions(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getOptions($product_id, (int)$this->config->get('config_language_id'));
	}

	/**
	 * Get Option Value
	 *
	 * Get the record of the product option value record in the database.
	 *
	 * @param int $product_id              primary key of the product record
	 * @param int $product_option_value_id primary key of the product option value record
	 *
	 * @return array<string, mixed> option value record that has product ID, product option value ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $product_option_value_info = $this->model_catalog_product->getOptionValue($product_id, $product_option_value_id);
	 */
	public function getOptionValue(int $product_id, int $product_option_value_id): array {
		$mapper = new ProductMapper();
		return $mapper->getOptionValue($product_id, $product_option_value_id, (int)$this->config->get('config_language_id'));
	}

	/**
	 * Get Option Values
	 *
	 * Get the record of the product option value records in the database.*
	 *
	 * @param int $product_id        primary key of the product record
	 * @param int $product_option_id primary key of the product option record
	 *
	 * @return array<string, mixed> option value records that have product ID, product option ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $product_option_values = $this->model_catalog_product->getOptionValues($product_id, $product_option_id);
	 */
	public function getOptionValues(int $product_id, int $product_option_id): array {
		$mapper = new ProductMapper();
		return $mapper->getOptionValues($product_id, $product_option_id, (int)$this->config->get('config_language_id'));
	}

	/**
	 * Get Discounts
	 *
	 * Get the record of the product discount records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> discount records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $discounts = $this->model_catalog_product->getDiscounts($product_id);
	 */
	public function getDiscounts(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getDiscounts(
			$product_id,
			(int)$this->config->get('config_customer_group_id')
		);
	}

	/**
	 * Get Images
	 *
	 * Get the record of the product image records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> image records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $results = $this->model_catalog_product->getImages($product_id);
	 */
	public function getImages(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getImages($product_id);
	}

	/**
	 * Get Subscription
	 *
	 * Get the record of the product subscription record in the database.
	 *
	 * @param int $product_id           primary key of the product record
	 * @param int $subscription_plan_id primary key of the subscription plan record
	 *
	 * @return array<string, mixed> subscription record that has product ID, subscription plan ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $product_subscription_info = $this->model_catalog_product->getSubscription($product_id, $subscription_plan_id);
	 */
	public function getSubscription(int $product_id, int $subscription_plan_id): array {
		$mapper = new ProductMapper();
		return $mapper->getSubscription(
			$product_id,
			$subscription_plan_id,
			(int)$this->config->get('config_customer_group_id')
		);
	}

	/**
	 * Get Subscriptions
	 *
	 * Get the record of the product subscription records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> subscription records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $subscriptions = $this->model_catalog_product->getSubscriptions($product_id);
	 */
	public function getSubscriptions(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getSubscriptions(
			$product_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_customer_group_id')
		);
	}

	/**
	 * Get Layout ID
	 *
	 * Get the record of the product layout record in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return int layout record that has product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $layout_id = $this->model_catalog_product->getLayoutId($product_id);
	 */
	public function getLayoutId(int $product_id): int {
		$mapper = new ProductMapper();
		return $mapper->getLayoutId($product_id, (int)$this->config->get('config_store_id'));
	}

	/**
	 * Get Related
	 *
	 * Get the record of the product related record in the database.*
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return array<int, array<string, mixed>> related records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $results = $this->model_catalog_product->getRelated($product_id);
	 */
	public function getRelated(int $product_id): array {
		$mapper = new ProductMapper();
		return $mapper->getRelated(
			$product_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id'),
			(int)$this->config->get('config_customer_group_id')
		);
	}

	/**
	 * Get Specials
	 *
	 * Get the record of the product special records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> special records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $results = $this->model_catalog_product->getSpecials();
	 */
	public function getSpecials(array $data = []): array {
		$mapper = new ProductMapper();
		return $mapper->getSpecials(
			$data,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id'),
			(int)$this->config->get('config_customer_group_id')
		);
	}

	/**
	 * Get Total Specials
	 *
	 * Get the total number of total product special records in the database.
	 *
	 * @return int total number of special records
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $special_total = $this->model_catalog_product->getTotalSpecials();
	 */
	public function getTotalSpecials(): int {
		$mapper = new ProductMapper();
		return $mapper->getTotalSpecials(
			(int)$this->config->get('config_customer_group_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Add Report
	 *
	 * Create a new product report record in the database.
	 *
	 * @param int    $product_id primary key of the product record
	 * @param string $ip
	 * @param string $country
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('catalog/product');
	 *
	 * $this->model_catalog_product->addReport($product_id, $ip, $country);
	 */
	public function addReport(int $product_id, string $ip, string $country = ''): void {
		$mapper = new ProductMapper();
		$mapper->addReport($product_id, (int)$this->config->get('config_store_id'), $ip, $country);
	}
}
