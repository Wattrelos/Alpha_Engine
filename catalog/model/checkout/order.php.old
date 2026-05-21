<?php
namespace Opencart\Catalog\Model\Checkout;

use Alpha\Mappers\OrderMapper;

/**
 * Class Order
 *
 * Can be called using $this->load->model('checkout/order');
 *
 * @package Opencart\Catalog\Model\Checkout
 */
class Order extends \Opencart\System\Engine\Model {
	/**
	 * Add Order
	 *
	 * Create a new order record in the database.
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int returns the primary key of the new order record
	 *
	 * @example
	 *
	 * $order_data = [
	 *     'subscription_id'        => 1,
	 *     'invoice_prefix'         => 'INV-',
	 *     'store_id'               => 1,
	 *     'store_name'             => 'Your Store',
	 *     'store_url'              => '',
	 *     'customer_id'            => 1,
	 *     'customer_group_id'      => 1,
	 *     'firstname'              => 'John',
	 *     'lastname'               => 'Doe',
	 *     'email'                  => 'demo@opencart.com',
	 *     'telephone'              => '1234567890',
	 *     'custom_field'           => [],
	 *     'payment_address_id'     => 1,
	 *     'payment_firstname'      => 'John',
	 *     'payment_lastname'       => 'Doe',
	 *     'payment_company'        => '',
	 *     'payment_address_1'      => 'Address 1',
	 *     'payment_address_2'      => 'Address 2',
	 *     'payment_city'           => '',
	 *     'payment_postcode'       => '',
	 *     'payment_country'        => 'United Kingdom',
	 *     'payment_country_id'     => 222,
	 *     'payment_zone'           => 'Lancashire',
	 *     'payment_zone_id'        => 3563,
	 *     'payment_address_format' => '',
	 *     'payment_custom_field'   => [],
	 *     'payment_method'         => [
	 *         'name' => 'Payment Name',
	 *         'code' => 'Payment Code'
	 *      ],
	 *      'shipping_address_id'     => 1,
	 *      'shipping_firstname'      => 'John',
	 *      'shipping_lastname'       => 'Doe',
	 *      'shipping_company'        => '',
	 *      'shipping_address_1'      => 'Address 1',
	 *      'shipping_address_2'      => 'Address 2',
	 *      'shipping_city'           => '',
	 *      'shipping_postcode'       => '',
	 *      'shipping_country'        => 'United Kingdom',
	 *      'shipping_country_id'     => 222,
	 *      'shipping_zone'           => 'Lancashire',
	 *      'shipping_zone_id'        => 3563,
	 *      'shipping_address_format' => '',
	 *      'shipping_custom_field'   => [],
	 *      'shipping_method'         => [
	 *          'name' => 'Shipping Name',
	 *          'code' => 'Shipping Code'
	 *      ],
	 *      'comment'         => '',
	 *      'total'           => '0.0000',
	 *      'affiliate_id'    => 0,
	 *      'commission'      => '0.0000',
	 *      'marketing_id'    => 0,
	 *      'tracking'        => '',
	 *      'language_id'     => 1,
	 *      'language_code'   => 'en-gb',
	 *      'currency_id'     => 1,
	 *      'currency_code'   => 'USD',
	 *      'currency_value'  => '1.00000000',
	 *      'ip'              => '',
	 *      'forwarded_ip'    => '',
	 *      'user_agent'      => '',
	 *      'accept_language' => ''
	 * ];
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->addOrder($order_data);
	 */
	public function addOrder(array $data): int {
		$mapper = new OrderMapper();
		
		$order_id = $mapper->create($data);

		// Products
		if (!empty($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->model_checkout_order->addProduct($order_id, $product);
			}
		}

		// Totals
		if (!empty($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->model_checkout_order->addTotal($order_id, $total);
			}
		}

		return $order_id;
	}

	/**
	 * Edit Order
	 *
	 * Edit order record in the database.
	 *
	 * @param int                  $order_id primary key of the order record
	 * @param array<string, mixed> $data     array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $order_data = [
	 *     'subscription_id'        => 1,
	 *     'invoice_prefix'         => 'INV-',
	 *     'store_id'               => 1,
	 *     'store_name'             => 'Your Store',
	 *     'store_url'              => '',
	 *     'customer_id'            => 1,
	 *     'customer_group_id'      => 1,
	 *     'firstname'              => 'John',
	 *     'lastname'               => 'Doe',
	 *     'email'                  => 'demo@opencart.com',
	 *     'telephone'              => '1234567890',
	 *     'custom_field'           => [],
	 *     'payment_address_id'     => 1,
	 *     'payment_firstname'      => 'John',
	 *     'payment_lastname'       => 'Doe',
	 *     'payment_company'        => '',
	 *     'payment_address_1'      => 'Address 1',
	 *     'payment_address_2'      => 'Address 2',
	 *     'payment_city'           => '',
	 *     'payment_postcode'       => '',
	 *     'payment_country'        => 'United Kingdom',
	 *     'payment_country_id'     => 222,
	 *     'payment_zone'           => 'Lancashire',
	 *     'payment_zone_id'        => 3563,
	 *     'payment_address_format' => '',
	 *     'payment_custom_field'   => [],
	 *     'payment_method'         => [
	 *         'name' => 'Payment Name',
	 *         'code' => 'Payment Code'
	 *      ],
	 *      'shipping_address_id'     => 1,
	 *      'shipping_firstname'      => 'John',
	 *      'shipping_lastname'       => 'Doe',
	 *      'shipping_company'        => '',
	 *      'shipping_address_1'      => 'Address 1',
	 *      'shipping_address_2'      => 'Address 2',
	 *      'shipping_city'           => '',
	 *      'shipping_postcode'       => '',
	 *      'shipping_country'        => 'United Kingdom',
	 *      'shipping_country_id'     => 222,
	 *      'shipping_zone'           => 'Lancashire',
	 *      'shipping_zone_id'        => 3563,
	 *      'shipping_address_format' => '',
	 *      'shipping_custom_field'   => [],
	 *      'shipping_method'         => [
	 *          'name' => 'Shipping Name',
	 *          'code' => 'Shipping Code'
	 *      ],
	 *      'comment'         => '',
	 *      'total'           => '0.0000',
	 *      'affiliate_id'    => 0,
	 *      'commission'      => '0.0000',
	 *      'marketing_id'    => 0,
	 *      'tracking'        => '',
	 *      'language_id'     => 1,
	 *      'language_code'   => 'en-gb',
	 *      'currency_id'     => 1,
	 *      'currency_code'   => 'USD',
	 *      'currency_value'  => '1.00000000',
	 *      'ip'              => '',
	 *      'forwarded_ip'    => '',
	 *      'user_agent'      => '',
	 *      'accept_language' => ''
	 * ];
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->editOrder($order_id, $order_data);
	 */
	public function editOrder(int $order_id, array $data): void {
		// 1. Void the order first
		$this->addHistory($order_id, (int)$this->config->get('config_void_status_id'));

		$order_info = $this->getOrder($order_id);

		if ($order_info) {
			// 2. Merge the old order data with the new data
			foreach ($order_info as $key => $value) {
				if (!isset($data[$key])) {
					$data[$key] = $value;
				}
			}

			$mapper = new OrderMapper();
			$mapper->update($order_id, $data);

			// Products
			$this->model_checkout_order->deleteProducts($order_id);

			if (!empty($data['products'])) {
				foreach ($data['products'] as $product) {
					$this->model_checkout_order->addProduct($order_id, $product);
				}
			}

			// Totals
			$this->model_checkout_order->deleteTotals($order_id);

			if (!empty($data['totals'])) {
				foreach ($data['totals'] as $total) {
					$this->model_checkout_order->addTotal($order_id, $total);
				}
			}
		}
	}

	/**
	 * Edit Transaction ID
	 *
	 * Edit order transaction record in the database.
	 *
	 * @param int    $order_id       primary key of the order record
	 * @param string $transaction_id primary key of the transaction record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->editTransactionId($order_id, $transaction_id);
	 */
	public function editTransactionId(int $order_id, string $transaction_id): void {
		$mapper = new OrderMapper();

		$mapper->updateTransactionId($order_id, $transaction_id);
	}

	/**
	 * Edit Order Status ID
	 *
	 * Edit order status record in the database.
	 *
	 * @param int $order_id        primary key of the order record
	 * @param int $order_status_id primary key of the order status record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->editOrderStatusId($order_id, $order_status_id);
	 */
	public function editOrderStatusId(int $order_id, int $order_status_id): void {
		$mapper = new OrderMapper();
		
		$mapper->updateStatus($order_id, $order_status_id);
	}

	/**
	 * Edit Comment
	 *
	 * Edit order comment record in the database.
	 *
	 * @param int    $order_id primary key of the order record
	 * @param string $comment
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->editComment($order_id, $comment);
	 */
	public function editComment(int $order_id, string $comment): void {
		$mapper = new OrderMapper();

		$mapper->updateComment($order_id, $comment);
	}

	/**
	 * Delete Order
	 *
	 * Delete order record in the database.
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->deleteOrder($order_id);
	 */
	public function deleteOrder(int $order_id): void {
		// Void the order first so it restocks products
		$this->addHistory($order_id, (int)$this->config->get('config_void_status_id'));

		$mapper = new OrderMapper();
		$mapper->delete($order_id);

		// Transaction
		$this->load->model('account/transaction');

		$this->model_account_transaction->deleteTransactionsByOrderId($order_id);

		// Reward
		$this->load->model('account/reward');

		$this->model_account_reward->deleteRewardsByOrderId($order_id);
	}

	/**
	 * Get Order
	 *
	 * Get the record of the order record in the database.
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<string, mixed> order record that has order ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $order_info = $this->model_checkout_order->getOrder($order_id);
	 */
	public function getOrder(int $order_id): array {
		$mapper = new OrderMapper();
		
		$order_data = $mapper->getOrder($order_id);

		if ($order_data) {
			// O Mapper já traz order_status, country names e zone codes via JOIN.
			// Apenas processamos os campos que são armazenados como JSON no banco.
			$order_data['custom_field'] = $order_data['custom_field'] ? json_decode($order_data['custom_field'], true) : [];
			
			$json_fields = [
				'payment_custom_field', 
				'payment_method', 
				'shipping_custom_field', 
				'shipping_method'
			];

			foreach ($json_fields as $field) {
				$order_data[$field] = $order_data[$field] ? json_decode($order_data[$field], true) : [];
			}

			$order_data['products'] = $this->getProducts($order_id);
			$order_data['totals'] = $this->getTotals($order_id);

			return $order_data;
		}

		return [];
	}

	/**
	 * Add Product
	 *
	 * Create a new order product record in the database.
	 *
	 * @param int                  $order_id primary key of the order record
	 * @param array<string, mixed> $data     array of data
	 *
	 * @return int returns the primary key of the new order product record
	 *
	 * @example
	 *
	 * $order_product_data = [
	 *     'product_id' => 1,
	 *     'master_id'  => 0,
	 *     'name'       => 'Product Name',
	 *     'model'      => 'Product Model',
	 *     'quantity'   => 1,
	 *     'price'      => 0.0000,
	 *     'total'      => 0.0000,
	 *     'tax'        => 0.0000,
	 *     'reward'     => 0
	 * ];
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->addProduct($order_id, $order_product_data);
	 */
	public function addProduct(int $order_id, array $data): int {
		$mapper = new OrderMapper();
		$order_product_id = $mapper->addProduct($order_id, $data);

		if (!empty($data['option'])) {
			foreach ($data['option'] as $option) {
				$this->model_checkout_order->addOption($order_id, $order_product_id, $option);
			}
		}

		// If subscription add details
		if (!empty($data['subscription'])) {
			$this->model_checkout_order->addSubscription($order_id, $order_product_id, $data['subscription'] + ['quantity' => $data['quantity']]);
		}

		return $order_product_id;
	}

	/**
	 * Delete Products
	 *
	 * Delete order product record in the database.
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->deleteProducts($order_id);
	 */
	public function deleteProducts(int $order_id): void {
		$mapper = new OrderMapper();

		$mapper->deleteProducts($order_id);
	}

	/**
	 * Get Product
	 *
	 * Get the record of the order product record in the database.
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<int, array<string, mixed>> product record that has order ID, order product ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $order_product = $this->model_checkout_order->getProduct($order_id, $order_product_id);
	 */
	public function getProduct(int $order_id, int $order_product_id): array {
		$mapper = new OrderMapper();
		return $mapper->getProduct($order_id, $order_product_id);
	}

	/**
	 * Get Products
	 *
	 * Get the record of the order product records in the database.
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<int, array<string, mixed>> product records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $order_products = $this->model_checkout_order->getProducts($order_id);
	 */
	public function getProducts(int $order_id): array {
		$mapper = new OrderMapper();

		return $mapper->getOrderProducts($order_id);
	}

	/**
	 * Add Option
	 *
	 * Create a new order option record in the database.
	 *
	 * @param int                  $order_id         primary key of the order record
	 * @param int                  $order_product_id primary key of the order product record
	 * @param array<string, mixed> $data             array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $order_option_data = [
	 *     'product_option_id'       => 1,
	 *     'product_option_value_id' => 1,
	 *     'name'                    => 'Option Name',
	 *     'value'                   => 'Option Value',
	 *     'type'                    => 'radio'
	 * ];
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->addOption($order_id, $order_product_id, $order_option_data);
	 */
	public function addOption(int $order_id, int $order_product_id, array $data): void {
		$mapper = new OrderMapper();
		$mapper->addOption($order_id, $order_product_id, $data);
	}

	/**
	 * Delete Options
	 *
	 * Delete order option records in the database.
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->deleteOptions($order_id);
	 */
	public function deleteOptions(int $order_id): void {
		$mapper = new OrderMapper();

		$mapper->deleteOptions($order_id);
	}

	/**
	 * Get Options
	 *
	 * Get the record of the order option records in the database.
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<int, array<string, mixed>> option records that have order ID, order product ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $order_options = $this->model_checkout_order->getOptions($order_id, $order_product_id);
	 */
	public function getOptions(int $order_id, int $order_product_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOptions($order_id, $order_product_id);
	}

	/**
	 * Add Subscription
	 *
	 * Create a order subscription record in the database.
	 *
	 * @param int                  $order_id         primary key of the order record
	 * @param int                  $order_product_id primary key of the order product record
	 * @param array<string, mixed> $data             array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $order_subscription_data = [
	 *     'product_id'           => 1,
	 *     'subscription_plan_id' => 1,
	 *     'trial_price'          => 0.0000,
	 *     'trial_tax'            => 0.0000,
	 *     'trial_frequency'      => 'month',
	 *     'trial_cycle'          => 5,
	 *     'trial_duration'       => 1,
	 *     'trial_status'         => 1,
	 *     'price'                => 0.0000,
	 *     'tax'                  => 0.0000,
	 *     'frequency'            => 'month',
	 *     'cycle'                => 5,
	 *     'duration'             => 1
	 * ];
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->addSubscription($order_id, $order_product_id, $order_subscription_data);
	 */
	public function addSubscription(int $order_id, int $order_product_id, array $data): void {
		$mapper = new OrderMapper();
		$mapper->addOrderSubscription($order_id, $order_product_id, $data);
	}

	/**
	 * Delete Subscription
	 *
	 * Delete order subscription record in the database.
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->deleteSubscription($order_id);
	 */
	public function deleteSubscription(int $order_id): void {
		$mapper = new OrderMapper();

		$mapper->deleteSubscriptions($order_id);
	}

	/**
	 * Get Subscription
	 *
	 * Get the record of the order subscription record in the database.
	 *
	 * @param int $order_id         primary key of the order record
	 * @param int $order_product_id primary key of the order product record
	 *
	 * @return array<string, mixed> subscription record that have order ID, order product ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $order_subscription_info = $this->model_checkout_order->getSubscription($order_id, $order_product_id);
	 */
	public function getSubscription(int $order_id, int $order_product_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOrderSubscription($order_id, $order_product_id);
	}

	/**
	 * Get Subscriptions
	 *
	 * Get the record of the order subscription records in the database.
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<int, array<string, mixed>> subscription records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $results = $this->model_checkout_order->getSubscriptions($order_id);
	 */
	public function getSubscriptions(int $order_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOrderSubscriptions($order_id);
	}

	/**
	 * Get Total Orders By Subscription ID
	 *
	 * Get the total number of total orders by subscription records in the database.
	 *
	 * @param int $subscription_id primary key of the subscription record
	 *
	 * @return int total number of order that have subscription ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $subscription_total = $this->model_checkout_order->getTotalOrdersBySubscriptionId($subscription_id);
	 */
	public function getTotalOrdersBySubscriptionId(int $subscription_id): int {
		$mapper = new OrderMapper();
		return $mapper->countOrdersBySubscriptionId($subscription_id, (int)$this->customer->getId());
	}

	/**
	 * Add Total
	 *
	 * Create a new order total record in the database.
	 *
	 * @param int                  $order_id primary key of the order record
	 * @param array<string, mixed> $data     array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $order_total_data = [
	 *     'extension' => '',
	 *     'code'      => '',
	 *     'title'     => 'Order Total Title',
	 *     'value'     => 0.0000,
	 *     'sort_order'
	 * ];
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->addTotal($order_id, $order_total_data);
	 */
	public function addTotal(int $order_id, array $data): void {
		$mapper = new OrderMapper();
		$mapper->addTotal($order_id, $data);
	}

	/**
	 * Delete Totals
	 *
	 * Delete order total records in the database.
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->deleteTotals($order_id);
	 */
	public function deleteTotals(int $order_id): void {
		$mapper = new OrderMapper();

		$mapper->deleteTotals($order_id);
	}

	/**
	 * Get Totals
	 *
	 * Get the record of the order total records in the database.
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return array<int, array<string, mixed>> total records that have order ID
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $order_totals = $this->model_checkout_order->getTotals($order_id);
	 */
	public function getTotals(int $order_id): array {
		$mapper = new OrderMapper();

		return $mapper->getOrderTotals($order_id);
	}

	/**
	 * Add History
	 *
	 * Create a new order history record in the database.
	 *
	 * @param int    $order_id        primary key of the order record
	 * @param int    $order_status_id primary key of the order status record
	 * @param string $comment
	 * @param bool   $notify
	 * @param bool   $override
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->addHistory($order_id, $order_status_id, $comment, $notify, $override);
	 */
	public function addHistory(int $order_id, int $order_status_id, string $comment = '', bool $notify = false, bool $override = false): void {
		$mapper = new OrderMapper();

		$order_info = $this->getOrder($order_id);

		if ($order_info) {
			// Load subscription model
			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomer($order_info['customer_id']);

			// Fraud Detection Enable / Disable
			if ($customer_info && $customer_info['safe']) {
				$safe = true;
			} else {
				$safe = false;
			}

			// Only do the fraud check if the customer is not on the safe list and the order status is changing into the complete or process order status
			if (!$safe && !$override && in_array($order_status_id, (array)$this->config->get('config_processing_status') + (array)$this->config->get('config_complete_status'))) {
				// Anti-Fraud
				$this->load->model('setting/extension');

				$extensions = $this->model_setting_extension->getExtensionsByType('fraud');

				foreach ($extensions as $extension) {
					if ($this->config->get('fraud_' . $extension['code'] . '_status')) {
						$this->load->model('extension/' . $extension['extension'] . '/fraud/' . $extension['code']);

						$key = 'model_extension_' . $extension['extension'] . '_fraud_' . $extension['code'];

						if (isset($this->{$key}->check)) {
							$fraud_status_id = $this->{$key}->check($order_info);

							if ($fraud_status_id) {
								$order_status_id = $fraud_status_id;
							}
						}
					}
				}
			}

			// Products
			$order_products = $this->getProducts($order_id);

			// Subscriptions
			$order_subscriptions = $this->getSubscriptions($order_id);

			// Totals
			$order_totals = $this->getTotals($order_id);

			// If current order status is not processing or complete but new status is processing or complete then commence completing the order
			if (!in_array($order_info['order_status_id'], (array)$this->config->get('config_processing_status') + (array)$this->config->get('config_complete_status')) && in_array($order_status_id, (array)$this->config->get('config_processing_status') + (array)$this->config->get('config_complete_status'))) {
				// Redeem coupon and reward points
				foreach ($order_totals as $order_total) {
					$this->load->model('extension/' . $order_total['extension'] . '/total/' . $order_total['code']);

					$key = 'model_extension_' . $order_total['extension'] . '_total_' . $order_total['code'];

					if (isset($this->{$key}->confirm)) {
						// Confirm coupon and reward points
						$fraud_status_id = $this->{$key}->confirm($order_info, $order_total);

						// If the balance on the coupon and reward points is not enough to cover the transaction or has already been used then the fraud order status is returned.
						if ($fraud_status_id) {
							$order_status_id = $fraud_status_id;
						}
					}
				}

				foreach ($order_products as $order_product) {
					// Stock subtraction
					$mapper->subtractStock((int)$order_product['product_id'], (int)$order_product['quantity']);

					// Stock subtraction from master product
					if ($order_product['master_id']) {
						$mapper->subtractStock((int)$order_product['master_id'], (int)$order_product['quantity']);
					}

					$order_options = $this->getOptions($order_id, (int)$order_product['order_product_id']);

					foreach ($order_options as $order_option) {
						$mapper->subtractOptionStock((int)$order_option['product_option_value_id'], (int)$order_product['quantity']);
					}
				}
			}

			// If order status becomes complete status
			if (!in_array($order_info['order_status_id'], (array)$this->config->get('config_complete_status')) && in_array($order_status_id, (array)$this->config->get('config_complete_status'))) {
				// Affiliate add commission if complete status
				if ($order_info['affiliate_id'] && $this->config->get('config_affiliate_auto')) {
					// Add commission if sale is linked to affiliate referral.
					$this->load->model('account/customer');

					if (!$this->model_account_customer->getTotalTransactionsByOrderId($order_id)) {
						$this->model_account_customer->addTransaction($order_info['affiliate_id'], $this->language->get('text_order_id') . ' #' . $order_id, $order_info['commission'], $order_id);
					}
				}

				// Add subscription
				$this->load->model('checkout/subscription');

				foreach ($order_subscriptions as $key => $order_subscription) {
					$subscription_product_data = [];

					foreach ($order_subscriptions as $subscription) {
						if ($subscription['subscription_plan_id'] == $order_subscription['subscription_plan_id']) {
							$subscription_product_data[] = [
								'option'      => $this->model_checkout_order->getOptions($order_id, $order_subscription['order_product_id']),
								'trial_price' => $order_subscription['trial_price'],
								'trial_tax'   => $order_subscription['trial_tax'],
								'price'       => $order_subscription['price'],
								'tax'         => $order_subscription['tax']
							] + $order_subscription;

							unset($order_subscriptions[$key]);
						}
					}

					$subscription_data = [
						'trial_price'          => array_sum(array_column($subscription_product_data, 'trial_price')),
						'trial_tax'            => array_sum(array_column($subscription_product_data, 'trial_tax')),
						'price'                => array_sum(array_column($subscription_product_data, 'price')),
						'tax'                  => array_sum(array_column($subscription_product_data, 'tax')),
						'subscription_product' => $subscription_product_data,
						'language'             => $order_info['language_code'],
						'currency'             => $order_info['currency_code']
					] + $order_info + $order_subscription;

					$subscription_info = $this->model_checkout_subscription->getProductByOrderProductId($order_id, $order_subscription['order_product_id']);

					if (!$subscription_info) {
						$subscription_id = $this->model_checkout_subscription->addSubscription($subscription_data);
					} else {
						$this->model_checkout_subscription->editSubscription($subscription_info['subscription_id'], $subscription_data);

						$subscription_id = $subscription_info['subscription_id'];
					}

					// Add history and set active subscription
					$this->model_checkout_subscription->addHistory($subscription_id, (int)$this->config->get('config_subscription_active_status_id'));
				}
			}

			// If old order status is the processing or complete status but new status is not then commence restock, and remove coupon and reward history
			if (in_array($order_info['order_status_id'], (array)$this->config->get('config_processing_status') + (array)$this->config->get('config_complete_status')) && !in_array($order_status_id, (array)$this->config->get('config_processing_status') + (array)$this->config->get('config_complete_status'))) {
				// Restock
				foreach ($order_products as $order_product) {
					$mapper->restock((int)$order_product['product_id'], (int)$order_product['quantity']);

					// Restock the master product stock level if product is a variant
					if ($order_product['master_id']) {
						$mapper->restock((int)$order_product['master_id'], (int)$order_product['quantity']);
					}

					$order_options = $this->getOptions($order_id, (int)$order_product['order_product_id']);

					foreach ($order_options as $order_option) {
						$mapper->restockOption((int)$order_option['product_option_value_id'], (int)$order_product['quantity']);
					}
				}

				// Remove coupon and reward points history
				foreach ($order_totals as $order_total) {
					$this->load->model('extension/' . $order_total['extension'] . '/total/' . $order_total['code']);

					$key = 'model_extension_' . $order_total['extension'] . '_total_' . $order_total['code'];

					if (isset($this->{$key}->unconfirm)) {
						$this->{$key}->unconfirm($order_info);
					}
				}
			}

			// If order status is no longer complete status
			if (in_array($order_info['order_status_id'], (array)$this->config->get('config_complete_status')) && !in_array($order_status_id, (array)$this->config->get('config_complete_status'))) {
				// Suspend subscription
				$this->load->model('checkout/subscription');

				foreach ($order_products as $order_product) {
					// Subscription status set to suspend
					$subscription_info = $this->model_checkout_subscription->getProductByOrderProductId($order_id, $order_product['order_product_id']);

					if ($subscription_info) {
						// Add history and set suspended subscription
						$this->model_checkout_subscription->addHistory($subscription_info['subscription_id'], (int)$this->config->get('config_subscription_suspended_status_id'));
					}
				}

				// Affiliate remove commission.
				if ($order_info['affiliate_id']) {
					$this->load->model('account/transaction');

					$this->model_account_transaction->deleteTransaction($order_info['customer_id'], $order_id);
				}
			}

			// Update the DB with the new statuses
			$this->editOrderStatusId($order_id, $order_status_id);

			$mapper->addHistory($order_id, $order_status_id, $comment, $notify);

			$this->cache->delete('product');
		}
	}

	/**
	 * Delete Order Histories
	 *
	 * Delete order history records in the database.
	 *
	 * @param int $order_id primary key of the order record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('checkout/order');
	 *
	 * $this->model_checkout_order->deleteHistories($order_id);
	 */
	public function deleteHistories(int $order_id): void {
		$mapper = new OrderMapper();

		$mapper->deleteHistories($order_id);
	}
}
