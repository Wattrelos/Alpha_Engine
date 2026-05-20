<?php
namespace Opencart\Catalog\Controller\Startup;

use Alpha\Controller\BaseController;
use Alpha\Mappers\EntityMappers\LanguageMapper;
use Alpha\Mappers\CollectionToArrayConverter;

/**
 * Alpha Engine: Startup Language - Gerencia a internacionalização e o carregamento automático de traduções.
 *
 * @package Opencart\Catalog\Controller\Startup
 */
class Language extends BaseController {
	/**
	 * @var array<string, array<string, mixed>>
	 */
	private static array $languages = [];

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		// Language
		/** @var LanguageMapper $languageMapper */
		$languageMapper = $this->mapper->get(LanguageMapper::class);
		$results = $languageMapper->getLanguages();

		foreach ($results as $result) {
			$data = CollectionToArrayConverter::convertEntity($result);
			self::$languages[$data['code']] = $data;
		}

		$language_info = [];

		// Set default language
		if (isset(self::$languages[$this->config->get('config_language_catalog')])) {
			$language_info = self::$languages[$this->config->get('config_language_catalog')];
		}

		// If GET has language var
		if (isset($this->request->get['language']) && isset(self::$languages[$this->request->get['language']])) {
			$language_info = self::$languages[$this->request->get['language']];
		}

		if ($language_info) {
			// If extension switch add language directory
			if ($language_info['extension']) {
				$this->language->addPath('extension/' . $language_info['extension'], DIR_EXTENSION . $language_info['extension'] . '/catalog/language/');
			}

			// Set the config language_id key
			$this->config->set('config_language_id', $language_info['id']);
			$this->config->set('config_language', $language_info['code']);

			$this->load->language('default');
		}
	}

	/**
	 * Alpha Engine: Auto-Language Loader.
	 *
	 * Este método intercepta a execução de controladores e carrega automaticamente
	 * os arquivos de tradução (i18n) baseados na rota atual. Isso elimina a necessidade
	 * de usar funções legadas de carregamento manual nos controladores de domínio.
	 *
	 * @param string       $route  A rota do controlador sendo chamado.
	 * @param string       $prefix Prefixo de idioma.
	 * @param string       $code   Código do idioma (ex: 'pt-br').
	 * @param array<mixed> $output
	 *
	 * @return void
	 */
	public function after(&$route, &$prefix, &$code, &$output): void {
		if (!$code) {
			$code = $this->config->get('config_language');
		}

		// Use $this->language->load so it's not triggering infinite loops
		$this->language->load($route, $prefix, $code);

		if (isset(self::$languages[$code])) {
			$language_info = self::$languages[$code];

			$path = '';

			if ($language_info['extension']) {
				$extension = 'extension/' . $language_info['extension'];

				if (oc_substr($route, 0, strlen($extension)) != $extension) {
					$path = $extension . '/';
				}
			}

			// Use $this->language->load so it's not triggering infinite loops
			$this->language->load($path . $route, $prefix, $code);
		}
	}
}
