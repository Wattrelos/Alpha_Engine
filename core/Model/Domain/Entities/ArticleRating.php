<?php

namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;
use Alpha\Model\Domain\Entities\Customer\Customer;

class ArticleRating extends BaseEntity
{
    #[ManyToOne(targetEntity: ArticleComment::class)]
    private ?ArticleComment $articleComment = null;

    #[ManyToOne(targetEntity: Article::class)]
    private ?Article $article = null;

    #[ManyToOne(targetEntity: Store::class)]
    private ?Store $store = null;

    #[ManyToOne(targetEntity: Customer::class)]
    private ?Customer $customer = null;

    private int $rating = 0;
    private string $ip = '';
    private string $dateAdded = '';

    public function getArticleComment(): ?ArticleComment
    {
        return $this->articleComment;
    }

    public function setArticleComment(?ArticleComment $articleComment): self
    {
        $this->articleComment = $articleComment;
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

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
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

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;
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
}