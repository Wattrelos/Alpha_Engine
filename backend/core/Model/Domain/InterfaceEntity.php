<?php

namespace Alpha\Model\Domain;

/**
 * InterfaceEntity - Contrato fundamental para todas as entidades de domínio da Alpha Engine.
 * 
 * Define a obrigatoriedade da Surrogate Key e métodos de acesso padrão para persistência e mapeamento.
 */
interface InterfaceEntity extends \JsonSerializable
{
    public function getId(): int;
    public function setId(int $id): self;
}