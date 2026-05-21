<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\HomeRepository;
use Opencart\Catalog\Controller\Product\Thumb;

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

        // Alpha Engine: Carrega produtos em destaque via HomeRepository
        // Assumindo que 'module_featured_product_product' é uma string de IDs separados por vírgula
        $featured_config = $this->config->get('module_featured_product_product');
        $featured_product_ids = is_array($featured_config) ? $featured_config : array_filter(explode(',', (string)$featured_config));
        $featured_limit = (int)$this->config->get('module_featured_product_limit') ?: 4;
        $featured_results = $homeRepository->getFeatured($featured_product_ids, $featured_limit);
        
        $data['featured_products'] = [];
        foreach ($featured_results as $result) {
            $data['featured_products'][] = (new Thumb($this->registry))->index($result);
        }

        // Alpha Engine: Carrega produtos mais recentes via HomeRepository
        $latest_limit = (int)$this->config->get('module_latest_product_limit') ?: 4;
        $latest_results = $homeRepository->getLatest($latest_limit);

        $data['latest_products'] = [];
        foreach ($latest_results as $result) {
            $data['latest_products'][] = (new Thumb($this->registry))->index($result);
        }

        // Alpha Engine: Carrega banners da Home via HomeRepository
        // Assumindo que 'module_banner_home_id' é o ID do banner configurado para a home
        $home_banner_id = (int)$this->config->get('module_banner_home_id');
        $data['home_banner'] = $homeRepository->getHomeBanners($home_banner_id);

        // Alpha Engine: Carregamento unificado das traduções
        $this->loadLanguageData('common/home', $data);

        $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		// Alpha Engine: O método render da BaseController injeta as colunas, header e footer automaticamente
		$this->render('common/home', $data);
	}
}