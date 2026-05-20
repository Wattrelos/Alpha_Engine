<?php
namespace Opencart\Catalog\Controller\Common;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\SearchRepository;

class Search extends BaseController {
	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$searchRepository = $this->repository->get(SearchRepository::class);
		$searchData = $searchRepository->getSearchDisplayData();

		return $this->render('common/search', $searchData->toArray());
	}
}