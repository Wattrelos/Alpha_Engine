<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CookieRepository;

/**
 * Class Cookie
 *
 * Can be called from $this->load->controller('common/cookie');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Cookie extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$cookieRepository = $this->getRepository(CookieRepository::class);
		$cookieData = $cookieRepository->getCookieDisplayData();

		if ($cookieData) {
			return $this->load->view('common/cookie', $cookieData->toArray());
		}

		return '';
	}

	/**
	 * Confirm
	 *
	 * @return void
	 */
	public function confirm(): void {
		$this->language->load('common/cookie');

		$json = [];
		$agree = (string)($this->request->get['agree'] ?? '0');

		$cookieRepository = $this->getRepository(CookieRepository::class);

		if ($cookieRepository->confirmPolicy($agree)) {
			$json['success'] = $this->language->get('text_success');
		}
		$this->jsonResponse($json);
	}
}
