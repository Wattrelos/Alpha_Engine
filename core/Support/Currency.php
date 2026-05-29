<?php

namespace Alpha\Support;

class Currency
{
    private Language $language;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function format(float $number, string $currency = 'BRL', float $value = 1.0, bool $format = true): string|float
    {
        if (!$format) {
            return $number;
        }
        return 'R$ ' . number_format($number, 2, ',', '.');
    }
}
