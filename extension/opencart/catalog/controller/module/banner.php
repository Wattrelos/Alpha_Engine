<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\BannerRepository;

/**
 * Class Banner
 *
 * @package Opencart\Catalog\Controller\Extension\Opencart\Module
 */
class Banner extends BaseController {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting array of filters
	 *
	 * @return string
	 */
	public function index(array $setting): string {
		static $module = 0;

		$bannerRepository = $this->getRepository(BannerRepository::class);

		// Image
		$this->load->model('tool/image');

		$data['banners'] = [];

		$results = $bannerRepository->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$data['banners'][] = [
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height'])
				];
			}
		}

		if ($data['banners']) {
			$data['module'] = $module++;

			$data['effect'] = $setting['effect'];
			$data['controls'] = $setting['controls'];
			$data['indicators'] = $setting['indicators'];
			$data['items'] = $setting['items'];
			$data['interval'] = $setting['interval'];
			$data['width'] = $setting['width'];
			$data['height'] = $setting['height'];

			return $this->viewRenderer->render('extension/opencart/module/banner', $data);
		} else {
			return '';
		}
	}
}
