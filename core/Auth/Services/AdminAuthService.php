<?php

declare(strict_types=1);

namespace Alpha\Auth\Services;

use Alpha\Model\Domain\Repositories\UserRepository;

/**
 * AdminAuthService - Especialização para login e sessões de administradores (User).
 */
class AdminAuthService extends AbstractAuthService
{
    private ?UserRepository $userRepository;

    public function __construct(?UserRepository $userRepository = null)
    {
        parent::__construct();
        $this->userRepository = $userRepository;

        // Customizações para o contexto administrativo
        $this->cookieName = 'admin_session_id';
        $this->redisPrefix = 'sessao:admin:';
        $this->sessionKey = 'logged_admin';
        $this->sessionLifetime = 7200; // 2 horas
    }

    /**
     * Valida as credenciais do administrador.
     */
    public function authenticate(string $username, string $password, string $ip = ''): ?array
    {
        if ($this->userRepository === null) {
            return null;
        }

        // Bloqueia se atingiu o limite de 5 tentativas malsucedidas no período de 1 hora
        if ($this->userRepository->isLockedOut($username, 5)) {
            return null;
        }

        $user = $this->userRepository->findByUsername($username);

        // Verifica se o usuário existe, está ativo e se a senha confere
        if ($user && $user->isStatus() && password_verify($password, $user->getPassword())) {
            // Sucesso: Limpa o histórico de tentativas do usuário
            $this->userRepository->resetLoginAttempts($username);

            return [
                'id'       => $user->getId(),
                'username' => $user->getUsername(),
                'name'     => trim($user->getFirstname() . ' ' . $user->getLastname()),
                'role'     => 'admin'
            ];
        }

        // Falha: Registra a tentativa mal-sucedida
        $this->userRepository->addLoginAttempt($username, $ip);

        return null;
    }
}
