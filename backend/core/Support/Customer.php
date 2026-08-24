<?php

namespace Alpha\Support;

/**
 * Alpha Engine - Customer Support
 *
 * Proxy de acesso aos dados do cliente logado a partir dos dados de sessão.
 * Lê o JSON armazenado em $_SESSION['logged_user'] pelo AuthService ou chaves legadas de sessão.
 */
class Customer
{
    private ?\stdClass $user = null;
    private bool $checked = false;

    public function setUser(\stdClass|array|null $user): void
    {
        if (is_array($user)) {
            $this->user = (object)$user;
        } elseif ($user instanceof \stdClass) {
            $this->user = $user;
        } else {
            $this->user = null;
        }
        $this->checked = true;
    }

    public function clearUser(): void
    {
        $this->user = null;
        $this->checked = false;
    }

    /**
     * Parseia e retorna os dados do usuário logado como objeto stdClass.
     * Retorna null se não houver sessão válida.
     */
    private function getLoggedUser(): ?\stdClass
    {
        if ($this->user !== null && !empty($this->user->id)) {
            return $this->user;
        }

        if ($this->checked) {
            return $this->user;
        }

        // 1. Verifica dados na sessão nativa do PHP se já iniciada
        if (!empty($_SESSION['logged_user'])) {
            $val = $_SESSION['logged_user'];
            $user = is_string($val) ? json_decode($val) : (is_array($val) ? (object)$val : ($val instanceof \stdClass ? $val : null));
            if ($user instanceof \stdClass && !empty($user->id)) {
                $this->user = $user;
                $this->checked = true;
                return $this->user;
            }
        }

        if (!empty($_SESSION['customer_id'])) {
            $user = new \stdClass();
            $user->id = (int)$_SESSION['customer_id'];
            $user->customer_group_id = (int)($_SESSION['customer_group_id'] ?? 1);
            $user->name = trim(($_SESSION['customer_firstname'] ?? '') . ' ' . ($_SESSION['customer_lastname'] ?? ''));
            $user->email = (string)($_SESSION['customer_email'] ?? '');
            $user->telephone = (string)($_SESSION['customer_telephone'] ?? '');
            $this->user = $user;
            $this->checked = true;
            return $this->user;
        }

        // 2. Se não estiver em $_SESSION, verifica via Redis usando o cookie session_id
        $sessionId = $_COOKIE['session_id'] ?? '';
        if (!empty($sessionId)) {
            $redisHost = $_ENV['REDIS_HOST'] ?? '';
            $redisEnabled = filter_var($_ENV['REDIS_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);

            if ($redisEnabled && !empty($redisHost)) {
                try {
                    $redis = new \Predis\Client([
                        'host' => $redisHost,
                        'port' => $_ENV['REDIS_PORT'] ?? 6379,
                        'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                        'timeout' => 0.5
                    ]);
                    $redis->connect();
                    $sessionData = $redis->get("sessao:" . $sessionId);
                    if ($sessionData) {
                        $parsed = json_decode((string)$sessionData);
                        if ($parsed instanceof \stdClass && !empty($parsed->id)) {
                            $this->user = $parsed;
                            $this->checked = true;

                            // Sincroniza $_SESSION para compatibilidade máxima
                            if (session_status() === PHP_SESSION_ACTIVE || (session_status() === PHP_SESSION_NONE && !headers_sent())) {
                                if (session_status() === PHP_SESSION_NONE) {
                                    session_name('session_id');
                                    session_id($sessionId);
                                    @session_start();
                                }
                                $_SESSION['logged_user'] = $sessionData;
                                $_SESSION['customer_id'] = $parsed->id;
                                $_SESSION['customer_group_id'] = $parsed->customer_group_id ?? 1;
                                $_SESSION['customer_firstname'] = explode(' ', trim($parsed->name ?? ''))[0] ?? '';
                                $_SESSION['customer_lastname'] = explode(' ', trim($parsed->name ?? ''), 2)[1] ?? '';
                                $_SESSION['customer_email'] = $parsed->email ?? '';
                                $_SESSION['customer_telephone'] = $parsed->telephone ?? '';
                            }

                            return $this->user;
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback para sessão local PHP
                }
            }

            // Fallback: tenta iniciar sessão PHP local se não iniciada
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_name('session_id');
                session_id($sessionId);
                @session_start();
            }

            if (!empty($_SESSION['logged_user'])) {
                $val = $_SESSION['logged_user'];
                $user = is_string($val) ? json_decode($val) : (is_array($val) ? (object)$val : ($val instanceof \stdClass ? $val : null));
                if ($user instanceof \stdClass && !empty($user->id)) {
                    $this->user = $user;
                    $this->checked = true;
                    return $this->user;
                }
            }

            if (!empty($_SESSION['customer_id'])) {
                $user = new \stdClass();
                $user->id = (int)$_SESSION['customer_id'];
                $user->customer_group_id = (int)($_SESSION['customer_group_id'] ?? 1);
                $user->name = trim(($_SESSION['customer_firstname'] ?? '') . ' ' . ($_SESSION['customer_lastname'] ?? ''));
                $user->email = (string)($_SESSION['customer_email'] ?? '');
                $user->telephone = (string)($_SESSION['customer_telephone'] ?? '');
                $this->user = $user;
                $this->checked = true;
                return $this->user;
            }
        }

        $this->checked = true;
        return null;
    }

    public function isLogged(): bool
    {
        return $this->getLoggedUser() !== null;
    }

    public function getId(): int
    {
        // Compatibilidade com chave flat legacy
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0) {
            return (int)$_SESSION['customer_id'];
        }
        $user = $this->getLoggedUser();
        return $user ? (int)($user->id ?? 0) : 0;
    }

    public function getGroupId(): int
    {
        $user = $this->getLoggedUser();
        return (int)($user->customer_group_id ?? $_SESSION['customer_group_id'] ?? 1);
    }

    public function getCustomerGroupId(): int
    {
        return $this->getGroupId();
    }

    public function getFirstName(): string
    {
        // Chave flat legacy
        if (!empty($_SESSION['customer_firstname'])) {
            return (string)$_SESSION['customer_firstname'];
        }
        $user = $this->getLoggedUser();
        if ($user) {
            $name = (string)($user->name ?? '');
            // "name" é o nome completo; retorna a primeira palavra como firstname
            return explode(' ', trim($name))[0] ?? '';
        }
        return '';
    }

    public function getLastName(): string
    {
        // Chave flat legacy
        if (!empty($_SESSION['customer_lastname'])) {
            return (string)$_SESSION['customer_lastname'];
        }
        $user = $this->getLoggedUser();
        if ($user) {
            $name = trim((string)($user->name ?? ''));
            $parts = explode(' ', $name, 2);
            return $parts[1] ?? '';
        }
        return '';
    }

    public function getEmail(): string
    {
        if (!empty($_SESSION['customer_email'])) {
            return (string)$_SESSION['customer_email'];
        }
        $user = $this->getLoggedUser();
        return $user ? (string)($user->email ?? '') : '';
    }

    public function getTelephone(): string
    {
        if (!empty($_SESSION['customer_telephone'])) {
            return (string)$_SESSION['customer_telephone'];
        }
        $user = $this->getLoggedUser();
        return $user ? (string)($user->telephone ?? '') : '';
    }

    public function getAddressId(): int
    {
        if (!$this->isLogged()) {
            return 0;
        }
        $customerId = $this->getId();
        if ($customerId <= 0) {
            return 0;
        }
        try {
            /** @var \Alpha\Model\Domain\Repositories\CustomerRepository $customerRepo */
            $customerRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\CustomerRepository::class);
            $customer = $customerRepo->find($customerId);
            return $customer ? (int)$customer->getAddressId() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
