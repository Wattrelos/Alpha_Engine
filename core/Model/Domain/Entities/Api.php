<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Api - Gerencia as credenciais para acesso externo ao sistema.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Segurança de Integração: Campos 'username' e 'key' tipados como string para validação exata em middlewares de API.
 * - Controle de Acesso: Status booleano estrito para revogação imediata de chaves de integração.
 * - Rastreabilidade: Datas de criação e modificação tipadas para auditoria de segurança.
 * - PHP 8.4 Readiness: Uso de tipos nativos e interface fluida para facilitar a gestão via scripts de DevOps ou painel.
 */
class Api extends BaseEntity
{
    private string $username = '';
    private string $key = '';
    private bool $status = true;
    private string $dateAdded = '';
    private string $dateModified = '';

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getStatus(): bool
    {
        return $this;
    }

    public function setStatus(bool|int $status): self
    {
        $this->status = (bool)$status;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getDateModified(): string
    {
        return $this->dateModified;
    }

    public function setDateModified(string $dateModified): self
    {
        $this->dateModified = $dateModified;
        return $this;
    }
}