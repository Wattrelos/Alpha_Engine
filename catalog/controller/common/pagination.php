<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\PaginationRepository;
/**
 * Class Pagination
 *
 * Can be loaded using $this->load->controller('common/pagination', $setting);
 *
 * @example
 *
 * $setting = [
 *     'total' => 10,
 *     'page'  => 1,
 *     'limit' => 10,
 *     'url'   => ''
 * ];
 *
 * @package Opencart\Catalog\Controller\Common
 */
class Pagination extends BaseController {
	/**
	 * Index
	 *
	 * @param array<string, mixed> $setting array of filters
	 *
	 * @return string
	 */
	public function index(array $setting): string {
		$paginationRepository = $this->repository->get(PaginationRepository::class);
		$paginationData = $paginationRepository->prepare($setting);

		if ($paginationData->shouldRender()) {
			return $this->render('common/pagination', $paginationData->toArray());
		}

		return '';
	}
}
