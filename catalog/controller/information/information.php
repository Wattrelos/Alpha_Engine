<?php
namespace Opencart\Catalog\Controller\Information;

use Alpha\Mappers\InformationMapper;
use Alpha\Mappers\LanguageMapper;
use Alpha\Mappers\CollectionToArrayConverter;

/**
 * Class Information
 *
 * @package Opencart\Catalog\Controller\Information
 */
class Information extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return ?\Opencart\System\Engine\Action
	 */
	public function index(): ?\Opencart\System\Engine\Action {
		$this->load->language('information/information');

		$information_id = (int)($this->request->get['information_id'] ?? 0);
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');

		$information_mapper = new InformationMapper();

		// Alpha Engine: Obtemos a Entidade hidratada com coleções e objetos relacionados (Language, Store)
		$information = $information_mapper->getInformationEntity($information_id);

		// Valida se a página existe, está ativa e se pertence à loja atual (Regra Multi-store)
		$in_store = false;
		if ($information && $information->getStatus()) {
			foreach ($information->getInformationToStores() as $infoStore) {
				if ($infoStore->getStoreId() === $store_id) {
					$in_store = true;
					break;
				}
			}
		}

		if ($information && $in_store) {
			// Identifica a descrição correspondente ao idioma da sessão
			$description = null;
			foreach ($information->getDescriptions() as $desc) {
				if ($desc->getLanguageId() === $language_id) {
					$description = $desc;
					break;
				}
			}

			if (!$description) {
				return new \Opencart\System\Engine\Action('error/not_found');
			}

			$this->document->setTitle($description->getMetaTitle());
			$this->document->setDescription($description->getMetaDescription());
			$this->document->setKeywords($description->getMetaKeyword());

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
			];

			$data['breadcrumbs'][] = [
				'text' => $description->getTitle(),
				'href' => $this->url->link('information/information', 'language=' . $this->config->get('config_language') . '&information_id=' . $information_id)
			];

			$data['heading_title'] = $description->getTitle();

			$data['description'] = html_entity_decode($description->getDescription(), ENT_QUOTES, 'UTF-8');

			// Converte a árvore de Entidades para array (Language e Description inclusos)
			$data += CollectionToArrayConverter::convertEntity($information);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/information', $data));
		} else {
			return new \Opencart\System\Engine\Action('error/not_found');
		}

		return null;
	}

	/**
	 * Info
	 *
	 * @return void
	 */
	public function info(): void {
		$information_id = (int)($this->request->get['information_id'] ?? 0);
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');

		$information_mapper = new InformationMapper();

		// Alpha Engine: Normalizamos o uso buscando a Entidade (que já possui Descriptions e Languages)
		$information = $information_mapper->getInformationEntity($information_id);

		// Validação de segurança: status e vínculo multi-loja
		$in_store = false;
		if ($information && $information->getStatus()) {
			foreach ($information->getInformationToStores() as $infoStore) {
				if ($infoStore->getStoreId() === $store_id) {
					$in_store = true;
					break;
				}
			}
		}

		if ($information && $in_store) {
			// Identificamos a descrição correta para o idioma da sessão
			$description = null;
			foreach ($information->getDescriptions() as $desc) {
				if ($desc->getLanguageId() === $language_id) {
					$description = $desc;
					break;
				}
			}

			if ($description) {
				// O conversor gera o array associativo respeitando a árvore de objetos vinculados (Language incl.)
				$data = CollectionToArrayConverter::convertEntity($information);

				$data['title'] = $description->getTitle();
				$data['description'] = html_entity_decode($description->getDescription(), ENT_QUOTES, 'UTF-8');
				
				$this->response->addHeader('X-Robots-Tag: noindex');
				$this->response->setOutput($this->load->view('information/information_info', $data));
			}
		}
	}
}
