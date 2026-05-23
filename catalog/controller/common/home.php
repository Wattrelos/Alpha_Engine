<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\HomeRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;

/**
 * Class Home
 * 
 * Refatorado para a Alpha Engine.
 * Injeta diretamente os DTOs de produtos e banners sem depender do layout_id nativo cego,
 * aproveitando a arquitetura Loader-Free (Zero N+1 Queries).
 */
class Home extends BaseController {
	public function index(): void {
		// Alpha Engine: Utiliza o HomeRepository injetado pela BaseController
        $homeRepository = $this->getRepository(HomeRepository::class);
        $homeData = $homeRepository->getHomeData();
        
        $data = $homeData->toArray();

        // Alpha Engine: As meta tags (SEO) devem ser injetadas no objeto Document para o header.twig ler
        $this->document->setTitle($homeData->get('title') ?? $this->config->get('config_meta_title'));
        $this->document->setDescription($homeData->get('description') ?? $this->config->get('config_meta_description'));
        $this->document->setKeywords($homeData->get('keywords') ?? $this->config->get('config_meta_keyword'));

        // Alpha Engine: Registro de estilos CSS customizados (Identidade Visual)
        $this->document->addStyle('catalog/view/stylesheet/custom/personalizada.css');

        // Alpha Engine: Carregamento unificado das traduções
        $this->loadLanguageData('common/home', $data);

        $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		/** @var ProductRepository $productRepository */
		$productRepository = $this->getRepository(ProductRepository::class);

		// Alpha Engine: Injeção Loader-Free de Lançamentos (Latest)
		$filter_latest = [
			'sort'  => 'p.date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => 8 // Mostraremos 8 produtos na grid
		];
		
		$data['latest_products'] = [];
		foreach ($productRepository->getProducts($filter_latest) as $result) {
			$data['latest_products'][] = $this->load->view('product/thumb', $productRepository->getProductThumbData($result));
		}

		// Alpha Engine: Injeção Loader-Free de Destaques (Featured - Usando 'Mais Vistos' como regra)
		$filter_featured = [
			'sort'  => 'p.viewed',
			'order' => 'DESC',
			'start' => 0,
			'limit' => 4 // Mostraremos 4 destaques na grid
		];

		$data['featured_products'] = [];
		foreach ($productRepository->getProducts($filter_featured) as $result) {
			$data['featured_products'][] = $this->load->view('product/thumb', $productRepository->getProductThumbData($result));
		}

		// Array reservado para quando implementarmos o BannerRepository
		$data['home_banner'] = [];

		// A renderização do BaseController injeta automaticamente header, footer, colunas e os produtos na View
		$this->render('common/home', $data);
	}
}