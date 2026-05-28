<?php
namespace Opencart\Catalog\Model\Setting;

use Alpha\Model\Domain\Repositories\ApiSessionRepository;

/**
 * Class Api
 *
 * Can be called using $this->load->model('setting/api');
 *
 * @package Opencart\Catalog\Model\Setting
 */
class Api extends \Opencart\System\Engine\Model {
	/**
	 * Login
	 *
	 * @param string $username
	 * @param string $key
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $api_info = $this->model_setting_api->login($username, $key);
	 */
	public function login(string $username, string $key): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(ApiSessionRepository::class);
		return $repository->login($username, $key);
	}

	/**
	 * Get Api By Token
	 *
	 * @param string $token
	 *
	 * @return array<string, mixed>
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $api_info = $this->model_setting_api->getApiByToken($token);
	 */
	public function getApiByToken(string $token): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(ApiSessionRepository::class);
		return $repository->getApiByToken($token, oc_get_ip());
	}

	/**
	 * Get Sessions
	 *
	 * Get the record of the api session records in the database.
	 *
	 * @param int $api_id primary key of the Api record
	 *
	 * @return array<int, array<string, mixed>> session records that have api ID
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $api_sessions = $this->model_setting_api->getSessions($api_id);
	 */
	public function getSessions(int $api_id): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(ApiSessionRepository::class);
		return $repository->getSessions($api_id);
	}

	/**
	 * Delete API Sessions
	 *
	 * Delete api session records in the database.
	 *
	 * @param int $api_id primary key of the Api record
	 *
	 * @return array<int, array<string, mixed>>
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $this->model_setting_api->deleteSessions($api_id);
	 */
	public function deleteSessions(int $api_id): array {
		$repository = $this->registry->get('alpha_repository_factory')->get(ApiSessionRepository::class);
		// Preserva o comportamento do OpenCart, que estranhamente faz SELECT em vez de DELETE neste método
		return $repository->getSessions($api_id);
	}

	/**
	 * Update Session
	 *
	 * @param string $api_session_id primary key of the Api Session record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $this->model_setting_api->updateSession($api_session_id);
	 */
	public function updateSession(string $api_session_id): void {
		$repository = $this->registry->get('alpha_repository_factory')->get(ApiSessionRepository::class);
		$repository->updateSession($api_session_id);
	}

	/**
	 * Clean API Sessions
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('setting/api');
	 *
	 * $this->model_setting_api->cleanSessions();
	 */
	public function cleanSessions(): void {
		$repository = $this->registry->get('alpha_repository_factory')->get(ApiSessionRepository::class);
		$repository->cleanSessions();
	}
}
