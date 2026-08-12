<?php

namespace Alpha\Model\Domain;

/**
 * BaseEntity - Implementação base para as entidades de domínio.
 * 
 * Centraliza a gestão do identificador único (Surrogate Key) e fornece
 * uma base comum para o ciclo de vida dos objetos de negócio na Alpha Engine.
 */
abstract class BaseEntity implements InterfaceEntity, \JsonSerializable, \ArrayAccess
{
    protected int $id = 0;

    public function __construct()
    {
        // Alpha Engine: Gancho para extensões futuras como audit trail ou data hydration
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Implementação de JsonSerializable.
     * Permite que json_encode() converta a entidade automaticamente.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Converte as propriedades da entidade em um array associativo (snake_case).
     * Facilita a integração com a camada de visualização e o CollectionToArrayConverter.
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $data = ['id' => $this->id];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $name = $property->getName();

            if (!$property->isInitialized($this) || $name === 'id') {
                continue;
            }

            $key = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $name));
            $data[$key] = $property->getValue($this);
        }

        return $data;
    }

    /**
     * Utilitário para converter chaves legadas (snake_case) em propriedades Alpha (camelCase).
     */
    protected function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    /**
     * \ArrayAccess: Verifica se o atributo existe quando testado com isset().
     */
    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $this->snakeToCamel((string)$offset));
    }

    /**
     * \ArrayAccess: Emula a leitura como array associativo (ex: $entity['first_name']).
     */
    public function offsetGet(mixed $offset): mixed
    {
        $property = $this->snakeToCamel((string)$offset);
        $getter = 'get' . ucfirst($property);

        // Tenta usar o encapsulamento de domínio (Getter) primeiro
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        // Fallback para leitura direta da propriedade via Reflection (seguro para protected/private)
        if (property_exists($this, $property)) {
            $reflection = new \ReflectionProperty($this, $property);
            if ($reflection->isInitialized($this)) {
                return $reflection->getValue($this);
            }
        }

        return null;
    }

    /**
     * \ArrayAccess: Protege a entidade contra modificação espaguete legada.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Intencionalmente vazio. Na Alpha Engine, mutações devem usar setters explícitos.
    }

    public function offsetUnset(mixed $offset): void
    {
        // Intencionalmente vazio. Estruturas não devem ser destruídas em runtime via array.
    }

    /**
     * Métodos Mágicos: Intercepta a leitura via sintaxe de objeto (ex: $entity->first_name).
     * Delega inteligentemente para a mesma lógica robusta do ArrayAccess.
     */
    public function __get(string $name): mixed
    {
        return $this->offsetGet($name);
    }

    /**
     * Métodos Mágicos: Intercepta testes com isset() em sintaxe de objeto.
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Métodos Mágicos: Protege contra criação de propriedades dinâmicas,
     * evitando os avisos de Deprecation do PHP 8.2+.
     */
    public function __set(string $name, mixed $value): void
    {
        // Segue a mesma filosofia de imutabilidade externa do offsetSet.
        // Mutações devem ocorrer estritamente pelos Setters do domínio.
    }

    public function __unset(string $name): void
    {
        // Protege contra a destruição de propriedades.
    }
}