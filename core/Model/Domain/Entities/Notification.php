<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade Notification - Gerencia avisos e comunicações internas para clientes.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Engajamento Direto: Permite enviar textos personalizados.
 * - Tipagem PHP 8.4: Uso de bool para status de leitura e int para IDs.
 */
class Notification extends BaseEntity
{
    private string $title = '';
    private string $text = '';
    private bool $status = false;
    private string $dateAdded = '';

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getText(): string { return $this->text; }
    public function setText(string $text): self { $this->text = $text; return $this; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $date): self { $this->dateAdded = $date; return $this; }
}