<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade AddressFormat - Define o layout de exibição de endereços por país.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Flexibilidade de Localização: Permite formatos de endereços específicos (Ex: Brasil vs EUA).
 * - PHP 8.4 Readiness: Tipagem nativa para strings de template.
 */
class AddressFormat extends BaseEntity
{
    private string $name = '';
    private string $addressFormat = '';

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getAddressFormat(): string
    {
        return $this->addressFormat;
    }

    public function setAddressFormat(string $format): self { $this->addressFormat = $format; return $this; }
}