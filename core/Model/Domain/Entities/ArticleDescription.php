<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade ArticleDescription - Traduções e metadados SEO dos artigos.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização SEO: Campos de meta title, description e keyword isolados por idioma para SEO internacional.
 * - Conteúdo Rico: Propriedades title e description tipadas como string para armazenar o conteúdo do artigo.
 * - Injeção de Contexto: Propriedade articleId mapeada para permitir que o DAO vincule automaticamente a tradução ao seu artigo pai.
 * - Sanitização: Propriedades string inicializadas para evitar retornos nulos em templates legados.
 */
class ArticleDescription extends BaseEntity
{
    private string $title = '';
    private string $description = '';
    private string $metaTitle = '';
    private string $metaDescription = '';
    private string $metaKeyword = '';

    #[ManyToOne(targetEntity: Article::class, foreignKey: 'articleId')]
    private ?Article $article = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getArticleId(): int
    {
        return $this->article ? (int)$this->article->getId() : 0;
    }

    public function setArticleId(int $articleId): self
    {
        if (!$this->article) {
            $this->article = new Article();
        }
        $this->article->setId($articleId);
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->language ? (int)$this->language->getId() : 0;
    }

    public function setLanguageId(int $languageId): self
    {
        if (!$this->language) {
            $this->language = new Language();
        }
        $this->language->setId($languageId);
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    public function getMetaKeyword(): string
    {
        return $this->metaKeyword;
    }

    public function setMetaKeyword(string $metaKeyword): self
    {
        $this->metaKeyword = $metaKeyword;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;
        return $this;
    }
}