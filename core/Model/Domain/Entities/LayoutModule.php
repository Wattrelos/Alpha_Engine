<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade LayoutModule - Gerencia a posição e ordem dos módulos em um layout.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Flexibilidade de Design: Propriedades 'code' (identificador do módulo) e 'position' (column_left, content_top, etc) tipadas.
 * - Ordenação Garantida: sortOrder tipado como int para respeitar a hierarquia visual.
 * - Injeção Relacional: Atributo #[ManyToOne] para vinculação automática com o Layout pai.
 * - Interface Fluida: Facilita a reorganização dinâmica de componentes via código ou painel administrativo.
 */
class LayoutModule extends BaseEntity
{
    private int $layoutId = 0;
    private string $code = '';
    private string $position = '';
    private int $sortOrder = 0;

    #[ManyToOne(targetEntity: Layout::class, foreignKey: 'layoutId')]
    private ?Layout $layout = null;

    public function getLayoutId(): int
    {
        return $this->layoutId;
    }

    public function setLayoutId(int $layoutId): self
    {
        $this->layoutId = $layoutId;
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

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * Retorna o objeto Layout pai.
     */
    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    /**
     * Injeta o objeto Layout pai.
     */
    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
}