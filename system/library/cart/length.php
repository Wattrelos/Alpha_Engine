<?php
namespace Opencart\System\Library\Cart;

use Alpha\Mappers\EntityMappers\LengthClassMapper;
/*
 * Class Length
 *
 * @package Opencart\System\Library\Cart
 */
class Length {
	/**
	 * @var object
	 */
	private object $db;
	/**
	 * @var object
	 */
	private object $config;
	/**
	 * @var array<int, array<string, mixed>>
	 */
	private array $lengths = [];

	/**
	 * Constructor
	 *
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct(\Opencart\System\Engine\Registry $registry) {
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');

		$mapper = new LengthClassMapper();
		$results = $mapper->getLengthClasses((int)$this->config->get('config_language_id'));

		foreach ($results as $result) {
			$this->lengths[$result['id']] = [ // Usando 'id' como chave, alinhado com o WeightClassMapper
				'length_class_id' => $result['id'], // E também para o valor da PK
				'title'           => $result['title'],
				'unit'            => $result['unit'],
				'value'           => $result['value']
			];
		}
	}

	/**
	 * Convert
	 *
	 * @param float $value
	 * @param int   $from
	 * @param int   $to
	 *
	 * @return float
	 *
	 * @example
	 *
	 * $length = $this->length->convert($value, $from, $to);
	 */
	public function convert(float $value, int $from, int $to): float {
		if ($from == $to) {
			return $value;
		}

		if (isset($this->lengths[$from])) {
			$from = $this->lengths[$from]['value'];
		} else {
			$from = 1;
		}

		if (isset($this->lengths[$to])) {
			$to = $this->lengths[$to]['value'];
		} else {
			$to = 1;
		}

		return $value * ($to / $from);
	}

	/**
	 * Format
	 *
	 * @param float  $value
	 * @param int    $length_class_id primary key of the length class record
	 * @param string $decimal_point
	 * @param string $thousand_point
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $length = $this->length->format($value, $length_class_id, $decimal_point, $thousand_point);
	 */
	public function format(float $value, int $length_class_id, string $decimal_point = '.', string $thousand_point = ','): string {
		if (isset($this->lengths[$length_class_id])) {
			return number_format($value, 2, $decimal_point, $thousand_point) . $this->lengths[$length_class_id]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}

	/**
	 * Get Unit
	 *
	 * @param int $length_class_id primary key of the length class record
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $unit = $this->length->getUnit($length_class_id);
	 */
	public function getUnit(int $length_class_id): string {
		if (isset($this->lengths[$length_class_id])) {
			return $this->lengths[$length_class_id]['unit'];
		} else {
			return '';
		}
	}
}
