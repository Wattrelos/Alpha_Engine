<?php
namespace Opencart\Catalog\Controller\Common;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CurrencyRepository;
/**
 * Class Currency
 *
 * Can be called from $this->load->controller('common/currency');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Currency extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$currencyRepository = $this->getRepository(CurrencyRepository::class);
		$currencyData = $currencyRepository->getCurrencyDisplayData();

		$data = $currencyData->toArray();

		$data['redirect'] = $currencyRepository->getRedirectUrl($this->request->get);

		return $this->viewRenderer->render('common/currency', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$json = [];

		$required = [
			'code'     => (string)($this->request->post['code'] ?? ''),
			'redirect' => ''
		];

		$post_info = $this->request->post + $required;

		$currencyRepository = $this->getRepository(CurrencyRepository::class);

		if (!$currencyRepository->isValid($post_info['code'])) {
			$json['error'] = $this->language->get('error_currency');
		}

		if (!$json) {
			$currencyRepository->setCurrencyContext($post_info['code']);
			$json['redirect'] = $currencyRepository->processSaveRedirect($post_info['redirect'], $post_info['code']);
		}

		$this->jsonResponse($json);
	}
}
