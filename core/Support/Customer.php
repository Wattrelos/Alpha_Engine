<?php

namespace Alpha\Support;

class Customer
{
    public function isLogged(): bool
    {
        return !empty($_SESSION['logged_user']);
    }

    public function getGroupId(): int
    {
        return (int)($_SESSION['customer_group_id'] ?? 1);
    }

    public function getId(): int
    {
        if (isset($_SESSION['customer_id'])) {
            return (int)$_SESSION['customer_id'];
        }
        if (!empty($_SESSION['logged_user'])) {
            $user = json_decode($_SESSION['logged_user']);
            if ($user && isset($user->id)) {
                return (int)$user->id;
            }
        }
        return 0;
    }
}
