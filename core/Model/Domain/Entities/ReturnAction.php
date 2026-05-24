<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ReturnAction - Define ações (ex: Reembolso, Troca).
 * O OpenCart não utiliza tabela _description para esta entidade,
 * portanto o language_id reside diretamente aqui.
 */
class ReturnAction extends BaseEntity
{
    private int $returnActionId = 0;
    private int $languageId = 0;
    private string $name = '';

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getReturnActionId(): int { return $this->returnActionId; }
    public function setReturnActionId(int $val): self { $this->returnActionId = $val; return $this; }

    public function getLanguageId(): int { return $this->languageId; }
    public function setLanguageId(int $val): self { $this->languageId = $val; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $val): self { $this->name = $val; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $val): self { 
        $this->language = $val; return $this; 
    }
}