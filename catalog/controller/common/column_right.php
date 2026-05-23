<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;

/**
 * Class Column Right
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

		return $this->load->view('common/column_right', );
	}
}
