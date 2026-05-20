<?php

namespace Alpha\Support;

/**
 * LazyCollection - Alpha Engine
 * 
 * Implementa o carregamento tardio (Lazy Loading) de coleções utilizando Closures.
 * Permite adiar a execução de queries pesadas até o momento em que os dados 
 * são efetivamente necessários.
 */
class LazyCollection implements \IteratorAggregate, \Countable, \ArrayAccess, \JsonSerializable
{
    /**
     * @var callable A função que carregará os dados.
     */
    private $loader;

    /**
     * @var array|null O cache interno dos dados carregados.
     */
    private ?array $collection = null;

    /**
     * @param callable $loader Função que deve retornar um array ou um Iterable.
     */
    public function __construct(callable $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Dispara o carregamento dos dados caso ainda não tenham sido processados.
     * 
     * @return array
     */
    private function load(): array
    {
        if ($this->collection === null) {
            $result = ($this->loader)();

            if ($result instanceof \Traversable) {
                $this->collection = iterator_to_array($result);
            } else {
                $this->collection = (array)$result;
            }
        }

        return $this->collection;
    }

    /**
     * Implementação de IteratorAggregate para suporte a loops foreach.
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->load());
    }

    /**
     * Implementação de Countable para suporte à função count().
     */
    public function count(): int
    {
        return count($this->load());
    }

    /**
     * Implementação de JsonSerializable para conversão automática em JSON.
     */
    public function jsonSerialize(): mixed
    {
        return $this->load();
    }

    /**
     * Métodos da interface ArrayAccess para manipulação como array.
     */

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->load()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->load()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($this->collection === null) {
            $this->load();
        }
        
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if ($this->collection === null) {
            $this->load();
        }
        
        unset($this->collection[$offset]);
    }
}