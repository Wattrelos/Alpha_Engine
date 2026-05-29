<?php

namespace Alpha\Support;

class Url
{
    public function link(string $route, string $args = ''): string
    {
        if ($route === 'product/search') {
            parse_str($args, $params);
            $tag = $params['tag'] ?? '';
            return '/pt-br/busca' . ($tag ? '?busca=' . urlencode($tag) : '');
        }
        if ($route === 'account/login') {
            return '/pt-br/login';
        }
        if ($route === 'account/register') {
            return '/pt-br/cadastro';
        }
        return '/index.php?route=' . $route . ($args ? '&' . $args : '');
    }
}
