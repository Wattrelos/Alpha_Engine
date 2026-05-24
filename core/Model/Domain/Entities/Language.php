<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Language - Gerencia os idiomas disponíveis no sistema.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Integridade de Código: 'code' e 'locale' tipados para garantir carregamento correto de arquivos .php.
 * - Controle de Extensão: Suporte nativo para mapear pacotes de tradução externos.
 * - Interface Fluida: Setters preparados para encadeamento.
 */
class Language extends BaseEntity
{
    private string $name = '';
    private string $code = '';
    private string $locale = '';
    private string $extension = '';
    private int $sortOrder = 0;
    private bool $status = true;

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getLocale(): string { return $this->locale; }
    public function setLocale(string $locale): self { $this->locale = $locale; return $this; }

    public function getExtension(): string { return $this->extension; }
    public function setExtension(string $extension): self { $this->extension = $extension; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): self { $this->sortOrder = $sortOrder; return $this; }

    public function getStatus(): bool { return $this->status; }
    public function setStatus(bool|int $status): self { $this->status = (bool)$status; return $this; }
}