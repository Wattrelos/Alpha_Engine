<?php

namespace Alpha\Support;

class Session
{
    public array $data = [];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        if (!isset($_SESSION['currency'])) {
            $_SESSION['currency'] = 'BRL';
        }
        $this->data = &$_SESSION;
    }

    /**
     * Retorna o ID da sessão ativa.
     * 
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }
}

