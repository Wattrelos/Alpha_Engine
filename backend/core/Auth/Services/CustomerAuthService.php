<?php

declare(strict_types=1);

namespace Alpha\Auth\Services;

use Alpha\Model\Domain\Repositories\CustomerRepository;

/**
 * CustomerAuthService - Especialização para login e sessões de clientes (Customer).
 */
class CustomerAuthService extends AbstractAuthService
{
    private ?CustomerRepository $customerRepository;

    public function __construct(?CustomerRepository $customerRepository = null)
    {
        parent::__construct();
        $this->customerRepository = $customerRepository;

        // Customizações para o contexto do cliente
        $this->cookieName = 'session_id';
        $this->redisPrefix = 'sessao:';
        $this->sessionKey = 'logged_user';
        $this->sessionLifetime = 7200; // 2 horas
    }

    /**
     * Valida as credenciais do cliente.
     */
    public function authenticate(string $email, string $password, string $ip = ''): ?array
    {
        if ($this->customerRepository === null) {
            return null;
        }

        // Bloqueia se atingiu o limite de 5 tentativas malsucedidas
        if ($this->customerRepository->isLockedOut($email, 5)) {
            return null;
        }

        $customer = $this->customerRepository->authenticate($email, $password);

        if ($customer && $customer->isStatus()) {
            // Sucesso: Reseta o histórico de tentativas do cliente
            $this->customerRepository->resetLoginAttempts($email);

            return [
                'id'                => $customer->getId(),
                'name'              => trim($customer->getFirstname() . ' ' . $customer->getLastname()),
                'email'             => $customer->getEmail(),
                'telephone'         => $customer->getTelephone(),
                'customer_group_id' => $customer->getCustomerGroupId(),
                'role'              => 'client_premium'
            ];
        }

        // Falha: Registra a tentativa mal-sucedida
        $this->customerRepository->addLoginAttempt($email, $ip);

        return null;
    }

    /**
     * Cria e persiste a sessão do cliente, sincronizando chaves legadas no $_SESSION
     * e preservando tokens CSRF e estado pré-existente da sessão.
     */
    public function createSession(array $userData): string
    {
        $existingSessionData = $_SESSION ?? [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            if (empty($sessionId)) {
                $sessionId = bin2hex(random_bytes(32));
                session_id($sessionId);
            }
            if ($this->useRedis && $this->redis) {
                try {
                    $this->redis->set($this->redisPrefix . $sessionId, json_encode($userData));
                    $this->redis->expire($this->redisPrefix . $sessionId, $this->sessionLifetime);
                } catch (\Throwable) {
                    // Fallback silencioso
                }
            }
        } else {
            $sessionId = parent::createSession($userData);
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_name($this->cookieName);
                session_id($sessionId);
                @session_start();
            }
        }

        // Restaura dados preservados da sessão (ex: tokens CSRF, carrinho, endereço)
        foreach ($existingSessionData as $k => $v) {
            $_SESSION[$k] = $v;
        }

        $_SESSION['logged_user'] = json_encode($userData);
        $_SESSION['customer_id'] = $userData['id'];
        $_SESSION['customer_group_id'] = $userData['customer_group_id'] ?? 1;
        $_SESSION['customer_firstname'] = explode(' ', trim($userData['name']))[0] ?? '';
        $_SESSION['customer_lastname'] = explode(' ', trim($userData['name']), 2)[1] ?? '';
        $_SESSION['customer_email'] = $userData['email'] ?? '';
        $_SESSION['customer_telephone'] = $userData['telephone'] ?? '';
        $_SESSION['email'] = $userData['email'] ?? '';
        $_SESSION['telephone'] = $userData['telephone'] ?? '';

        return $sessionId;
    }

    /**
     * Remove a sessão ativa do cliente, limpando as chaves legadas.
     */
    public function destroySession(string $sessionId): void
    {
        parent::destroySession($sessionId);

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_name($this->cookieName);
            if (!empty($sessionId)) {
                session_id($sessionId);
            }
            @session_start();
        }

        unset($_SESSION['logged_user']);
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_group_id']);
        unset($_SESSION['customer_firstname']);
        unset($_SESSION['customer_lastname']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_telephone']);
        unset($_SESSION['email']);
        unset($_SESSION['telephone']);
    }
}
