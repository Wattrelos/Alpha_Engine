<?php
namespace Opencart\Catalog\Controller\Event;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\ThemeRepository;

/**
 * Class Theme
 *
 * @package Opencart\Catalog\Controller\Event
 */
class Theme extends BaseController {
	/**
	 * Index
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param string            $code
	 *
	 * @return void
	 */
	public function index(string &$route, array &$args, string &$code): void {
		// If there is a theme override, we should get it
		$themeRepository = $this->getRepository(ThemeRepository::class);
		$theme_info = $themeRepository->getTheme($route, $this->config->get('config_theme'));

		if ($theme_info) {
			$code = html_entity_decode($theme_info['code'], ENT_QUOTES, 'UTF-8');
		}
	}
}
