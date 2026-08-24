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
    /**
     * Parseia e retorna os dados do usuário logado como objeto stdClass.
     * Retorna null se não houver sessão válida.
     */
    private function getLoggedUser(): ?\stdClass
    {
        if (!empty($_SESSION['logged_user'])) {
            $val = $_SESSION['logged_user'];
            if (is_string($val)) {
                $user = json_decode($val);
            } elseif (is_array($val)) {
                $user = (object)$val;
            } elseif ($val instanceof \stdClass) {
                $user = $val;
            } else {
                $user = null;
            }
            if ($user instanceof \stdClass && !empty($user->id)) {
                return $user;
            }
        }

        if (!empty($_SESSION['customer_id'])) {
            $user = new \stdClass();
            $user->id = (int)$_SESSION['customer_id'];
            $user->customer_group_id = (int)($_SESSION['customer_group_id'] ?? 1);
            $user->name = trim(($_SESSION['customer_firstname'] ?? '') . ' ' . ($_SESSION['customer_lastname'] ?? ''));
            $user->email = (string)($_SESSION['customer_email'] ?? '');
            $user->telephone = (string)($_SESSION['customer_telephone'] ?? '');
            return $user;
        }

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
