<?php
namespace Opencart\Catalog\Controller\Checkout;
/**
 * Alpha Engine: Imports
 */
use Alpha\Controller\BaseController;
use Alpha\Model\Domain\Repositories\CartRepository;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Model\Domain\Repositories\CountryRepository;
use Alpha\Model\Domain\Repositories\ZoneRepository;
use Alpha\Model\Domain\Repositories\CustomFieldRepository;
/**
 * Class ShippingAddress
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class ShippingAddress extends BaseController {
	private CartRepository $cartRepository;
	private AddressRepository $addressRepository;
	private CountryRepository $countryRepository;
	private ZoneRepository $zoneRepository;
	private CustomFieldRepository $customFieldRepository;

	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
		$factory = $this->registry->get('alpha_repository_factory');
		$this->cartRepository = $factory->get(CartRepository::class);
		$this->addressRepository = $factory->get(AddressRepository::class);
		$this->countryRepository = $factory->get(CountryRepository::class);
		$this->zoneRepository = $factory->get(ZoneRepository::class);
		$this->customFieldRepository = $factory->get(CustomFieldRepository::class);
	}

	/**
	 * Index
	 *
	 * @return string
	 */
	public function index(): string {
		$data = [];
		$this->loadLanguageData('checkout/shipping_address', $data);

		$data['error_upload_size'] = sprintf($data['error_upload_size'], $this->config->get('config_file_max_size'));
		$data['config_file_max_size'] = ((int)$this->config->get('config_file_max_size') * 1024 * 1024);
		$data['payment_address_required'] = $this->config->get('config_checkout_payment_address');

		$this->session->data['upload_token'] = oc_token(32);

		$data['upload'] = $this->url->link('tool/upload', 'language=' . $this->config->get('config_language') . '&upload_token=' . $this->session->data['upload_token']);

		// Address
		$data['addresses'] = $this->addressRepository->getAddresses((int)$this->customer->getId(), (int)$this->config->get('config_language_id'));

		$data['address_id'] = $this->session->data['shipping_address']['address_id'] ?? 0;
		$data['postcode']   = $this->session->data['shipping_address']['postcode'] ?? '';
		$data['country_id'] = $this->session->data['shipping_address']['country_id'] ?? (int)$this->config->get('config_country_id');
		$data['zone_id']    = $this->session->data['shipping_address']['zone_id'] ?? '';

		// Country
		$data['countries'] = $this->countryRepository->getCountries();

		// Zone
		$data['zones'] = $this->zoneRepository->getZonesByCountryId($data['country_id']);

		// Custom Fields
		$data['custom_fields'] = [];

		$custom_fields = $this->customFieldRepository->getCustomFields($this->customer->getGroupId());

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'address') {
				$data['custom_fields'][] = $custom_field;
			}
		}

		$data['language'] = $this->config->get('config_language');

		return $this->load->view('checkout/shipping_address', $data);
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('checkout/shipping_address');

		$json = [];

		$required = [
			'firstname'     => '',
			'lastname'      => '',
			'company'       => '',
			'address_1'     => '',
			'number'        => 0,
			'address_2'     => '',
			'neighborhood'  => '',
			'city'          => '',
			'postcode'      => '',
			'country_id'    => 0,
			'zone_id'       => 0,
			'custom_field'  => []
		];

		$post_info = $this->request->post + $required;

		// Validate cart has products and has stock.
		if (!$this->cartRepository->hasProducts() || (!$this->cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$this->cartRepository->hasMinimum()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		// Validate if customer is logged in or customer session data is not set
		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		// Validate if shipping not required
		if (!$this->cartRepository->hasShipping()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			if (!oc_validate_length($post_info['firstname'], 1, 32)) {
				$json['error']['firstname'] = $this->language->get('error_firstname');
			}

			if (!oc_validate_length($post_info['lastname'], 1, 32)) {
				$json['error']['lastname'] = $this->language->get('error_lastname');
			}

			if (!oc_validate_length($post_info['address_1'], 3, 128)) {
				$json['error']['address_1'] = $this->language->get('error_address_1');
			}

			if (!oc_validate_length($post_info['city'], 2, 128)) {
				$json['error']['city'] = $this->language->get('error_city');
			}

			// Country
			$country_info = $this->countryRepository->getCountry((int)$post_info['country_id']);

			if ($country_info && $country_info['postcode_required'] && !oc_validate_length($post_info['postcode'], 2, 10)) {
				$json['error']['postcode'] = $this->language->get('error_postcode');
			}

			if (!$country_info) {
				$json['error']['country'] = $this->language->get('error_country');
			}

			// Zone
			$zone_total = $this->zoneRepository->getTotalZonesByCountryId((int)$post_info['country_id']);

			if ($zone_total && !$post_info['zone_id']) {
				$json['error']['zone'] = $this->language->get('error_zone');
			}

			// Custom field validation
			$custom_fields = $this->customFieldRepository->getCustomFields($this->customer->getGroupId());

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address') {
					if ($custom_field['required'] && empty($post_info['custom_field'][$custom_field['custom_field_id']])) {
						$json['error']['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
					} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !oc_validate_regex($post_info['custom_field'][$custom_field['custom_field_id']], $custom_field['validation'])) {
						$json['error']['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
					}
				}
			}
		}

		if (!$json) {
			// If no default address has been found, add it
			$address_id = $this->customer->getAddressId();

			if (!$address_id) {
				$post_info['default'] = 1;
			}

			$json['address_id'] = $this->addressRepository->save($post_info, (int)$this->customer->getId());
			$json['addresses'] = $this->addressRepository->getAddresses((int)$this->customer->getId(), (int)$this->config->get('config_language_id'));

			$this->session->data['shipping_address'] = $this->addressRepository->getAddress($json['address_id'], (int)$this->config->get('config_language_id'));

			$json['success'] = $this->language->get('text_success');

			// Clear payment and shipping methods
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->jsonResponse($json);
	}

	/**
	 * Address
	 *
	 * @return void
	 */
	public function address(): void {
		$this->load->language('checkout/shipping_address');

		$json = [];

		$address_id = (int)($this->request->get['address_id'] ?? 0);

		// Validate cart has products and has stock.
		if (!$this->cartRepository->hasProducts() || (!$this->cartRepository->hasStock() && !$this->config->get('config_stock_checkout')) || !$this->cartRepository->hasMinimum()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		// Validate if customer is logged in or customer session data is not set
		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		// Validate if shipping is not required
		if (!$this->cartRepository->hasShipping()) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		if (!$json) {
			// Shipping Address
			$address_info = $this->addressRepository->getAddress($address_id, (int)$this->config->get('config_language_id'));

			if (!$address_info) {
				$json['error'] = $this->language->get('error_address');

				unset($this->session->data['shipping_address']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
			}
		}

		if (!$json) {
			$this->session->data['shipping_address'] = $address_info;

			$json['success'] = $this->language->get('text_success');

			// Clear payment and shipping methods
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->jsonResponse($json);
	}
}
