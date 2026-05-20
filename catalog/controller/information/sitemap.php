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
	 * @return string
	 */
	public function index(): string {
		$this->loadLanguage('information/sitemap');

		$sitemapRepository = $this->repository->get(SitemapRepository::class);
		$sitemapData = $sitemapRepository->getSitemapData();
		
		$data = $sitemapData->toArray();

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/sitemap')
		];

		return $this->render('information/sitemap', $data);
	}
}
