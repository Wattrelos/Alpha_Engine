<?php
namespace Opencart\Catalog\Controller\Common;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
/**
 * Class Column Right
 *
 * Can be called from $this->load->controller('common/column_right');
 *
 * @package Opencart\Catalog\Controller\Common
 */
class ColumnRight extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		// Alpha Engine: Delegar a renderização completa da posição para o método herdado
		$data['modules'] = $this->renderPosition('column_right');

		return $this->load->view('common/column_right', $data);
	}
}
