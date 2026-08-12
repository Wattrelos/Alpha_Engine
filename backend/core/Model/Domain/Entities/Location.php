<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Location - Define endereços físicos de lojas ou pontos de retirada.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Gestão Multicanal: Permite exibir geolocalização e horários de funcionamento.
 * - PHP 8.4 Readiness: Inicialização de strings e tipagem estrita para contatos.
 */
class Location extends BaseEntity
{
    private string $name = '';
    private string $address = '';
    private string $telephone = '';
    private string $geocode = '';
    private string $image = '';
    private string $open = '';
    private string $comment = '';

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getAddress(): string { return $this->address; }
    public function setAddress(string $address): self { $this->address = $address; return $this; }

    public function getTelephone(): string { return $this->telephone; }
    public function setTelephone(string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getGeocode(): string { return $this->geocode; }
    public function setGeocode(string $geocode): self { $this->geocode = $geocode; return $this; }

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): self { $this->image = $image; return $this; }

    public function getOpen(): string
    {
        return $this->open;
    }

    public function setOpen(string $open): self { $this->open = $open; return $this; }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $comment): self { $this->comment = $comment; return $this; }
}