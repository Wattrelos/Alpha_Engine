<?php
namespace Opencart\Catalog\Model\Setting;

use Alpha\Model\Domain\Repositories\SettingRepository;

/**
 * Class Setting (Legacy Bridge)
 *
 * Proxy refatorado para Alpha Engine. Utiliza o SettingRepository para entregar
 * respostas ultra-rápidas servidas na memória, evitando múltiplos queries SQL legados.
 *
 * @package Opencart\Catalog\Model\Setting
 */
class Setting extends \Opencart\System\Engine\Model {
	private SettingRepository $settingRepository;

	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
		$this->settingRepository = $registry->get('repository')->get(SettingRepository::class);
	}

	/**
	 * Get Settings
	 *
	 * Get the record of the setting records in the database.
	 *
	 * @param int $store_id
	 *
	 * @return array<int, array<string, mixed>>
	 *
	 * @example
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $settings = $this->model_setting_setting->getSettings();
	 */
	public function getSettings(int $store_id = 0): array {
		return $this->settingRepository->getSettings($store_id);
	}

	/**
	 * Get Setting
	 *
	 * @param string $code
	 * @param int    $store_id
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $setting_info = $this->model_setting_setting->getSetting($code, $store_id);
	 */
	public function getSetting(string $code, int $store_id = 0): array {
		return $this->settingRepository->getSetting($code, $store_id);
	}

	/**
	 * Get Value
	 *
	 * @param string $key
	 * @param int    $store_id
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $this->load->model('setting/setting');
	 *
	 * $value = $this->model_setting_setting->getValue($key, $store_id);
	 */
	public function getValue(string $key, int $store_id = 0): string {
		return $this->settingRepository->getValue($key, $store_id);
	}
}
