<?php
namespace Opencart\Catalog\Controller\Cron;

use Alpha\Mappers\EntityMappers\ExtensionMapper;

/**
 * Class Currency
 *
 * @package Opencart\Catalog\Controller\Cron
 */
class Currency extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @param int    $cron_id
	 * @param string $code
	 * @param string $cycle
	 * @param string $date_added
	 * @param string $date_modified
	 *
	 * @return void
	 */
	public function index(int $cron_id, string $code, string $cycle, string $date_added, string $date_modified): void {
		// Extension
		// Alpha Engine: Instancia o ExtensionMapper diretamente
		$extensionMapper = new ExtensionMapper();

		// Busca a informação da extensão do motor de moedas
		$extension_info = $extensionMapper->getExtensionByCode('currency', (string)$this->config->get('config_currency_engine'));

		if ($extension_info) {
			$this->load->controller('extension/' . $extension_info['extension'] . '/currency/' . $extension_info['code'] . '.currency', (string)$this->config->get('config_currency'));
		}
	}
}
