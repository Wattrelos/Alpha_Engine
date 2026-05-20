<?php

namespace Alpha\Support;

/**
 * Collection - Utilitário para manipulação de conjuntos de dados.
 * 
 * Fornece métodos fluídos para acessar dados de domínio sem a verbosidade
 * de verificações 'isset' manuais em arrays.
 */
class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    public function __construct(protected array $items = [])
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function merge(array|Collection $data): self
    {
        $array = $data instanceof Collection ? $data->toArray() : $data;
        $this->items = array_merge($this->items, $array);
        return $this;
    }

    // Implementação de Interfaces Nativas PHP
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }
}