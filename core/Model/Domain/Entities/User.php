<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use DateTimeImmutable;

/**
 * Entidade User
 * Representa um usuário administrativo do sistema.
 * 
 * @Table(name="user")
 */
class User extends BaseEntity
{
    private string $username = '';
    private string $password = '';
    private string $salt = '';
    private string $firstname = '';
    private string $lastname = '';
    private string $email = '';
    private string $image = '';
    private string $code = '';
    private string $ip = '';
    private bool $status = false;
    private ?DateTimeImmutable $dateAdded = null;

    #[ManyToOne(targetEntity: UserGroup::class, foreignKey: 'userGroupId')]
    private ?UserGroup $userGroup = null;

    /**
     * Apontamentos Técnicos:
     * 1. Segurança de Credenciais: Password e Salt são mantidos isolados para o motor de autenticação.
     * 2. Rastreabilidade: O campo 'ip' e 'dateAdded' garantem auditoria básica de acesso.
     * 3. Vinculação de Grupo: O ManyToOne garante que o DAO carregue as permissões do usuário
     *    automaticamente via UserGroup.
     */

    public function getUserGroupId(): int
    {
        return $this->userGroup ? (int)$this->userGroup->getId() : 0;
    }

    public function setUserGroupId(int $userGroupId): self
    {
        if (!$this->userGroup) {
            $this->userGroup = new UserGroup();
        }
        $this->userGroup->setId($userGroupId);
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;
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

    public function getUserGroup(): ?UserGroup
    {
        return $this->userGroup;
    }

    public function setUserGroup(?UserGroup $userGroup): self
    {
        $this->userGroup = $userGroup;
        return $this;
    }

    // Getters e Setters para campos de perfil (firstname, lastname, image, code, ip) simplificados
    public function getFirstname(): string { return $this->firstname; }
    public function setFirstname(string $firstname): self { $this->firstname = $firstname; return $this; }
    
    public function getLastname(): string { return $this->lastname; }
    public function setLastname(string $lastname): self { $this->lastname = $lastname; return $this; }

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): self { $this->image = $image; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getIp(): string { return $this->ip; }
    public function setIp(string $ip): self { $this->ip = $ip; return $this; }

    public function getStatus(): bool
    {
        return $this->isStatus();
    }
}