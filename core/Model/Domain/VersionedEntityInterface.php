<?php

namespace Alpha\Model\Domain;

/**
 * Interface VersionedEntityInterface
 * 
 * Define o contrato para entidades de domínio que utilizam controle de concorrência otimista (Optimistic Locking).
 */
interface VersionedEntityInterface
{
    public function getVersion(): int;

    public function setVersion(int $version): self;
}
