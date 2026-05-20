<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use DateTimeImmutable;

/**
 * Entidade CustomerLogin
 * Monitora tentativas de login para prevenção de ataques de força bruta.
 * 
 * @Table(name="customer_login")
 */
class CustomerLogin extends BaseEntity
{
    private string $email = '';
    private string $ip = '';
    private int $total = 0;
    private ?DateTimeImmutable $dateAdded = null;
    private ?DateTimeImmutable $dateModified = null;

    /**
     * Apontamentos Técnicos:
     * 1. Segurança: O campo 'total' armazena o contador de falhas por IP/Email.
     * 2. Lifecycle: dateModified é atualizado a cada nova tentativa para controle de bloqueio temporal.
     */

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function getDateAdded(): ?DateTimeImmutable
    {
        return $this->dateAdded;
    }

    public function setDateAdded(?DateTimeImmutable $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getDateModified(): ?DateTimeImmutable
    {
        return $this->dateModified;
    }

    public function setDateModified(?DateTimeImmutable $dateModified): self
    {
        $this->dateModified = $dateModified;
        return $this;
    }

    /**
     * Incrementa o contador de tentativas
     */
    public function increment(): self
    {
        $this->total++;
        $this->dateModified = new DateTimeImmutable();
        return $this;
    }

    /**
     * Reseta as tentativas após sucesso
     */
    public function reset(): self
    {
        $this->total = 0;
        return $this;
    }
}