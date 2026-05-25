<?php
namespace Opencart\catalog\controller\api;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CustomerAffiliateRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;

/**
 * Class Affiliate
 *
 * Can be loaded using $this->load->controller('api/affiliate');
 *
 * @package Opencart\Catalog\Controller\Api\Sale
 */
class Affiliate extends BaseController {
	/**
	 * Index
	 *
	 * @return array<string, mixed>
	 */
	public function index(): array {
		$this->load->language('api/affiliate');

		$output = [];

		$affiliate_id = isset($this->request->post['affiliate_id']) ? (int)$this->request->post['affiliate_id'] : 0;

		if ($affiliate_id) {
			$affiliateRepository = $this->getRepository(CustomerAffiliateRepository::class);
			$affiliate_info = $affiliateRepository->find($affiliate_id);

			// Na arquitetura de Domínio, verificamos se a Entidade foi encontrada e se está ativa
			if (!$affiliate_info || (method_exists($affiliate_info, 'getStatus') && !$affiliate_info->getStatus())) {
				$output['error'] = $this->language->get('error_affiliate');
			}
		}

		// Get Sub Total
		if (isset($this->session->data['order_id'])) {
			$subtotal = 0;

			$orderRepository = $this->getRepository(OrderRepository::class);
			$order = $orderRepository->find((int)$this->session->data['order_id']);

			// Tenta utilizar a Hidratação de Domínio (OrderTotal) do Pedido
			if ($order && method_exists($order, 'getTotals') && !empty($order->getTotals())) {
				foreach ($order->getTotals() as $total) {
					if (method_exists($total, 'getCode') && $total->getCode() == 'subtotal') {
						$subtotal = method_exists($total, 'getValue') ? $total->getValue() : 0;
						break;
					}
				}
			} else {
				// Fallback de Segurança caso a Entidade OrderTotal ainda não tenha sido completamente populada no Controller
				$results = $orderRepository->getTotals($this->session->data['order_id']);
				foreach ($results as $result) {
					if ($result['code'] == 'subtotal') {
						$subtotal = $result['value'];
						break;
					}
				}
			}

			if (!$subtotal) {
				$output['error'] = $this->language->get('error_order');
			}
		}

		if (!$output) {
			$output['success'] = $this->language->get('text_success');

			$this->session->data['affiliate_id'] = $affiliate_id;
		}

		return $output;
	}
}
