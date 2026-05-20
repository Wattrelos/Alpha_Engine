<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\FooterRepository;
use Opencart\Catalog\Controller\Common\Cookie;

/**
 * Class Footer
 * 
 * Controlador de rodapé refatorado para a Alpha Engine.
 */
class Footer extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$footerRepository = $this->repository->get(FooterRepository::class);
		$footerData = $footerRepository->getFooterData();

		$data = $footerData->toArray();

		// Alpha Engine: Injeção Loader-Free do componente de cookies
		$data['cookie'] = (new Cookie($this->registry))->index();

		// TODO: Adicionar resolução de módulos de rodapé dinâmicos se necessário
		
		return $this->render('common/footer', $data);
	}
}