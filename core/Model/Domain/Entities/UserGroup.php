<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade UserGroup
 * Define grupos de permissões para usuários administrativos.
 * 
 * @Table(name="user_group")
 */
class UserGroup extends BaseEntity
{
    private string $name = '';
    private array $permission = [];

    #[OneToMany(targetEntity: User::class, foreignKey: 'userGroupId')]
    private array $users = [];

    /**
     * Apontamentos Técnicos:
     * 1. Gestão de Permissões: O campo 'permission' é tipado como array para facilitar a manipulação
     *    de acessos (access/modify) diretamente na lógica de negócio.
     * 2. Integridade de Acesso: A coleção 'users' permite auditar rapidamente quais administradores
     *    pertencem a um determinado nível de privilégio.
     */

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPermission(): array
    {
        return $this->permission;
    }

    public function setPermission(array $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    /** @return User[] */
    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(array $users): self
    {
        $this->users = $users;
        return $this;
    }
}