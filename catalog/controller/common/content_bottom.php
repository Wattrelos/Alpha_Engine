<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;

/**
 * Class Content Bottom
 */
class ContentBottom extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		// Alpha Engine: Delegar a renderização completa da posição para o método herdado
		$data['modules'] = $this->renderPosition('content_bottom');

		return $this->load->view('common/content_bottom', );
	}
}

