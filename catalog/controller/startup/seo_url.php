<?php
namespace Opencart\Catalog\Controller\Startup;

use Alpha\Model\Domain\Repositories\SeoUrlRepository;

/**
 * Class SeoUrl (Alpha Engine Modernized)
 *
 * @package Opencart\Catalog\Controller\Startup
 */
class SeoUrl extends \Opencart\System\Engine\Controller {
	/**
	 * @var array<string, string>
	 */
	private array $data = [];

	/**
	 * Index
	 *
	 * @return null
	 */
	public function index() {
		// Add rewrite to URL class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);

			// Alpha Engine: Injeção fluida do repositório nativo do domínio
			$seoUrlRepository = $this->registry->get('alpha_repository_factory')->get(SeoUrlRepository::class);

			// Decode URL
			if (isset($this->request->get['_route_'])) {
				// Cast explícito de string para segurança estrita
				$parts = explode('/', (string)$this->request->get['_route_']);

				// remove any empty arrays from trailing
				if (!empty($parts) && end($parts) === '') {
					array_pop($parts);
				}

				foreach ($parts as $key => $value) {
					// Alpha Engine: Resolve o slug para a query string interna correspondente (ex: "product_id=123")
					$query_string = $seoUrlRepository->getQueryByKeyword($value, (int)$this->config->get('config_store_id'), (int)$this->config->get('config_language_id'));

					if ($query_string) {
						$pair = explode('=', $query_string);
						if (isset($pair[0]) && isset($pair[1])) {
							$this->request->get[$pair[0]] = html_entity_decode($pair[1], ENT_QUOTES, 'UTF-8');
							unset($parts[$key]);
						}
					}
				}

				if (!isset($this->request->get['route'])) {
					$this->request->get['route'] = $this->config->get('action_default');
				}

				if ($parts) {
					$this->request->get['route'] = $this->config->get('action_error');
				}
			}
		}

		return null;
	}

	/**
	 * Rewrite
	 *
	 * @param string $link
	 *
	 * @return string	 
	 */
	public function rewrite(string $link): string {
		// Alpha Engine: Prevenção rigorosa PHP 8.4 contra retornos false do parse_url
		$url_info = parse_url(str_replace('&amp;', '&', $link)) ?: [];

		// Build the url
		$url = '';

		if (!empty($url_info['scheme'])) {
			$url .= $url_info['scheme'] . '://';
		} elseif (!empty($url_info['host'])) {
			$url .= '//';
		}

		if (!empty($url_info['host'])) {
			$url .= $url_info['host'];
		}

		if (!empty($url_info['port'])) {
			$url .= ':' . $url_info['port'];
		}

		$query = [];
		$parts = [];
		if (!empty($url_info['query'])) {
			parse_str($url_info['query'], $query);
			$parts = explode('&', $url_info['query']);
		}

		// Start changing the URL query into a path
		$paths = [];

		$seoUrlRepository = $this->registry->get('alpha_repository_factory')->get(SeoUrlRepository::class);

		foreach ($parts as $part) {
			$pair = explode('=', $part);

			if (isset($pair[0])) {
				$key = (string)$pair[0];
			}

			if (isset($pair[1])) {
				$value = (string)$pair[1];
			} else {
				$value = '';
			}

			$index = $key . '=' . $value;

			$languageId = (int)$this->config->get('config_language_id');
			$storeId = (int)$this->config->get('config_store_id');

			if (!isset($this->data[$languageId][$index])) {
				// Alpha Engine: Resolução de alta performance via Repositório (Identity Map -> Cache Físico -> DAO)
				$keyword = $seoUrlRepository->getKeywordByQuery($key, $value, $storeId, $languageId);
				
				if ($keyword) {
					$this->data[$languageId][$index] = [
						'keyword'    => $keyword,
						'sort_order' => count($paths)
					];
				} else {
					$this->data[$languageId][$index] = false;
				}
			}

			if (!empty($this->data[$languageId][$index])) {
				$paths[] = $this->data[$languageId][$index];

				unset($query[$key]);
			}
		}

		$sort_order = [];

		foreach ($paths as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $paths);

		// Build the path
		$url .= str_replace('/index.php', '', $url_info['path'] ?? '');

		foreach ($paths as $result) {
			$url .= '/' . $result['keyword'];
		}

		// Rebuild the URL query
		if ($query) {
			$url .= '?' . str_replace(['%2F'], ['/'], http_build_query($query));
		}

		return $url;
	}
}
