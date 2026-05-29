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
        return (int)($_SESSION['customer_id'] ?? 0);
    }
}
