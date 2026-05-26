<?php
namespace Opencart\Catalog\Controller\Account;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\OrderStatusRepository;
use Alpha\Model\Domain\Repositories\SubscriptionRepository;
use Alpha\Model\Domain\Repositories\UploadRepository;

/**
 * Order Controller - Modernizado para Alpha Engine.
 */
class Order extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$data = [];
		$this->loadLanguageData('account/order', $data);

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language'));

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
			'href' => $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . $url)
		];

		$limit = 10;

		$data['orders'] = [];

		$orderRepository = $this->getRepository(OrderRepository::class);
		$orderStatusRepository = $this->getRepository(OrderStatusRepository::class);

		$results = $orderRepository->getOrders(($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$order_status_info = $orderStatusRepository->getOrderStatus($result['order_status_id']);

			if ($order_status_info) {
				$order_status = $order_status_info['name'];
			} else {
				$order_status = '';
			}

			$data['orders'][] = [
				'status'        => $order_status,
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'product_total' => $orderRepository->getTotalProductsByOrderId($result['order_id']),
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'view'          => $this->url->link('account/order.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $result['order_id']),
			] + $result;
		}

		$order_total = $orderRepository->getTotalOrders();

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $order_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($order_total - $limit)) ? $order_total : ((($page - 1) * $limit) + $limit), $order_total, ceil($order_total / $limit));

		$data['continue'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

		// Alpha Engine: Renderização envelopada
		$this->render('account/order_list', $data);
	}

	/**
	 * Info
	 *
	 * @return \Opencart\System\Engine\Action|null
	 */
	public function info(): ?\Opencart\System\Engine\Action {
		$data = [];
		$this->loadLanguageData('account/order', $data);

		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$orderRepository = $this->getRepository(OrderRepository::class);
		$order_info = $orderRepository->getOrder($order_id);

		if ($order_info) {
			$heading_title = sprintf($this->language->get('text_order'), $order_info['order_id']);

			$this->document->setTitle($heading_title);

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
				'href' => $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . $url)
			];

			$data['breadcrumbs'][] = [
				'text' => $heading_title,
				'href' => $this->url->link('account/order.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $order_id . $url)
			];

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['order_id'] = $order_id;

			$orderStatusRepository = $this->getRepository(OrderStatusRepository::class);
			$order_status_info = $orderStatusRepository->getOrderStatus($order_info['order_status_id']);

			if ($order_status_info) {
				$data['order_status'] = $order_status_info['name'];
			} else {
				$data['order_status'] = '';
			}

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			// Payment Address
			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{number}'  . "\n" . '{address_2}' . "\n" . '{neighborhood}' . "\n" . '{city}' . "\n" . '{zone}' . "\n" . '{country}'. "\n" . '{postcode}';
			}

			$find = [
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{number}',
				'{address_2}',
				'{neighborhood}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			];

			$replace = [
				'firstname'    => $order_info['payment_firstname'],
				'lastname'     => $order_info['payment_lastname'],
				'company'      => $order_info['payment_company'],
				'address_1'    => $order_info['payment_address_1'],
				'number'       => $order_info['payment_number'],
				'address_2'    => $order_info['payment_address_2'],
				'neighborhood' => $order_info['payment_neighborhood'],
				'city'         => $order_info['payment_city'],
				'postcode'     => $order_info['payment_postcode'],
				'zone'         => $order_info['payment_zone'],
				'zone_code'    => $order_info['payment_zone_code'],
				'country'      => $order_info['payment_country']
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

			if (isset($order_info['payment_method']['name'])) {
				$data['payment_method'] = $order_info['payment_method']['name'];
			} else {
				$data['payment_method'] = '';
			}

			// Shipping Address
			if ($order_info['shipping_method']) {
				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}'. "\n" . '{number}'  . "\n" . '{address_2}' . "\n" . '{neighborhood}' . "\n" . '{city}' . "\n" . '{zone}' . "\n" . '{country}'. "\n" . '{postcode}';
				}

				$find = [
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{number}',
					'{address_2}',
					'{neighborhood}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				];

				$replace = [
					'firstname'    => $order_info['shipping_firstname'],
					'lastname'     => $order_info['shipping_lastname'],
					'company'      => $order_info['shipping_company'],
					'address_1'    => $order_info['shipping_address_1'],
					'number'       => $order_info['shipping_number'],
					'address_2'    => $order_info['shipping_address_2'],
					'neighborhood' => $order_info['shipping_neighborhood'],
					'city'         => $order_info['shipping_city'],
					'postcode'     => $order_info['shipping_postcode'],
					'zone'         => $order_info['shipping_zone'],
					'zone_code'    => $order_info['shipping_zone_code'],
					'country'      => $order_info['shipping_country']
				];

				$data['shipping_address'] = str_replace($pattern_1, '<br/>', preg_replace($pattern_2, '<br/>', trim(str_replace($find, $replace, $format))));

				$data['shipping_method'] = $order_info['shipping_method']['name'];
			} else {
				$data['shipping_address'] = '';
				$data['shipping_method'] = '';
			}

			$subscriptionRepository = $this->getRepository(SubscriptionRepository::class);
			$uploadRepository = $this->getRepository(UploadRepository::class);

			// Products
			$data['products'] = [];

			$products = $orderRepository->getProducts($order_id);

			foreach ($products as $product) {
				$option_data = [];

				$options = $orderRepository->getOptions($order_id, $product['order_product_id']);

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

				$subscription_plan = '';

				$order_subscription_info = $orderRepository->getSubscription($order_id, $product['order_product_id']);

				if ($order_subscription_info) {
					if ($order_subscription_info['trial_status']) {
						$trial_price = $this->currency->format($order_subscription_info['trial_price'] + ($this->config->get('config_tax') ? $order_subscription_info['trial_tax'] : 0), $order_info['currency_code'], $order_info['currency_value']);
						$trial_cycle = $order_subscription_info['trial_cycle'];
						$trial_frequency = $this->language->get('text_' . $order_subscription_info['trial_frequency']);
						$trial_duration = $order_subscription_info['trial_duration'];

						$subscription_plan .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
					}

					$price = $this->currency->format($order_subscription_info['price'] + ($this->config->get('config_tax') ? $order_subscription_info['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']);
					$cycle = $order_subscription_info['cycle'];
					$frequency = $this->language->get('text_' . $order_subscription_info['frequency']);
					$duration = $order_subscription_info['duration'];

					if ($order_subscription_info['duration']) {
						$subscription_plan .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
					} else {
						$subscription_plan .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
					}

					$subscription_plan_id = $order_subscription_info['subscription_plan_id'];
				} else {
					$subscription_plan_id = 0;
				}

				$subscription_info = $subscriptionRepository->getProductByOrderProductId($order_id, $product['order_product_id']);

				if ($subscription_info) {
					$subscription = $this->url->link('account/subscription.info', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&subscription_id=' . $subscription_info['subscription_id']);
				} else {
					$subscription = '';
				}

				$data['products'][] = [
					'option'               => $option_data,
					'subscription_plan_id' => $subscription_plan_id,
					'subscription_plan'    => $subscription_plan,
					'subscription'         => $subscription,
					'price'                => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'                => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'view'                 => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id']),
					'return'               => $this->url->link('account/returns.add', 'language=' . $this->config->get('config_language') . '&order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'])
				] + $product;
			}

			// Totals
			$data['totals'] = [];

			$totals = $orderRepository->getTotals($order_id);

			foreach ($totals as $total) {
				$data['totals'][] = ['text' => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])] + $total;
			}

			$data['comment'] = nl2br($order_info['comment']);

			// History
			$data['history'] = $this->getHistory();

			$data['continue'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);

			// Alpha Engine: Renderização envelopada
			$this->render('account/order_info', $data);
			return null;
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}
	}

	/**
	 * History
	 *
	 * @return void
	 */
	public function history(): void {
		$data = [];
		$this->loadLanguageData('account/order', $data);

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language'));

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
		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (isset($this->request->get['page']) && $this->request->get['route'] == 'account/order.history') {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		if (!$this->customer->isLogged()) {
			return '';
		}

		$orderRepository = $this->getRepository(OrderRepository::class);
		$order_info = $orderRepository->getOrder($order_id);

		if (!$order_info) {
			return '';
		}

		$data['histories'] = [];

		$results = $orderRepository->getHistories($order_id);

		foreach ($results as $result) {
			$data['histories'][] = [
				'comment'    => nl2br($result['comment']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			] + $result;
		}

		$history_total = $orderRepository->getTotalHistories($order_id);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $history_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('account/order.history', 'customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $order_id . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($history_total - $limit)) ? $history_total : ((($page - 1) * $limit) + $limit), $history_total, ceil($history_total / $limit));

		// Alpha Engine: Utilização de getTemplate para resgatar a string do parcial
		return $this->getTemplate('account/order_history', $data);
	}
}
