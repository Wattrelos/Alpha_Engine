<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Session Entity - Alpha Engine
 * 
 * Representa o estado persistente de uma sessão no sistema.
 * Utiliza Surrogate Keys (id) e isola o identificador de transporte (tokenSession).
 */
class Session extends BaseEntity
{
    // Observação: id agora é uma Surrogate Key de session.
    private string $tokenSession = '';
    private string $data = '';
    private string $expire = '';

    public function getTokenSession(): string
    {
        return $this->tokenSession;
    }

    public function setTokenSession(string $tokenSession): self
    {
        $this->tokenSession = $tokenSession;
        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getExpire(): string
    {
        return $this->expire;
    }

    public function setExpire(string $expire): self
    {
        $this->expire = $expire;
        return $this;
    }
}
