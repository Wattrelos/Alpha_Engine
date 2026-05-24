<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade UserGroup
 * Define perfis e permissões de acesso ao painel de administração.
 * 
 * @Table(name="user_group")
 */
class UserGroup extends BaseEntity
{
    private string $name = '';
    private string $permission = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    public function getPermissionArray(): array { return json_decode($this->permission, true) ?: []; }
}