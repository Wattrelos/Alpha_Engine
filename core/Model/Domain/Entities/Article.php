<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\OneToMany;

/**
 * Entidade Article - Representa uma postagem de blog ou artigo do CMS.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Gestão de Conteúdo: Centraliza os dados principais do artigo, como imagem, status e ordem de exibição.
 * - Tipagem PHP 8.4: Propriedades como status (bool) e sortOrder (int) garantem integridade e consistência.
 * - Relacionamentos Ativos: #[OneToMany] configurado para hidratação automática das descrições do artigo (títulos, conteúdo, meta tags).
 * - Auditoria: Campos de data (dateAdded, dateModified) tipados como string para compatibilidade com o formato de banco de dados.
 */
class Article extends BaseEntity
{
    private int $topicId = 0;
    private string $author = '';
    private int $rating = 0;
    private bool $status = true;
    private string $dateAdded = '';
    private string $dateModified = '';

    /**
     * @var ArticleDescription[]
     */
    #[OneToMany(targetEntity: ArticleDescription::class, mappedBy: "article", foreignKey: "articleId")]
    private array $descriptions = [];

    public function getTopicId(): int
    {
        return $this->topicId;
    }

    public function setTopicId(int $topicId): self
    {
        $this->topicId = $topicId;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
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

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
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

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;
        return $this;
    }
}