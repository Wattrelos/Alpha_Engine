<?php

namespace Alpha\Support;

class Language
{
    private string $code;
    private array $data = [];

    public function __construct(string $code = 'pt-br')
    {
        $this->code = $code;
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

    public function load(string $route): array
    {
        $file = '/var/www/html/agsonhos/core/language/' . $this->code . '/' . $route . '.php';
        if (is_file($file)) {
            $_ = [];
            include $file;
            $this->data = array_merge($this->data, $_);
            return $_;
        }
        return [];
    }

    public function get(string $key): string
    {
        return $this->data[$key] ?? $key;
    }

    public function set(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }
}
