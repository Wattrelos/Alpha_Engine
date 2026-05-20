<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Model\Domain\Repositories\HomeRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

class Home extends \Opencart\System\Engine\Controller {
    private HomeRepository $homeRepository;

    public function __construct(\Opencart\System\Engine\Registry $registry) {
        parent::__construct($registry);
        // Alpha Engine: Injeção do HomeRepository via RepositoryFactory
        $this->homeRepository = RepositoryFactory::getInstance()->get(HomeRepository::class);
    }

	public function index(): string {
		// Alpha Engine: Utiliza o HomeRepository para configurar metadados e obter dados básicos da home
        $homeData = $this->homeRepository->getHomeData();
        $data['title'] = $homeData->get('title');
        $data['description'] = $homeData->get('description');
        $data['keywords'] = $homeData->get('keywords');

		 // Alpha Engine: Carrega produtos em destaque via HomeRepository
        // Assumindo que 'module_featured_product_product' é uma string de IDs separados por vírgula
        $featured_product_ids = explode(',', $this->config->get('module_featured_product_product'));
        $featured_limit = (int)$this->config->get('module_featured_product_limit') ?: 4;
        $data['featured_products'] = $this->homeRepository->getFeatured($featured_product_ids, $featured_limit);

        // Alpha Engine: Carrega produtos mais recentes via HomeRepository
        $latest_limit = (int)$this->config->get('module_latest_product_limit') ?: 4;
        $data['latest_products'] = $this->homeRepository->getLatest($latest_limit);

        // Alpha Engine: Carrega banners da Home via HomeRepository
        // Assumindo que 'module_banner_home_id' é o ID do banner configurado para a home
        $home_banner_id = (int)$this->config->get('module_banner_home_id');
        $data['home_banner'] = $this->homeRepository->getHomeBanners($home_banner_id);

        // Alpha Engine: Exemplo de como você pode passar dados para a view
        $data['text_featured'] = $this->language->get('heading_title_featured');
        $data['text_latest'] = $this->language->get('heading_title_latest');
        $data['text_empty'] = $this->language->get('text_empty');
        $data['button_cart'] = $this->language->get('button_cart');
        $data['button_wishlist'] = $this->language->get('button_wishlist');
        $data['button_compare'] = $this->language->get('button_compare');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		return $this->render('common/home', $homeData->getData());


	}
}