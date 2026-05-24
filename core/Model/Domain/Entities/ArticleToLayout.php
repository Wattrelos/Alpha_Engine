<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

class ArticleToLayout extends BaseEntity
{
    #[ManyToOne(targetEntity: Article::class)]
    private ?Article $article = null;

    #[ManyToOne(targetEntity: Store::class)]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Layout::class)]
    private ?Layout $layout = null;

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
}