<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CategoryRepository;

class Menu extends BaseController {
	public function index(): string {
		// Carrega as traduções padrão do menu (como text_all, text_category)
		$this->load->language('common/menu');
		$data['text_category'] = $this->language->get('text_category');
		$data['text_all'] = $this->language->get('text_all');

		// Injeta o repositório da Alpha Engine
		$categoryRepository = $this->getRepository(CategoryRepository::class);

		// 1. Injeta o HTML processado super rápido na variável {{ categorias }}
		$data['categorias'] = $categoryRepository->getMenuHtml();

		// 2. (Fallback) Mantém o array vazio para evitar quebra no laço Twig legado, se houver
		$data['categories'] = [];

		return $this->load->view('common/menu', $data);
	}
}