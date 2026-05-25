<?php
namespace Opencart\Catalog\Model\Cms;

use Alpha\Mappers\TopicMapper;

/**
 * Class Topic
 *
 * Can be called using $this->load->model('cms/topic');
 *
 * @package Opencart\Catalog\Model\Cms
 */
class Topic extends \Opencart\System\Engine\Model {
	/**
	 * Get Topic
	 *
	 * Get the record of the topic record in the database.
	 *
	 * @param int $topic_id primary key of the topic record
	 *
	 * @return array<int, array<string, mixed>> topic record that has topic ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/topic');
	 *
	 * $topic_info = $this->model_cms_topic->getTopic($topic_id);
	 */
	public function getTopic(int $topic_id): array {
		$mapper = new TopicMapper();

		return $mapper->getTopic(
			$topic_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Get Topics
	 *
	 * Get the record of the topic records in the database.
	 *
	 * @return array<int, array<string, mixed>> topic records
	 *
	 * @example
	 *
	 * $this->load->model('cms/topic');
	 *
	 * $results = $this->model_cms_topic->getTopics();
	 */
	public function getTopics(): array {
		$mapper = new TopicMapper();

		return $mapper->getTopics(
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Get Layout ID
	 *
	 * Get the record of the article layout record in the database.
	 *
	 * @param int $topic_id
	 * @param int $article_id primary key of the article record
	 *
	 * @return int total number of layout records that have article ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $layout_id = $this->model_cms_article->getLayoutId($article_id);
	 */
	public function getLayoutId(int $topic_id): int {
		$mapper = new TopicMapper();

		return $mapper->getLayoutId(
			$topic_id,
			(int)$this->config->get('config_store_id')
		);
	}
}
