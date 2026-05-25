<?php
namespace Opencart\Catalog\Controller\Event;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ActivityRepository;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;

/**
 * Class Activity
 *
 * @package Opencart\Catalog\Controller\Event
 */
class Activity extends BaseController {
	/**
	 * Add Customer
	 *
	 * catalog/model/account/customer/addCustomer/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function addCustomer(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			$activity_data = [
				'customer_id' => $output,
				'name'        => $args[0]['firstname'] . ' ' . $args[0]['lastname']
			];

			$activityRepository->addActivity('register', $activity_data);
		}
	}

	/**
	 * Edit Customer
	 *
	 * catalog/model/account/customer/editCustomer/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function editCustomer(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			$activity_data = [
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			];

			$activityRepository->addActivity('edit', $activity_data);
		}
	}

	/**
	 * Edit Password
	 *
	 * catalog/model/account/customer/editPassword/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function editPassword(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			if ($this->customer->isLogged()) {
				$activity_data = [
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
				];

				$activityRepository->addActivity('password', $activity_data);
			} else {
				$customerRepository = $this->getRepository(CustomerRepository::class);
				$customer_info = $customerRepository->findByEmail($args[0]);

				if ($customer_info) {
					$customerId = is_object($customer_info) ? $customer_info->getId() : $customer_info['customer_id'];
					$name = (is_object($customer_info) ? $customer_info->getFirstname() : $customer_info['firstname']) . ' ' . (is_object($customer_info) ? $customer_info->getLastname() : $customer_info['lastname']);

					$activity_data = [
						'customer_id' => $customerId,
						'name'        => $name
					];

					$activityRepository->addActivity('reset', $activity_data);
				}
			}
		}
	}

	/**
	 * Login
	 *
	 * catalog/model/account/customer/deleteLoginAttempts/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function login(string &$route, array &$args, &$output): void {
		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'account/login' || $this->request->get['route'] == 'checkout/login.save') && $this->config->get('config_customer_activity')) {
			$customerRepository = $this->getRepository(CustomerRepository::class);
			$customer_info = $customerRepository->findByEmail($args[0]);

			if ($customer_info) {
				$activityRepository = $this->getRepository(ActivityRepository::class);

				$customerId = is_object($customer_info) ? $customer_info->getId() : $customer_info['customer_id'];
				$name = (is_object($customer_info) ? $customer_info->getFirstname() : $customer_info['firstname']) . ' ' . (is_object($customer_info) ? $customer_info->getLastname() : $customer_info['lastname']);

				$activity_data = [
					'customer_id' => $customerId,
					'name'        => $name
				];

				$activityRepository->addActivity('login', $activity_data);
			}
		}
	}

	/**
	 * Forgotten
	 *
	 * catalog/model/account/customer/addToken/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function forgotten(string &$route, array &$args, &$output): void {
		// Customer
		if (isset($this->request->get['route']) && $this->request->get['route'] == 'account/forgotten' && $this->config->get('config_customer_activity')) {
			$customerRepository = $this->getRepository(CustomerRepository::class);
			$customer_info = $customerRepository->findByEmail($args[0]);

			if ($customer_info) {
				$activityRepository = $this->getRepository(ActivityRepository::class);

				$customerId = is_object($customer_info) ? $customer_info->getId() : $customer_info['customer_id'];
				$name = (is_object($customer_info) ? $customer_info->getFirstname() : $customer_info['firstname']) . ' ' . (is_object($customer_info) ? $customer_info->getLastname() : $customer_info['lastname']);

				$activity_data = [
					'customer_id' => $customerId,
					'name'        => $name
				];

				$activityRepository->addActivity('forgotten', $activity_data);
			}
		}
	}

	/**
	 * Add Transaction
	 *
	 * catalog/model/account/customer/addTransaction/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function addTransaction(string &$route, array &$args, &$output): void {
		// Customer
		if ($this->config->get('config_customer_activity')) {
			$customerRepository = $this->getRepository(CustomerRepository::class);
			$customer_info = $customerRepository->find($args[0]);

			if ($customer_info) {
				$activityRepository = $this->getRepository(ActivityRepository::class);

				$customerId = is_object($customer_info) ? $customer_info->getId() : $customer_info['customer_id'];
				$name = (is_object($customer_info) ? $customer_info->getFirstname() : $customer_info['firstname']) . ' ' . (is_object($customer_info) ? $customer_info->getLastname() : $customer_info['lastname']);

				$activity_data = [
					'customer_id' => $customerId,
					'name'        => $name,
					'order_id'    => $args[3]
				];

				$activityRepository->addActivity('transaction', $activity_data);
			}
		}
	}

	/**
	 * Add Affiliate
	 *
	 * catalog/model/account/affiliate/addAffiliate/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function addAffiliate(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			$activity_data = [
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			];

			$activityRepository->addActivity('affiliate_add', $activity_data);
		}
	}

	/**
	 * Edit Affiliate
	 *
	 * catalog/model/account/affiliate/editAffiliate/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function editAffiliate(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			$activity_data = [
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			];

			$activityRepository->addActivity('affiliate_edit', $activity_data);
		}
	}

	/**
	 * Add Address
	 *
	 * catalog/model/account/address/addAddress/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function addAddress(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			$activity_data = [
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			];

			$activityRepository->addActivity('address_add', $activity_data);
		}
	}

	/**
	 * Edit Address
	 *
	 * catalog/model/account/address/editAddress/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function editAddress(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			$activity_data = [
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			];

			$activityRepository->addActivity('address_edit', $activity_data);
		}
	}

	/**
	 * Delete Address
	 *
	 * catalog/model/account/address/deleteAddress/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function deleteAddress(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity')) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			$activity_data = [
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			];

			$activityRepository->addActivity('address_delete', $activity_data);
		}
	}

	/**
	 * Add Return
	 *
	 * catalog/model/account/returns/addReturn/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @return void
	 */
	public function addReturn(string &$route, array &$args, &$output): void {
		if ($this->config->get('config_customer_activity') && $output) {
			$activityRepository = $this->getRepository(ActivityRepository::class);

			if ($this->customer->isLogged()) {
				$activity_data = [
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'return_id'   => $output
				];

				$activityRepository->addActivity('return_account', $activity_data);
			} else {
				$activity_data = [
					'name'      => $args[0]['firstname'] . ' ' . $args[0]['lastname'],
					'return_id' => $output
				];

				$activityRepository->addActivity('return_guest', $activity_data);
			}
		}
	}

	/**
	 * Add History
	 *
	 * catalog/model/checkout/order/addHistory/before
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 *
	 * @return void
	 */
	public function addHistory(string &$route, array &$args): void {
		if ($this->config->get('config_customer_activity')) {
			// If the last order status id returns 0, and the new order status is not, then we record it as new order
			$orderRepository = $this->getRepository(OrderRepository::class);
			$order_info = $orderRepository->getOrder($args[0]);

			if ($order_info && !$order_info['order_status_id'] && $args[1]) {
				$activityRepository = $this->getRepository(ActivityRepository::class);

				if ($order_info['customer_id']) {
					$activity_data = [
						'customer_id' => $order_info['customer_id'],
						'name'        => $order_info['firstname'] . ' ' . $order_info['lastname'],
						'order_id'    => $args[0]
					];

					$activityRepository->addActivity('order_account', $activity_data);
				} else {
					$activity_data = [
						'name'     => $order_info['firstname'] . ' ' . $order_info['lastname'],
						'order_id' => $args[0]
					];

					$activityRepository->addActivity('order_guest', $activity_data);
				}
			}
		}
	}
}
