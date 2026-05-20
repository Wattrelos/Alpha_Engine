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
		$currencyRepository = $this->repository->get(CurrencyRepository::class);
		$currencyData = $currencyRepository->getCurrencyDisplayData();

		$data = $currencyData->toArray();
		$data['action'] = $this->url->link('common/currency.save', 'language=' . $this->config->get('config_language'));

		$data['redirect'] = $currencyRepository->getRedirectUrl($this->request->get);

		return $this->render('common/currency', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$json = [];

		$required = [
			'code'     => '',
			'redirect' => ''
		];

		$post_info = $this->request->post + $required;

		$currencyRepository = $this->repository->get(CurrencyRepository::class);

		if (!$currencyRepository->updateCurrency($post_info['code'])) {
			$json['error'] = $this->language->get('error_currency');
			$json['redirect'] = $currencyRepository->processSaveRedirect($post_info['redirect'], $post_info['code']);
		}

		$this->jsonResponse($json);
	}
}
