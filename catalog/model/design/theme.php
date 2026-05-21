<?php
namespace Opencart\Catalog\Model\Design;

use Alpha\Model\Domain\Repositories\ThemeRepository;

/**
 * Class Theme
 *
 * Can be called using $this->load->model('design/theme');
 *
 * @package Opencart\Catalog\Model\Design
 */
class Theme extends \Opencart\System\Engine\Model {
	/**
	 * Get Theme
	 *
	 * Get the record of the theme record in the database.
	 *
	 * @param string $route
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('design/theme');
	 *
	 * $theme_info = $this->model_design_theme->getTheme($route);
	 */
	public function getTheme(string $route): array {
        // Alpha Engine: Consome o Repositório de temas que possui Identity Map Nativo
		$repositoryFactory = $this->registry->get('alpha_repository_factory');
		$themeRepository = $repositoryFactory->get(ThemeRepository::class);

		return $themeRepository->getTheme($route, (int)$this->config->get('config_store_id')) ?? [];
	}
}
