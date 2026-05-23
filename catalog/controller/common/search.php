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
		$searchRepository = $this->getRepository(SearchRepository::class);
		$searchData = $searchRepository->getSearchDisplayData();

		return $this->load->view('common/search', $searchData->toArray());
	}
}