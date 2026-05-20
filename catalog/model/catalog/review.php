<?php
namespace Opencart\Catalog\Model\Catalog;

use Alpha\Mappers\ReviewMapper;
use Alpha\Model\Domain\Entities\Review;

/**
 * Class Review
 *
 * Can be called using $this->load->model('catalog/review');
 *
 * @package Opencart\Catalog\Model\Catalog
 */
class Review extends \Opencart\System\Engine\Model {
	/**
	 * Add Review
	 *
	 * Create a new review record in the database.
	 *
	 * @param int                  $product_id primary key of the product record
	 * @param array<string, mixed> $data       array of data
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $review_data = [
	 *     'author'      => 'Author Name',
	 *     'customer_id' => 1,
	 *     'product_id'  => 1,
	 *     'text'        => '',
	 *     'rating'      => 4
	 * ];
	 *
	 * $this->load->model('catalog/review');
	 *
	 * $this->model_catalog_review->addReview($product_id, $review_data);
	 */
	public function addReview(int $product_id, array $data): int {
		$reviewMapper = new ReviewMapper();
		
		$review = new Review();
		$review->setProductId($product_id)
			->setCustomerId((int)$this->customer->getId())
			->setAuthor($data['author'])
			->setText($data['text'])
			->setRating((int)$data['rating'])
			->setStatus(false)
			->setDateAdded(date('Y-m-d H:i:s'))
			->setDateModified(date('Y-m-d H:i:s'));

		return $reviewMapper->save($review) ?? 0;
	}

	/**
	 * Get Reviews By Product ID
	 *
	 * Get the record of the reviews by product records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>> review records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/review');
	 *
	 * $results = $this->model_catalog_review->getReviewsByProductId($product_id, $start, $limit);
	 */
	public function getReviewsByProductId(int $product_id, int $start = 0, int $limit = 20): array {
		$mapper = new ReviewMapper();

		// Converte para o padrão de página do Mapper (OpenCart usa start, Alpha usa page)
		$page = floor($start / $limit) + 1;
		return $mapper->getReviewsByProductId($product_id, (int)$page, $limit)['data'];
	}

	/**
	 * Get Total Reviews By Product ID
	 *
	 * Get the total number of total review records in the database.
	 *
	 * @param int $product_id primary key of the product record
	 *
	 * @return int total number of review records that have product ID
	 *
	 * @example
	 *
	 * $this->load->model('catalog/review');
	 *
	 * $review_total = $this->model_catalog_review->getTotalReviewsByProductId($product_id);
	 */
	public function getTotalReviewsByProductId(int $product_id): int {
		$mapper = new ReviewMapper();

		return $mapper->getTotalReviewsByProductId($product_id);
	}
}
