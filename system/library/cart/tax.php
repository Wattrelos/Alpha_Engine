<?php
namespace Opencart\System\Library\Cart;

use Alpha\Mappers\EntityMappers\TaxRuleMapper;

/**
 * Class Tax
 *
 * @package Opencart\System\Library\Cart
 */
class Tax {
	/**
	 * @var object
	 */
	private object $db;
	/**
	 * @var object
	 */
	private object $config;
	/**
	 * @var array<int, array<int, array<string, mixed>>>
	 */
	private array $tax_rates = [];

	/**
	 * Constructor
	 *
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct(\Opencart\System\Engine\Registry $registry) {
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');
	}

	/**
	 * Set Shipping Address
	 *
	 * @param int $country_id primary key of the country record
	 * @param int $zone_id    primary key of the zone record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->tax->setShippingAddress($country_id, $zone_id);
	 */
	// Regra de negócio: No banco de dados, todas as PK tem o nome de id e todas as FK tem o [nome da tabela pai] + "_id";
	public function setShippingAddress(int $countryId, int $zoneId): void {		
		$repository = new TaxRuleMapper();
		$customerGroupId = (int)$this->config->get('config_customer_group_id');
		$taxRules = $repository->getRules('shipping', $countryId, $zoneId, $customerGroupId);
		// $taxRules['data'] agora contém arrays associativos (convertidos de entidades)
		foreach ($taxRules['data'] as $result) {
			$this->tax_rates[$result['id']][$result['tax_rate_id']] = [
				'tax_rate_id' => $result['tax_rate_id'],
				'name'        => $result['tax_rate']['name'],
				'rate'        => $result['tax_rate']['rate'],
				'type'        => $result['tax_rate']['type'],
				'priority'    => $result['priority']
			];
		}
	}

	/**
	 * Set Payment Address
	 *
	 * @param int $country_id primary key of the country record
	 * @param int $zone_id    primary key of the zone record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->tax->setPaymentAddress($country_id, $zone_id);
	 */

	public function setPaymentAddress(int $country_id, int $zone_id): void {
		$repository = new TaxRuleMapper();
		$customerGroupId = (int)$this->config->get('config_customer_group_id');
		$taxRules = $repository->getRules('payment', $country_id, $zone_id, $customerGroupId);

		foreach ($taxRules['data'] as $result) {
			$this->tax_rates[$result['id']][$result['tax_rate_id']] = [
				'tax_rate_id' => $result['tax_rate_id'],
				'name'        => $result['tax_rate']['name'],
				'rate'        => $result['tax_rate']['rate'],
				'type'        => $result['tax_rate']['type'],
				'priority'    => $result['priority']
			];
		}
	}

	/**
	 * Set Store Address
	 *
	 * @example
	 *
	 * $this->tax->setStoreAddress($country_id, $zone_id);
	 */
	public function setStoreAddress(int $country_id, int $zone_id): void {
		$repository = new TaxRuleMapper();
		$customerGroupId = (int)$this->config->get('config_customer_group_id');
		// Passamos o ID 464 como uma zona adicional para o endereço da loja
		$taxRules = $repository->getRules('store', $country_id, $zone_id, $customerGroupId, ['464']);

		foreach ($taxRules['data'] as $result) {
			$this->tax_rates[$result['id']][$result['tax_rate_id']] = [
				'tax_rate_id' => $result['tax_rate_id'],
				'name'        => $result['tax_rate']['name'],
				'rate'        => $result['tax_rate']['rate'],
				'type'        => $result['tax_rate']['type'],
				'priority'    => $result['priority']
			];
		}
	}

	/**
	 * Calculate
	 *
	 * @param float $value
	 * @param int   $tax_class_id primary key of the tax class record
	 * @param bool  $calculate
	 *
	 * @return float
	 *
	 * @example
	 *
	 * $tax = $this->tax->calculate($value, $tax_class_id, $calculate);
	 */
	public function calculate(float $value, int $tax_class_id, bool $calculate = true): float {
		if ($tax_class_id && $calculate) {
			$amount = 0;

			$tax_rates = $this->getRates($value, $tax_class_id);

			foreach ($tax_rates as $tax_rate) {
				$amount += $tax_rate['amount'];
			}

			return $value + $amount;
		} else {
			return $value;
		}
	}

	/**
	 * Get Tax
	 *
	 * @param float $value
	 * @param int   $tax_class_id primary key of the tax class record
	 *
	 * @return float
	 *
	 * @example
	 *
	 * $tax = $this->tax->getTax($value, $tax_class_id);
	 */
	public function getTax(float $value, int $tax_class_id): float {
		$amount = 0;

		$tax_rates = $this->getRates($value, $tax_class_id);

		foreach ($tax_rates as $tax_rate) {
			$amount += $tax_rate['amount'];
		}

		return $amount;
	}

	/**
	 * Get Rate Name
	 *
	 * @param int $tax_rate_id primary key of the tax rate record
	 *
	 * @return false|string
	 *
	 * @example
	 *
	 * $rate = $this->tax->getRateName($tax_rate_id);
	 */
	public function getRateName(int $tax_rate_id) {
		$tax_query = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "tax_rate` WHERE `tax_rate_id` = '" . (int)$tax_rate_id . "'");

		if ($tax_query->num_rows) {
			return $tax_query->row['name'];
		} else {
			return false;
		}
	}

	/**
	 * Get Rates
	 *
	 * @param float $value
	 * @param int   $tax_class_id primary key of the tax class record
	 *
	 * @return array<int, array<string, mixed>>
	 *
	 * @example
	 *
	 * $rates = $this->tax->getRates($value, $tax_class_id);
	 */
	public function getRates(float $value, int $tax_class_id): array {
		$tax_rate_data = [];

		if (isset($this->tax_rates[$tax_class_id])) {
			foreach ($this->tax_rates[$tax_class_id] as $tax_rate) {
				if (isset($tax_rate_data[$tax_rate['tax_rate_id']])) {
					$amount = $tax_rate_data[$tax_rate['tax_rate_id']]['amount'];
				} else {
					$amount = 0;
				}

				if ($tax_rate['type'] == 'F') {
					$amount += $tax_rate['rate'];
				} elseif ($tax_rate['type'] == 'P') {
					$amount += ($value / 100 * $tax_rate['rate']);
				}

				$tax_rate_data[$tax_rate['tax_rate_id']] = [
					'tax_rate_id' => $tax_rate['tax_rate_id'],
					'name'        => $tax_rate['name'],
					'rate'        => $tax_rate['rate'],
					'type'        => $tax_rate['type'],
					'amount'      => $amount
				];
			}
		}

		return $tax_rate_data;
	}

	/**
	 * Clear
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->tax->clear();
	 */
	public function clear(): void {
		$this->tax_rates = [];
	}
}
