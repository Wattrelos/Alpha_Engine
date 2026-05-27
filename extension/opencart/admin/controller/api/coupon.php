<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Api;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\OrderRepository;

/**
 * Class Coupon
 *
 * @package Opencart\Admin\Controller\Extension\Opencart\Api
 */
class Coupon extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$this->load->language('extension/opencart/api/coupon');

		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$data['coupon'] = '';

		if ($order_id) {
			$orderRepository = $this->getRepository(OrderRepository::class);
			$order_totals = $orderRepository->getTotals($order_id);

			foreach ($order_totals as $order_total) {
				if (method_exists($order_total, 'getCode') && $order_total->getCode() == 'coupon') {
					$title = method_exists($order_total, 'getTitle') ? $order_total->getTitle() : '';

					// If coupon or reward points
					$start = strpos($title, '(');
					$end = strrpos($title, ')');

					if ($start !== false && $end !== false) {
						$data['coupon'] = substr($title, $start + 1, $end - ($start + 1));
					}
				}
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		return $this->load->view('extension/opencart/api/coupon', $data);
	}
}
