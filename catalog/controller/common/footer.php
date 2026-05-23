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
		// Alpha Engine: Utiliza o padrão unificado da BaseController para repositórios
		$footerRepository = $this->getRepository(FooterRepository::class);
		$footerData = $footerRepository->getFooterData();

		$data = $footerData->toArray();

		// Alpha Engine: Carregamento unificado das traduções do rodapé
		$this->loadLanguageData('common/footer', $data);

		// Alpha Engine: Injeção Loader-Free do componente de cookies
		$data['cookie'] = (new Cookie($this->registry))->index();

		// TODO: Adicionar resolução de módulos de rodapé dinâmicos se necessário
		
		return $this->load->view('common/footer', $data);
	}
}