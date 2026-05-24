<?php
namespace Opencart\catalog\controller\api;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\ZoneRepository;
use Alpha\Model\Domain\Repositories\AddressRepository;

/**
 * Class Payment Address
 *
 * Can be loaded using $this->load->controller('api/payment_address');
 *
 * @package Opencart\Catalog\Controller\Api
 */
class PaymentAddress extends BaseController {
	/**
	 * Index
	 *
	 * @return array<string, mixed>
	 */
	public function index(): array {
		$this->load->language('api/payment_address');

		$output = [];

		// Add keys for missing post vars
		$required = [
			'payment_firstname'  => '',
			'payment_lastname'   => '',
			'payment_company'    => '',
			'payment_address_1'  => '',
			'payment_address_2'  => '',
			'payment_postcode'   => '',
			'payment_city'       => '',
			'payment_zone_id'    => 0,
			'payment_country_id' => 0
		];

		$post_info = $this->request->post + $required;

		if (!oc_validate_length((string)$post_info['payment_firstname'], 1, 32)) {
			$output['error']['payment_firstname'] = $this->language->get('error_firstname');
		}

		if (!oc_validate_length((string)$post_info['payment_lastname'], 1, 32)) {
			$output['error']['payment_lastname'] = $this->language->get('error_lastname');
		}

		if (!oc_validate_length((string)$post_info['payment_address_1'], 3, 128)) {
			$output['error']['payment_address_1'] = $this->language->get('error_address_1');
		}

		if (!oc_validate_length((string)$post_info['payment_city'], 2, 128)) {
			$output['error']['payment_city'] = $this->language->get('error_city');
		}

		// Country
		$countryRepository = $this->getRepository(CountryRepository::class);
		$country_info = $countryRepository->getCountry((int)$post_info['payment_country_id']);

		if ($country_info && $country_info['postcode_required'] && !oc_validate_length((string)$post_info['payment_postcode'], 2, 10)) {
			$output['error']['payment_postcode'] = $this->language->get('error_postcode');
		}

		if (!$country_info) {
			$output['error']['payment_country'] = $this->language->get('error_country');
		}

		// Zone
		$zoneRepository = $this->getRepository(ZoneRepository::class);
		$zone_total = count($zoneRepository->getZonesByCountryId((int)$post_info['payment_country_id']));

		if ($zone_total && !$post_info['payment_zone_id']) {
			$output['error']['payment_zone'] = $this->language->get('error_zone');
		}

		// Custom field validation
		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields((int)$this->config->get('config_customer_group_id'));

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'address') {
				if ($custom_field['required'] && empty($post_info['payment_custom_field'][$custom_field['custom_field_id']])) {
					$output['error']['payment_custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !oc_validate_regex($post_info['payment_custom_field'][$custom_field['custom_field_id']], $custom_field['validation'])) {
					$output['error']['payment_custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
				}
			}
		}

		if (!$output) {
			if ($country_info) {
				$country = $country_info['name'];
				$iso_code_2 = $country_info['iso_code_2'];
				$iso_code_3 = $country_info['iso_code_3'];
				$address_format = $country_info['address_format'] ?? '';
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			// Zone
			$zone_info = $zoneRepository->getZone((int)$post_info['payment_zone_id']);

			if ($zone_info) {
				$zone = $zone_info['name'];
				$zone_code = $zone_info['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			$this->session->data['payment_address'] = [
				'address_id'     => $post_info['payment_address_id'],
				'firstname'      => $post_info['payment_firstname'],
				'lastname'       => $post_info['payment_lastname'],
				'company'        => $post_info['payment_company'],
				'address_1'      => $post_info['payment_address_1'],
				'address_2'      => $post_info['payment_address_2'],
				'postcode'       => $post_info['payment_postcode'],
				'city'           => $post_info['payment_city'],
				'zone_id'        => $post_info['payment_zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => (int)$post_info['payment_country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'custom_field'   => $post_info['payment_custom_field'] ?? []
			];

			$output['success'] = $this->language->get('text_success');
		}

		return $output;
	}

	/**
	 * Set Address
	 *
	 * @return array<string, mixed>
	 */
	public function setAddress(): array {
		$this->load->language('api/payment_address');

		$output = [];

		// Payment Address
		if (isset($this->request->post['payment_address_id'])) {
			$address_id = (int)$this->request->post['payment_address_id'];
		} else {
			$address_id = 0;
		}

		$addressRepository = $this->getRepository(AddressRepository::class);
		$address_info = $addressRepository->getAddress($address_id);

		if (!$address_info || $address_info['customer_id'] != $this->customer->getId()) {
			$output['error'] = $this->language->get('error_address');
		}

		if (!$output) {
			$this->session->data['payment_address'] = $address_info;

			$output['success'] = $this->language->get('text_success');
		}

		return $output;
	}
}
