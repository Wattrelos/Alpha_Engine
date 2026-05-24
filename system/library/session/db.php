<?php
/*
CREATE TABLE IF NOT EXISTS `session` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `token_session` varchar(32) NOT NULL,
  `data` text NOT NULL,
  `expire` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_session` (`token_session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/
namespace Opencart\System\Library\Session;

use Alpha\Model\Domain\Repositories\SessionRepository;

/**
 * Class DB
 *
 * @package Opencart\System\Library\Session
 */
class DB {
	protected \Opencart\System\Engine\Registry $registry;
	protected object $config;
	private SessionRepository $sessionRepository;

	/**
	 * Constructor
	 *
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct(\Opencart\System\Engine\Registry $registry) {
		$this->registry = $registry;
		$this->config = $registry->get('config');

		// Alpha Engine: Resolução do repositório via Registry para aderência ao padrão de domínio
		$repository = $registry->get('repository');

		if ($repository) {
			$this->sessionRepository = $repository->get(SessionRepository::class);
		} else {
			// Fallback: Se o container de repositórios não estiver no Registry, instanciamos o repositório diretamente
			$mapperFactory = new \Alpha\Mappers\MapperFactory($registry);
			$this->sessionRepository = new SessionRepository($mapperFactory, $registry);
		}
	}

	/**
	 * Read
	 *
	 * @param string $session_id
	 *
	 * @return array<mixed>
	 */
	public function read(string $session_id): array {
		// Alpha Engine: Delegação da leitura para o repositório, que gerencia expiração e tipagem
		return $this->sessionRepository->read($session_id);
	}

	/**
	 * Write
	 *
	 * @param string       $session_id
	 * @param array<mixed> $data
	 *
	 * @return bool
	 */
	public function write(string $session_id, array $data): bool {
		if ($session_id) {
			$expire = (int)$this->config->get('config_session_expire') ?: 3600;

			// Alpha Engine: Persistência centralizada via Repositório de Domínio
			$this->sessionRepository->write($session_id, $data, $expire);
		}

		return true;
	}

	/**
	 * Destroy
	 *
	 * @param string $session_id
	 *
	 * @return bool
	 */
	public function destroy(string $session_id): bool {
		// Alpha Engine: Remoção lógica/física delegada ao domínio
		$this->sessionRepository->destroy($session_id);

		return true;
	}

	/**
	 * GC
	 *
	 * @return bool
	 */
	public function gc(): bool {
		$divisor = (int)$this->config->get('config_session_divisor') ?: 100;
		$probability = (int)$this->config->get('config_session_probability') ?: 1;

		if (round(mt_rand(1, $divisor / $probability)) == 1) {
			// Alpha Engine: Limpeza de dados obsoletos via repositório
			$this->sessionRepository->gc();
		}

		return true;
	}
}
