<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;

/**
 * Class Column Left
 */
class ColumnLeft extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		// Alpha Engine: Delegar a renderização completa da posição para o método herdado
		$data['modules'] = $this->renderPosition('column_left');

		return $this->load->view('common/column_left', $data);
	}
}
