<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Checkout;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CouponRepository;

/**
 * Class Coupon
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Checkout
 */
class Coupon extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		if ($this->config->get('total_coupon_status')) {
			$data = [];
			$this->loadLanguageData('extension/opencart/checkout/coupon', $data);

			$data['save'] = $this->url->link('extension/opencart/checkout/coupon.save', 'language=' . $this->config->get('config_language'), true);
			$data['remove'] = $this->url->link('extension/opencart/checkout/coupon.remove', 'language=' . $this->config->get('config_language'), true);
			$data['list'] = $this->url->link('checkout/cart.list', 'language=' . $this->config->get('config_language'), true);

			if (isset($this->session->data['coupon'])) {
				$data['coupon'] = $this->session->data['coupon'];
			} else {
				$data['coupon'] = '';
			}

			// Alpha Engine: Renderização envelopada Anti-WSOD
			return $this->viewRenderer->render('extension/opencart/checkout/coupon', $data);
		}

		return '';
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/opencart/checkout/coupon');

		$json = [];

		if (isset($this->request->post['coupon'])) {
			$coupon = $this->request->post['coupon'];
		} else {
			$coupon = '';
		}

		if (!$this->config->get('total_coupon_status')) {
			$json['error'] = $this->language->get('error_status');
		}

		// Alpha Engine: Injeção do Repositório de Domínio e desacoplamento do Model antigo
		$couponRepository = $this->getRepository(CouponRepository::class);
		$coupon_info = $couponRepository->getCoupon($coupon);

		if (!$coupon_info) {
			$json['error'] = $this->language->get('error_coupon');
		}

		if (!$json) {
			$json['success'] = $this->language->get('text_success');

			$this->session->data['coupon'] = $coupon;

			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->jsonResponse($json);
	}

	/**
	 * Remove
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->load->language('extension/opencart/checkout/coupon');

		$json = [];

		if (!isset($this->session->data['coupon'])) {
			$json['error'] = $this->language->get('error_remove');
		}

		if (!$json) {
			$json['success'] = $this->language->get('text_remove');

			unset($this->session->data['coupon']);

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->jsonResponse($json);
	}
}
