<?php
namespace Opencart\Catalog\Controller\Common;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
/**
 * Class Content Top
 *
 * Can be called from $this->load->controller('common/content_top');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class ContentTop extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		// Alpha Engine: Delegar a renderização completa da posição para o método herdado
		$data['modules'] = $this->renderPosition('content_top');

		return $this->load->view('common/content_top', $data);
	}
}
