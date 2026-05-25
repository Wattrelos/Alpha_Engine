<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\SubscriptionRepository;
use Alpha\Model\Domain\Repositories\SubscriptionStatusRepository;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Alpha\Model\Domain\Repositories\UploadRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;

/**
 * Class Subscription
 *
 * @package Opencart\Catalog\Controller\Account
 */
class Subscription extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('account/subscription');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/subscription', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . $url)
		];

		$limit = 10;

		$data['subscriptions'] = [];

		$subscriptionRepository = $this->getRepository(SubscriptionRepository::class);
		$subscriptionStatusRepository = $this->getRepository(SubscriptionStatusRepository::class);

		$results = $subscriptionRepository->getSubscriptions(($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$description = '';

			if ($result['trial_status']) {
				$trial_price = $this->currency->format($result['trial_price'], $result['currency']);
				$trial_cycle = $result['trial_cycle'];
				$trial_frequency = $this->language->get('text_' . $result['trial_frequency']);
				$trial_duration = $result['trial_duration'];

				$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
			}

			$price = $this->currency->format($result['price'], $result['currency']);
			$cycle = $result['cycle'];
			$frequency = $this->language->get('text_' . $result['frequency']);
			$duration = $result['duration'];

			if ($duration) {
				$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
			} else {
				$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
			}

			$subscription_status_info = $subscriptionStatusRepository->getSubscriptionStatus($result['subscription_status_id']);

			if ($subscription_status_info) {
				$subscription_status = $subscription_status_info['name'];
			} else {
				$subscription_status = '';
			}

			$data['subscriptions'][] = [
				'product_total' => $subscriptionRepository->getTotalProducts($result['subscription_id']),
				'description'   => $description,
				'status'        => $subscription_status,
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'view'          => $this->url->link('account/subscription.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $result['subscription_id'])
			] + $result;
		}

		$subscription_total = $subscriptionRepository->getTotalSubscriptions();

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $subscription_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('account/subscription', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($subscription_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($subscription_total - $limit)) ? $subscription_total : ((($page - 1) * $limit) + $limit), $subscription_total, ceil($subscription_total / $limit));

		$data['continue'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		$this->render('account/subscription_list', $data);
	}

	/**
	 * Info
	 *
	 * @return \Opencart\System\Engine\Action|null
	 */
	public function info(): ?\Opencart\System\Engine\Action {
		$this->load->language('account/subscription');

		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$subscriptionRepository = $this->getRepository(SubscriptionRepository::class);
		$subscription_info = $subscriptionRepository->getSubscription($subscription_id);

		if ($subscription_info) {
			$heading_title = sprintf($this->language->get('text_subscription'), $subscription_info['subscription_id']);

			$this->document->setTitle($heading_title);

			$data['heading_title'] = $heading_title;

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/subscription', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . $url)
			];

			$data['breadcrumbs'][] = [
				'text' => $heading_title,
				'href' => $this->url->link('account/subscription.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $this->request->get['subscription_id'] . $url)
			];

			$data['subscription_id'] = $subscription_info['subscription_id'];
			$data['order_id'] = $subscription_info['order_id'];

			// Payment Address
			$addressRepository = $this->getRepository(AddressRepository::class);

			$address_info = $addressRepository->getAddress($this->customer->getId(), $subscription_info['payment_address_id']);

			if ($address_info) {
				if ($address_info['address_format']) {
					$format = $address_info['address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = [
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				];

				$replace = [
					'firstname' => $address_info['firstname'],
					'lastname'  => $address_info['lastname'],
					'company'   => $address_info['company'],
					'address_1' => $address_info['address_1'],
					'address_2' => $address_info['address_2'],
					'city'      => $address_info['city'],
					'postcode'  => $address_info['postcode'],
					'zone'      => $address_info['zone'],
					'zone_code' => $address_info['zone_code'],
					'country'   => $address_info['country']
				];

				$pattern_1 = [
					"\r\n",
					"\r",
					"\n"
				];

				$pattern_2 = [
					"/\\s\\s+/",
					"/\r\r+/",
					"/\n\n+/"
				];

				$data['payment_address'] = str_replace($pattern_1, '<br/>', preg_replace($pattern_2, '<br/>', trim(str_replace($find, $replace, $format))));
			} else {
				$data['payment_address'] = '';
			}

			// Shipping Address
			$address_info = $addressRepository->getAddress($this->customer->getId(), $subscription_info['shipping_address_id']);

			if ($address_info) {
				if ($address_info['address_format']) {
					$format = $address_info['address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = [
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				];

				$replace = [
					'firstname' => $address_info['firstname'],
					'lastname'  => $address_info['lastname'],
					'company'   => $address_info['company'],
					'address_1' => $address_info['address_1'],
					'address_2' => $address_info['address_2'],
					'city'      => $address_info['city'],
					'postcode'  => $address_info['postcode'],
					'zone'      => $address_info['zone'],
					'zone_code' => $address_info['zone_code'],
					'country'   => $address_info['country']
				];

				$pattern_1 = [
					"\r\n",
					"\r",
					"\n"
				];

				$pattern_2 = [
					"/\\s\\s+/",
					"/\r\r+/",
					"/\n\n+/"
				];

				$data['shipping_address'] = str_replace($pattern_1, '<br/>', preg_replace($pattern_2, '<br/>', trim(str_replace($find, $replace, $format))));
			} else {
				$data['shipping_address'] = '';
			}

			if ($subscription_info['shipping_method']) {
				$data['shipping_method'] = $subscription_info['shipping_method']['name'];
			} else {
				$data['shipping_method'] = '';
			}

			if ($subscription_info['payment_method']) {
				$data['payment_method'] = $subscription_info['payment_method']['name'];
			} else {
				$data['payment_method'] = '';
			}

			// Product
			$data['products'] = [];

			$productRepository = $this->getRepository(ProductRepository::class);
			$uploadRepository = $this->getRepository(UploadRepository::class);
			$results = $subscriptionRepository->getProducts($subscription_id);

			foreach ($results as $result) {
				$option_data = [];

				$options = $subscriptionRepository->getOptions($result['product_id'], $result['subscription_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $uploadRepository->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = ['value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value)] + $option;
				}

				$data['products'][] = [
					'option'      => $option_data,
					'trial_price' => $this->currency->format($result['trial_price'] + ($this->config->get('config_tax') ? $result['trial_tax'] : 0), $subscription_info['currency']),
					'price'       => $this->currency->format($result['price'] + ($this->config->get('config_tax') ? $result['tax'] : 0), $subscription_info['currency']),
					'view'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				] + $result;
			}

			$data['description'] = '';

			if ($subscription_info['trial_status']) {
				$trial_price = $this->currency->format($subscription_info['trial_price'] + ($this->config->get('config_tax') ? $subscription_info['trial_tax'] : 0), $subscription_info['currency']);
				$trial_cycle = $subscription_info['trial_cycle'];
				$trial_frequency = $this->language->get('text_' . $subscription_info['trial_frequency']);
				$trial_duration = $subscription_info['trial_duration'];

				$data['description'] .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
			}

			$price = $this->currency->format($subscription_info['price'] + ($this->config->get('config_tax') ? $result['trial_tax'] : 0), $subscription_info['currency']);
			$cycle = $subscription_info['cycle'];
			$frequency = $this->language->get('text_' . $subscription_info['frequency']);
			$duration = $subscription_info['duration'];

			if ($duration) {
				$data['description'] .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
			} else {
				$data['description'] .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
			}

			$data['date_next'] = date($this->language->get('date_format_short'), strtotime($subscription_info['date_next']));
			$data['duration'] = $subscription_info['duration'];
			$data['trial_duration'] = $subscription_info['trial_duration'];
			$data['remaining'] = $subscription_info['trial_remaining'] + $subscription_info['remaining'];

			// Orders
			$data['history'] = $this->getHistory();
			$data['order'] = $this->getOrders();

			if ($subscription_info['order_id']) {
				$data['order_link'] = $this->url->link('account/order.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $subscription_info['order_id']);
			} else {
				$data['order_link'] = '';
			}

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['continue'] = $this->url->link('account/subscription', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . $url);

			$data['language'] = $this->config->get('config_language');

			$data['customer_token'] = $this->session->data['customer_token'];

			$this->render('account/subscription_info', $data);
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}

		return null;
	}

	/**
	 * Cancel Subscription
	 *
	 * @return void
	 */
	public function cancel(): void {
		$this->load->language('account/subscription');

		$json = [];

		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', 'language=' . $this->config->get('config_language'));

			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			$subscriptionRepository = $this->getRepository(SubscriptionRepository::class);
			$subscription_info = $subscriptionRepository->getSubscription($subscription_id);

			if ($subscription_info) {
				if ($subscription_info['trial_remaining']) {
					$json['error'] = sprintf($this->language->get('error_duration'), $subscription_info['trial_remaining'] + $subscription_info['remaining']);
				} elseif ($subscription_info['remaining']) {
					$json['error'] = sprintf($this->language->get('error_duration'), $subscription_info['remaining']);
				}

				if ($subscription_info['subscription_status_id'] == $this->config->get('config_subscription_canceled_status_id')) {
					$json['error'] = $this->language->get('error_canceled');
				}
			} else {
				$json['error'] = $this->language->get('error_subscription');
			}
		}

		if (!$json) {
			$subscriptionRepository->addHistory($subscription_id, (int)$this->config->get('config_subscription_canceled_status_id'));

			$json['success'] = $this->language->get('text_success');
		}

		$this->jsonResponse($json);
	}

	/**
	 * History
	 *
	 * @return void
	 */
	public function history(): void {
		$this->load->language('account/subscription');

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->response->setOutput($this->getHistory());
	}

	/**
	 * Get History
	 *
	 * @return string
	 */
	protected function getHistory(): string {
		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		if (isset($this->request->get['page']) && $this->request->get['route'] == 'account/subscription.history') {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		if (!$this->customer->isLogged()) {
			return '';
		}

		// Histories
		$data['histories'] = [];

		$subscriptionRepository = $this->getRepository(SubscriptionRepository::class);
		$results = $subscriptionRepository->getHistories($subscription_id, ($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$data['histories'][] = [
				'comment'    => nl2br($result['comment']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			] + $result;
		}

		$subscription_total = $subscriptionRepository->getTotalHistories($subscription_id);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $subscription_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('account/subscription.history', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $subscription_id . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($subscription_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($subscription_total - $limit)) ? $subscription_total : ((($page - 1) * $limit) + $limit), $subscription_total, ceil($subscription_total / $limit));

		return $this->getTemplate('account/subscription_history', $data);
	}

	/**
	 * Order
	 *
	 * @return void
	 */
	public function order(): void {
		$this->load->language('account/subscription');

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->response->setOutput($this->getOrders());
	}

	/**
	 * Get Orders
	 *
	 * @return string
	 */
	protected function getOrders(): string {
		if (isset($this->request->get['subscription_id'])) {
			$subscription_id = (int)$this->request->get['subscription_id'];
		} else {
			$subscription_id = 0;
		}

		if (isset($this->request->get['page']) && $this->request->get['route'] == 'account/subscription.order') {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (!$this->customer->isLogged()) {
			return '';
		}

		$limit = 10;

		// Order
		$data['orders'] = [];

		$orderRepository = $this->getRepository(OrderRepository::class);
		$results = $orderRepository->getOrdersBySubscriptionId($subscription_id, ($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$data['orders'][] = [
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'view'       => $this->url->link('account/subscription.order', 'customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $result['order_id'] . '&page={page}')
			] + $result;
		}

		$order_total = $orderRepository->getTotalOrdersBySubscriptionId($subscription_id);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $order_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('account/subscription.order', 'customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $subscription_id . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($order_total - $limit)) ? $order_total : ((($page - 1) * $limit) + $limit), $order_total, ceil($order_total / $limit));

		return $this->getTemplate('account/subscription_order', $data);
	}
}
