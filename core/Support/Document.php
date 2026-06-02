<?php

namespace Alpha\Support;

class Document
{
    private string $title = '';
    private string $description = '';
    private string $keywords = '';

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }
}
