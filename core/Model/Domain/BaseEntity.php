<?php

namespace Alpha\Model\Domain;

/**
 * BaseEntity - Implementação base para as entidades de domínio.
 * 
 * Centraliza a gestão do identificador único (Surrogate Key) e fornece
 * uma base comum para o ciclo de vida dos objetos de negócio na Alpha Engine.
 */
abstract class BaseEntity implements InterfaceEntity, \JsonSerializable
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
}