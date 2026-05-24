<?php
namespace Opencart\catalog\controller\api;

use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\ZoneRepository;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;

/**
 * Class Shipping Address
 *
 * Can be loaded using $this->load->controller('api/shipping_address');
 *
 * @package Opencart\Catalog\Controller\Api
 */
class ShippingAddress extends BaseController {
	/**
	 * Index
	 *
	 * @return array<string, mixed>
	 */
	public function index(): array {
		$this->load->language('api/shipping_address');

		$output = [];

		if ($this->getRepository(CartRepository::class)->hasShipping()) {
			// Add keys for missing post vars
			$required = [
				'shipping_firstname'    => '',
				'shipping_lastname'     => '',
				'shipping_company'      => '',
				'shipping_address_1'    => '',
				'shipping_number'       => 0,
				'shipping_address_2'    => '',
				'shipping_postcode'     => '',
				'shipping_neighborhood' => '',
				'shipping_city'         => '',
				'shipping_zone_id'      => 0,
				'shipping_country_id'   => 0
			];

			$post_info = $this->request->post + $required;

			if (!oc_validate_length($post_info['shipping_firstname'], 1, 32)) {
				$output['error']['shipping_firstname'] = $this->language->get('error_firstname');
			}

			if (!oc_validate_length($post_info['shipping_lastname'], 1, 32)) {
				$output['error']['shipping_lastname'] = $this->language->get('error_lastname');
			}

			if (!oc_validate_length($post_info['shipping_address_1'], 3, 128)) {
				$output['error']['shipping_address_1'] = $this->language->get('error_address_1');
			}

			if (!oc_validate_length($post_info['shipping_city'], 2, 128)) {
				$output['error']['shipping_city'] = $this->language->get('error_city');
			}

			// Country
			$countryRepository = $this->getRepository(CountryRepository::class);
			$country_info = $countryRepository->getCountry((int)$post_info['shipping_country_id']);

			if ($country_info && $country_info['postcode_required'] && !oc_validate_length($post_info['shipping_postcode'], 2, 10)) {
				$output['error']['shipping_postcode'] = $this->language->get('error_postcode');
			}

			if (!$country_info) {
				$output['error']['shipping_country'] = $this->language->get('error_country');
			}

			// Zone
			$zoneRepository = $this->getRepository(ZoneRepository::class);
			$zone_total = count($zoneRepository->getZonesByCountryId((int)$post_info['shipping_country_id']));

			if ($zone_total && !$post_info['shipping_zone_id']) {
				$output['error']['shipping_zone'] = $this->language->get('error_zone');
			}

			// Custom field validation
			$customFieldRepository = $this->getRepository(CustomFieldRepository::class);
			$custom_fields = $customFieldRepository->getCustomFields((int)$this->config->get('config_customer_group_id'));

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address') {
					if ($custom_field['required'] && empty($post_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
						$output['error']['shipping_custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
					} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !oc_validate_regex($post_info['shipping_custom_field'][$custom_field['custom_field_id']], $custom_field['validation'])) {
						$output['error']['shipping_custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
					}
				}
			}
		} else {
			$output['error']['warning'] = $this->language->get('error_shipping');
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
			$zone_info = $zoneRepository->getZone((int)$post_info['shipping_zone_id']);

			if ($zone_info) {
				$zone = $zone_info['name'];
				$zone_code = $zone_info['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			$this->session->data['shipping_address'] = [
				'address_id'     => $post_info['shipping_address_id'],
				'firstname'      => $post_info['shipping_firstname'],
				'lastname'       => $post_info['shipping_lastname'],
				'company'        => $post_info['shipping_company'],
				'address_1'      => $post_info['shipping_address_1'],
				'number'         => $post_info['shipping_number'],
				'address_2'      => $post_info['shipping_address_2'],
				'postcode'       => $post_info['shipping_postcode'],
				'neighborhood'   => $post_info['shipping_neighborhood'],
				'city'           => $post_info['shipping_city'],
				'zone_id'        => $post_info['shipping_zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => (int)$post_info['shipping_country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'custom_field'   => $post_info['shipping_custom_field'] ?? []
			];

			$output['success'] = $this->language->get('text_success');
		}

		return $output;
	}
}
