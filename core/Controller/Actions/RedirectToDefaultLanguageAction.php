<?php

namespace Alpha\Controller\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RedirectToDefaultLanguageAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $query = $uri->getQuery();

        // Monta o novo caminho com o idioma padrão
        $targetUrl = '/pt-br' . ($path === '/' ? '' : $path);

        if (!empty($query)) {
            $targetUrl .= '?' . $query;
        }

        // Mantém o status 307 para requisições POST não perderem os dados
        $status = ($request->getMethod() === 'POST') ? 307 : 302;

        return $response->withHeader('Location', $targetUrl)->withStatus($status);
    }
}
