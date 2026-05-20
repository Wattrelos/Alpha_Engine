<?php
namespace Opencart\System\Library\Session;

use Alpha\Model\Domain\Repositories\SessionRepository;
use Alpha\Model\Domain\Repositories\RepositoryFactory;

/**
 * Class API
 * 
 * Driver de sessão especializado para requisições de API na Alpha Engine.
 * Atua como um Proxy para o SessionRepository, garantindo que o ciclo de vida
 * das sessões de API siga os padrões de domínio e segurança do sistema.
 * 
 * Melhoras Alpha Engine:
 * - PSR-4 Compliant: Carregamento moderno via namespaces.
 * - Decoupling: Removida a dependência obrigatória do Registry legado.
 * - Type Safety: Métodos estritamente tipados conforme PHP 8.4.
 */
class API {
	private SessionRepository $sessionRepository;

	/**
	 * Constructor
	 * 
	 * @param mixed $registry Mantido para compatibilidade de assinatura com o Core, mas não utilizado.
	 */
	public function __construct($registry = null) {
		// Alpha Engine: Resolução de dependência via infraestrutura global PSR-4.
		// Utilizamos o RepositoryFactory para obter instâncias completas de repositórios de domínio.
		$this->sessionRepository = RepositoryFactory::getInstance()->get(SessionRepository::class);
	}

	public function read(string $session_id): array {
		return $this->sessionRepository->read($session_id) ?: [];
	}

	public function write(string $session_id, array $data): bool {
		$this->sessionRepository->write($session_id, $data);
		return true;
	}

	public function destroy(string $session_id): bool {
		$this->sessionRepository->destroy($session_id);
		return true;
	}

	public function gc(): bool {
		$this->sessionRepository->gc();
		return true;
	}
}