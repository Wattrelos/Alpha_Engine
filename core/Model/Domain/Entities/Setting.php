<?php

namespace Alpha\Model\Domain\Entities;

/**
 * Entidade Setting - Representa uma configuração individual do sistema.
 * 
 * Alpha Engine:
 * - Tipagem estrita PHP 8.4.
 * - Gerenciamento de estado de serialização.
 */
class Setting extends BaseEntity
{
    protected int $storeId = 0;
    protected string $code = '';
    protected string $key = '';
    protected mixed $value = '';
    protected bool $serialized = false;

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function isSerialized(): bool
    {
        return $this->serialized;
    }

    public function setSerialized(bool $serialized): self
    {
        $this->serialized = $serialized;
        return $this;
    }

    /**
     * Apontamento Técnico:
     * No OpenCart 4, configurações complexas são armazenadas como JSON.
     * O Mapper deve garantir que, se 'serialized' for true, o valor seja
     * tratado corretamente antes de chegar à aplicação.
     */
}