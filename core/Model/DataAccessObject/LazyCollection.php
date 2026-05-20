<?php
namespace Alpha\Model\DataAccessObject;

/**
 * LazyCollection - Implementação de carregamento sob demanda para a Alpha Engine.
 * 
 * Esta classe encapsula uma Closure que contém a lógica de busca do DataAccessObject.
 * Os dados só são hidratados se um método de acesso (count, foreach, offsetGet) for chamado.
 */
class LazyCollection implements \ArrayAccess, \IteratorAggregate, \Countable {
    private ?array $items = null;
    private \Closure $loader;

    /**
     * @param \Closure $loader Função que retorna o array de entidades hidratadas.
     */
    public function __construct(\Closure $loader) {
        $this->loader = $loader;
    }

    /**
     * Executa a carga dos dados se ainda não tiver sido feita.
     */
    private function initialize(): void {
        if ($this->items === null) {
            $this->items = ($this->loader)();
        }
    }

    public function getIterator(): \Traversable {
        $this->initialize();
        return new \ArrayIterator($this->items);
    }

    public function count(): int {
        $this->initialize();
        return count($this->items);
    }

    public function offsetExists(mixed $offset): bool {
        $this->initialize();
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        $this->initialize();
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        $this->initialize();
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void {
        $this->initialize();
        unset($this->items[$offset]);
    }

    /**
     * Retorna os dados puros para exportação (ex: serialização JSON).
     */
    public function toArray(): array {
        $this->initialize();
        return $this->items;
    }
}