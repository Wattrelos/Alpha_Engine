<?php
namespace Opencart\Catalog\Model\Design;

use Alpha\Model\Domain\Repositories\BannerRepository;
/**
 * Class Banner
 *
 * Can be called using $this->load->model('design/banner');
 *
 * @package Opencart\Catalog\Model\Design
 */
class Banner extends \Opencart\System\Engine\Model {
	/**
	 * Get Banner
	 *
	 * Get the record of the banner record in the database.
	 *
	 * @param int $banner_id primary key of the banner record
	 *
	 * @return array<int, array<string, mixed>> banner records that have banner ID
	 *
	 * @example
	 *
	 * $this->load->model('design/banner');
	 *
	 * $banner_info = $this->model_design_banner->getBanner($banner_id);
	 */
	public function getBanner(int $banner_id): array {
		// Alpha Engine: Consome o repositório nativo com Identity Map
		$repositoryFactory = $this->registry->get('alpha_repository_factory');
		$bannerRepository = $repositoryFactory->get(BannerRepository::class);
		return $bannerRepository->getBanner($banner_id);
	}
}
