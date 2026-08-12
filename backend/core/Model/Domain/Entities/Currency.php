<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Currency - Gerencia as moedas e taxas de conversão do sistema.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Precisão Monetária: Campo 'value' tipado como float para garantir que a conversão de câmbio seja precisa.
 * - Flexibilidade de UI: Suporte nativo para símbolos à esquerda ou direita, essencial para internacionalização.
 * - Saneamento de Dados: Propriedades string inicializadas para evitar erros de concatenação em motores de template.
 * - PHP 8.4 Readiness: Uso de tipos nativos e visibilidade privada com getters/setters fluídos.
 */
class Currency extends BaseEntity
{
    private string $title = '';
    private string $code = '';
    private string $symbolLeft = '';
    private string $symbolRight = '';
    private int $decimalPlace = 2;
    private float $value = 1.00000000;
    private bool $status = true;
    private string $dateModified = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getSymbolLeft(): string
    {
        return $this->symbolLeft;
    }

    public function setSymbolLeft(string $symbolLeft): self
    {
        $this->symbolLeft = $symbolLeft;
        return $this;
    }

    public function getSymbolRight(): string
    {
        return $this->symbolRight;
    }

    public function setSymbolRight(string $symbolRight): self
    {
        $this->symbolRight = $symbolRight;
        return $this;
    }

    public function getDecimalPlace(): int
    {
        return $this->decimalPlace;
    }

    public function setDecimalPlace(int $decimalPlace): self
    {
        $this->decimalPlace = $decimalPlace;
        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool|int $status): self
    {
        $this->status = (bool)$status;
        return $this;
    }

    public function getDateModified(): string
    {
        return $this->dateModified;
    }

    public function setDateModified(string $dateModified): self
    {
        $this->dateModified = $dateModified;
        return $this;
    }
}