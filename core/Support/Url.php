<?php

namespace Alpha\Support;

/**
 * Url Helper - DEPRECATED
 * 
 * ATENÇÃO: Esta classe de geração de URLs legadas do OpenCart está depreciada.
 * NÃO utilize este formato legado (?route=...). Prefira as rotas amigáveis do Slim.
 * 
 * Para geração de URLs, utilize:
 * - No PHP: O RouteParser do Slim ($routeParser->urlFor(...))
 * - No Twig: A função `url_for(...)` ou a extensão `url(...)`
 */
class Url
{
    /**
     * @deprecated Não utilizar rotas legadas OpenCart. Utilize o roteamento do Slim ou UrlExtension.
     */
    public function link(string $route, string $args = ''): string
    {
        // Dispara um aviso informando que a rota é legada e deve ser evitada
        trigger_error('Aviso de Depreciação: Rota legada "' . $route . '" com argumentos "' . $args . '" foi acionada. Utilize as rotas amigáveis do Slim.', E_USER_DEPRECATED);

        $lang = 'pt-br';
        if ($args) {
            parse_str($args, $params);
            if (isset($params['language'])) {
                $lang = $params['language'];
            }
        }

        // Normalização do idioma
        if ($lang !== 'pt-br' && $lang !== 'en' && $lang !== 'es') {
            $lang = 'pt-br';
        }

        switch ($route) {
            case 'common/home':
                return "/{$lang}";
            case 'account/login':
                return "/{$lang}/login";
            case 'account/register':
                return "/{$lang}/cadastro";
            case 'account/logout':
                return "/{$lang}/logout";
            case 'checkout/cart':
                return "/{$lang}/carrinho";
            case 'checkout/checkout':
                return "/{$lang}/checkout";
            case 'product/search':
                $searchQuery = isset($params['search']) ? '?busca=' . urlencode($params['search']) : '';
                return "/{$lang}/busca{$searchQuery}";
            case 'information/sitemap':
                return "/{$lang}/sitemap";
            case 'information/contact':
                return "/{$lang}/contato";
            case 'account':
                return "/{$lang}/account";
            case 'account/orders':
                return "/{$lang}/account/orders";
            case 'account/return':
            case 'account/return/add':
            case 'account/return.add':
                return "/{$lang}/account/return";
        }

        // Se for uma categoria, produto ou informação (SEO / ID)
        if ($route === 'product/category') {
            if (isset($params['path'])) {
                $parts = explode('_', $params['path']);
                $id = end($parts);
                return "/{$lang}/categoria/{$id}";
            }
        }
        if ($route === 'product/product') {
            if (isset($params['product_id'])) {
                return "/{$lang}/produto/{$params['product_id']}";
            }
        }
        if ($route === 'information/information') {
            if (isset($params['information_id'])) {
                return "/{$lang}/pagina/{$params['information_id']}";
            }
        }

        // Fallback genérico convertendo para rota amigável do Slim
        return "/{$lang}/" . ltrim($route, '/');
    }
}
