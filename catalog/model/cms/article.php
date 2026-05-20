<?php
namespace Opencart\Catalog\Model\Cms;

use Alpha\Mappers\EntityMappers\ArticleMapper;

/**
 * Class Article
 *
 * Can be called using $this->load->model('cms/article');
 *
 * @package Opencart\Catalog\Model\Cms
 */
class Article extends \Opencart\System\Engine\Model {
	/**
	 * Get Article
	 *
	 * Get the record of the article record in the database.
	 *
	 * @param int $article_id primary key of the article record
	 *
	 * @return array<string, mixed> article record that has article ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $article_info = $this->model_cms_article->getArticle($article_id);
	 */
	public function getArticle(int $article_id): array {
		$mapper = new ArticleMapper();

		return $mapper->getArticle(
			$article_id,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Get Articles
	 *
	 * Get the record of the article records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> article records
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $results = $this->model_cms_article->getArticles();
	 */
	public function getArticles(array $data = []): array {
		$mapper = new ArticleMapper();

		return $mapper->getArticles(
			$data,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Edit Rating
	 *
	 * Edit article rating record in the database.
	 *
	 * @param int $article_id primary key of the article record
	 * @param int $rating
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->editRating($article_id, $rating);
	 */
	public function editRating(int $article_id, int $rating): void {
		$mapper = new ArticleMapper();
		$mapper->updateRating($article_id, $rating);
	}

	/**
	 * Get Total Articles
	 *
	 * Get the total number of total article records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of article records
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $article_total = $this->model_cms_article->getTotalArticles();
	 */
	public function getTotalArticles(array $data = []): int {
		$mapper = new ArticleMapper();

		return $mapper->getTotalArticles(
			$data,
			(int)$this->config->get('config_language_id'),
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Get Layout ID
	 *
	 * Get the record of the article layout record in the database.
	 *
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
	public function getLayoutId(int $article_id): int {
		$mapper = new ArticleMapper();
		return $mapper->getLayoutId(
			$article_id, 
			(int)$this->config->get('config_store_id')
		);
	}

	/**
	 * Add Comment
	 *
	 * Create a new article comment record in the database.
	 *
	 * @param int                  $article_id primary key of the article record
	 * @param array<string, mixed> $data       array of data
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $article_data = [
	 *     'parent_id' => 0,
	 *     'author'    => 'Author Name',
	 *     'comment'   => '',
	 *     'ip'        => '',
	 *     'status'    => 0
	 * ];
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->addComment($article_id, $article_data);
	 */
	public function addComment(int $article_id, array $data): int {
		$mapper = new ArticleMapper();
		$last_id = $mapper->addComment(
			$article_id, 
			$data, 
			(int)$this->customer->getId(), 
			oc_get_ip()
		);

		$this->cache->delete('comment');
		return $last_id;
	}

	/**
	 * Edit Comment Rating
	 *
	 * Edit article comment rating record in the database.
	 *
	 * @param int $article_id         primary key of the article record
	 * @param int $article_comment_id primary key of the article comment record
	 * @param int $rating
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->editCommentRating($article_id, $article_comment_id, $rating);
	 */
	public function editCommentRating(int $article_id, int $article_comment_id, int $rating): void {
		$mapper = new ArticleMapper();
		$mapper->editCommentRating($article_id, $article_comment_id, $rating);
	}

	/**
	 * Get Comment
	 *
	 * Get the record of the article comment record in the database.
	 *
	 * @param int $article_comment_id primary key of the article comment record
	 *
	 * @return array<string, mixed> comment record that has article comment ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $comment_info = $this->model_cms_article->getComment($article_comment_id);
	 */
	public function getComment(int $article_comment_id): array {
		$mapper = new ArticleMapper();
		return $mapper->getComment($article_comment_id);
	}

	/**
	 * Get Comments
	 *
	 * Get the record of the article comment records in the database.
	 *
	 * @param int                  $article_id primary key of the article record
	 * @param array<string, mixed> $data       array of filters
	 *
	 * @return array<int, array<string, mixed>> comment records that have article ID
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'parent_id' => 0,
	 *     'sort'      => 'date_added',
	 *     'order'     => 'DESC',
	 *     'start'     => 0,
	 *     'limit'     => 10
	 * ];
	 *
	 * $this->load->model('cms/article');
	 *
	 * $results = $this->model_cms_article->getComments($article_id, $filter_data);
	 */
	public function getComments(int $article_id, array $data = []): array {
		$mapper = new ArticleMapper();
		
		// Alpha Engine: Mantemos o cache no Bridge para evitar N chamadas ao banco durante a transição
		$key = md5(json_encode([$article_id, $data]));
		$comment_data = $this->cache->get('article.comment.' . $key);

		if (!$comment_data) {
			$comment_data = $mapper->getComments($article_id, $data);
			$this->cache->set('article.comment.' . $key, $comment_data);
		}

		return $comment_data;
	}

	/**
	 * Get Total Comments
	 *
	 * Get the total number of total article comment records in the database.
	 *
	 * @param int                  $article_id primary key of the article record
	 * @param array<string, mixed> $data       array of filters
	 *
	 * @return int total number of comment records that have article ID
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'parent_id' => 0,
	 *     'sort'      => 'date_added',
	 *     'order'     => 'DESC',
	 *     'start'     => 0,
	 *     'limit'     => 10
	 * ];
	 *
	 * $this->load->model('cms/article');
	 *
	 * $comment_total = $this->model_cms_article->getTotalComments($article_id, $filter_data);
	 */
	public function getTotalComments(int $article_id, array $data = []): int {
		$mapper = new ArticleMapper();
		return $mapper->getTotalComments($article_id, $data);
	}

	/**
	 * Add Rating
	 *
	 * Create a new article rating record in the database.
	 *
	 * @param int  $article_id         primary key of the article record
	 * @param int  $article_comment_id primary key of the article comment record
	 * @param bool $rating
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->addRating($article_id, $article_comment_id, $rating);
	 */
	public function addRating(int $article_id, int $article_comment_id, bool $rating): void {
		$mapper = new ArticleMapper();
		$mapper->addRating(
			$article_id, 
			$article_comment_id, 
			(int)$this->config->get('config_store_id'), 
			(int)$this->customer->getId(), 
			$rating, 
			oc_get_ip()
		);
	}

	/**
	 * Delete Rating
	 *
	 * Delete article rating record in the database.
	 *
	 * @param int $article_id         primary key of the article record
	 * @param int $article_comment_id primary key of the article comment record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $this->model_cms_article->deleteRating($article_id, $article_comment_id);
	 */
	public function deleteRating(int $article_id, int $article_comment_id): void {
		$mapper = new ArticleMapper();
		$mapper->deleteRating(
			$article_id, 
			$article_comment_id, 
			(int)$this->customer->getId()
		);
	}

	/**
	 * Get Ratings
	 *
	 * Get the record of the article rating records in the database.
	 *
	 * @param int $article_id         primary key of the article record
	 * @param int $article_comment_id primary key of the article comment record
	 *
	 * @return array<int, array<string, mixed>> rating records that have article ID
	 *
	 * @example
	 *
	 * $this->load->model('cms/article');
	 *
	 * $results = $this->model_cms_article->getRatings($article_id, $article_comment_id);
	 */
	public function getRatings(int $article_id, int $article_comment_id = 0): array {
		$mapper = new ArticleMapper();
		return $mapper->getRatings($article_id, $article_comment_id);
	}
}
