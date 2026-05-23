<?php
namespace Opencart\Catalog\Controller\Localisation;

use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\ZoneRepository;

/**
 * Class Country
 *
 * @package Opencart\Catalog\Controller\Localisation
 */
class Country extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$json = [];
		$country_id = (int)($this->request->get['country_id'] ?? 0);

		if ($country_id) {
			/** @var CountryRepository $countryRepo */
			$countryRepo = $this->registry->get('alpha_repository_factory')->get(CountryRepository::class);
			
			// Retorna o DTO Legado perfeitamente formatado (Status como inteiro 0/1, Nomes Traduzidos)
			$json = $countryRepo->getCountry($country_id);

			if ($json) {
				/** @var ZoneRepository $zoneRepo */
				$zoneRepo = $this->registry->get('alpha_repository_factory')->get(ZoneRepository::class);
				
				// Associa a coleção de estados traduzidos diretamente na chave singular 'zone'
				$json['zone'] = $zoneRepo->getZonesByCountryId($country_id);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
