<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;

class Menu extends BaseController {
	public function index(): string {
		$data = [];

		// Alpha Engine: Carregamento unificado de traduções
		$this->loadLanguageData('common/menu', $data);

		// Injeta o repositório da Alpha Engine
		$categoryRepository = $this->getRepository(CategoryRepository::class);

		// 1. Injeta o HTML processado super rápido na variável {{ categorias }}
		$data['categorias'] = $categoryRepository->getMenuHtml();

		// 2. (Fallback) Mantém o array vazio para evitar quebra no laço Twig legado, se houver
		$data['categories'] = [];

		return $this->load->view('common/menu', $data);
	}
}