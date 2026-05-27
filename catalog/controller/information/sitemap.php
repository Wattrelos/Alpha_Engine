<?php
namespace Opencart\Catalog\Controller\Information;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\SitemapRepository;

/**
 * Class Sitemap
 * @package Opencart\Catalog\Controller\Information
 */
class Sitemap extends BaseController {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$sitemapRepository = $this->getRepository(SitemapRepository::class);
		$sitemapData = $sitemapRepository->getSitemapData();
		
		$data = $sitemapData->toArray();

		// Alpha Engine: Injeção automática das strings de tradução no array $data
		$this->loadLanguageData('information/sitemap', $data);

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/sitemap', 'language=' . $this->config->get('config_language'))
		];

		$this->render('information/sitemap', $data);
	}
}
