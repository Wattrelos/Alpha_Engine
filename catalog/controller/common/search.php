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

		$data = $searchData->toArray();

		// Alpha Engine: Injeção de traduções para prevenir "Undefined variable" no Twig
		$this->loadLanguageData('common/search', $data);

		return $this->viewRenderer->render('common/search', $data);
	}
}