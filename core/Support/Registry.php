<?php

namespace Alpha\Support;

/**
 * Class Registry - O container de serviços leve e nativo da Alpha Engine.
 * 
 * Substitui a dependência da classe herdada Opencart\System\Engine\Registry
 * para manter o desacoplamento completo da Alpha Engine standalone.
 */
class Registry extends \Opencart\System\Engine\Registry
{
    /**
     * @var array<string, object>
     */
    private array $data = [];

    /**
     * Retorna o serviço mapeado pela chave.
     * 
     * @param string $key
     * @return object|null
     */
    public function get(string $key): ?object
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Associa um serviço a uma chave.
     * 
     * @param string $key
     * @param object $value
     * @return void
     */
    public function set(string $key, object $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Verifica se um serviço está associado a uma chave.
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a associação de um serviço.
     * 
     * @param string $key
     * @return void
     */
    public function unset(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Sobrecarga mágica __get.
     */
    public function __get(string $key): ?object
    {
        return $this->get($key);
    }

    /**
     * Sobrecarga mágica __set.
     */
    public function __set(string $key, object $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Sobrecarga mágica __isset.
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }
}
