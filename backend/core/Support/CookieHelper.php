<?php

declare(strict_types=1);

namespace Alpha\Support;

use Psr\Http\Message\ServerRequestInterface;

/**
 * CookieHelper - Utilitário para construção de cabeçalhos Set-Cookie seguros na Alpha Engine.
 * 
 * Centraliza e padroniza a formatação de cookies HTTP incluindo as diretivas de segurança
 * HttpOnly, SameSite e anexo condicional da flag Secure sob conexão HTTPS.
 */
class CookieHelper
{
    /**
     * Verifica se a requisição atual está sendo realizada sob conexão segura HTTPS.
     */
    public static function isHttps(ServerRequestInterface $request): bool
    {
        $scheme = $request->getUri()->getScheme();
        $serverParams = $request->getServerParams();

        $httpsServer = strtolower((string)($serverParams['HTTPS'] ?? $_SERVER['HTTPS'] ?? ''));
        $forwardedProto = strtolower((string)($serverParams['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));

        return ($scheme === 'https') || ($httpsServer === 'on') || ($httpsServer === '1') || ($forwardedProto === 'https');
    }

    /**
     * Constrói a string formatada para o cabeçalho HTTP Set-Cookie.
     * 
     * @param ServerRequestInterface $request Requisição PSR-7 atual
     * @param string $name Nome do Cookie
     * @param string $value Valor do Cookie
     * @param int $maxAge Tempo de vida em segundos (7200 = 2h; <= 0 = expirar imediatamente)
     * @param string $path Caminho do Cookie (padrão: '/')
     * @param string $sameSite Política SameSite (Lax, Strict, None)
     * @param bool $httpOnly Restringe acesso via JavaScript (padrão: true)
     * @return string String pronta para o cabeçalho Set-Cookie
     */
    public static function makeCookieHeader(
        ServerRequestInterface $request,
        string $name,
        string $value,
        int $maxAge = 7200,
        string $path = '/',
        string $sameSite = 'Lax',
        bool $httpOnly = true
    ): string {
        $cookie = sprintf('%s=%s; Path=%s', $name, $value, $path);

        if ($maxAge > 0) {
            $cookie .= sprintf('; Max-Age=%d', $maxAge);
        } else {
            $cookie .= '; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0';
        }

        if ($httpOnly) {
            $cookie .= '; HttpOnly';
        }

        if (!empty($sameSite)) {
            $cookie .= sprintf('; SameSite=%s', ucfirst($sameSite));
        }

        if (self::isHttps($request)) {
            $cookie .= '; Secure';
        }

        return $cookie;
    }
}
