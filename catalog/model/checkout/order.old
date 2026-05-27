<?php
namespace Opencart\Catalog\Model\Checkout;

use Alpha\Mappers\OrderMapper;
use Alpha\Model\Domain\Repositories\CustomerAffiliateRepository;

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
	 */
	public function editTransactionId(int $order_id, string $transaction_id): void {
		$mapper = new OrderMapper();
		$mapper->updateTransactionId($order_id, $transaction_id);
	}

	/**
	 * Edit Order Status ID
	 */
	public function editOrderStatusId(int $order_id, int $order_status_id): void {
		$mapper = new OrderMapper();
		$mapper->updateStatus($order_id, $order_status_id);
	}

	/**
	 * Edit Comment
	 */
	public function editComment(int $order_id, string $comment): void {
		$mapper = new OrderMapper();
		$mapper->updateComment($order_id, $comment);
	}

	/**
	 * Delete Order
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
	 */
	public function getOrder(int $order_id): array {
		$mapper = new OrderMapper();
		$order_data = $mapper->getOrder($order_id);

		if ($order_data) {
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
	 */
	public function deleteProducts(int $order_id): void {
		$mapper = new OrderMapper();
		$mapper->deleteProducts($order_id);
	}

	/**
	 * Get Product
	 */
	public function getProduct(int $order_id, int $order_product_id): array {
		$mapper = new OrderMapper();
		return $mapper->getProduct($order_id, $order_product_id);
	}

	/**
	 * Get Products
	 */
	public function getProducts(int $order_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOrderProducts($order_id);
	}

	/**
	 * Add Option
	 */
	public function addOption(int $order_id, int $order_product_id, array $data): void {
		$mapper = new OrderMapper();
		$mapper->addOption($order_id, $order_product_id, $data);
	}

	/**
	 * Delete Options
	 */
	public function deleteOptions(int $order_id): void {
		$mapper = new OrderMapper();
		$mapper->deleteOptions($order_id);
	}

	/**
	 * Get Options
	 */
	public function getOptions(int $order_id, int $order_product_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOptions($order_id, $order_product_id);
	}

	/**
	 * Add Subscription
	 */
	public function addSubscription(int $order_id, int $order_product_id, array $data): void {
		$mapper = new OrderMapper();
		$mapper->addOrderSubscription($order_id, $order_product_id, $data);
	}

	/**
	 * Delete Subscription
	 */
	public function deleteSubscription(int $order_id): void {
		$mapper = new OrderMapper();
		$mapper->deleteSubscriptions($order_id);
	}

	/**
	 * Get Subscription
	 */
	public function getSubscription(int $order_id, int $order_product_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOrderSubscription($order_id, $order_product_id);
	}

	/**
	 * Get Subscriptions
	 */
	public function getSubscriptions(int $order_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOrderSubscriptions($order_id);
	}

	/**
	 * Get Total Orders By Subscription ID
	 */
	public function getTotalOrdersBySubscriptionId(int $subscription_id): int {
		$mapper = new OrderMapper();
		return $mapper->countOrdersBySubscriptionId($subscription_id, (int)$this->customer->getId());
	}

	/**
	 * Add Total
	 */
	public function addTotal(int $order_id, array $data): void {
		$mapper = new OrderMapper();
		$mapper->addTotal($order_id, $data);
	}

	/**
	 * Delete Totals
	 */
	public function deleteTotals(int $order_id): void {
		$mapper = new OrderMapper();
		$mapper->deleteTotals($order_id);
	}

	/**
	 * Get Totals
	 */
	public function getTotals(int $order_id): array {
		$mapper = new OrderMapper();
		return $mapper->getOrderTotals($order_id);
	}

	/**
	 * Add History
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
					// Alpha Engine: Affiliate Domain Validation
					$affiliateRepo = $this->registry->get('alpha_repository_factory')->get(CustomerAffiliateRepository::class);
					$affiliate = $affiliateRepo->find($order_info['affiliate_id']);

					// Only add commission if the affiliate exists and is active (approved)
					if ($affiliate && $affiliate->isStatus()) {
						// Add commission if sale is linked to affiliate referral.
						$this->load->model('account/customer');

						if (!$this->model_account_customer->getTotalTransactionsByOrderId($order_id)) {
							$this->model_account_customer->addTransaction($order_info['affiliate_id'], $this->language->get('text_order_id') . ' #' . $order_id, $order_info['commission'], $order_id);
						}
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

					// Alpha Engine Fix: A transação pertence ao affiliate_id e não ao customer_id (comprador).
					$this->model_account_transaction->deleteTransaction($order_info['affiliate_id'], $order_id);
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
	 */
	public function deleteHistories(int $order_id): void {
		$mapper = new OrderMapper();
		$mapper->deleteHistories($order_id);
	}
}