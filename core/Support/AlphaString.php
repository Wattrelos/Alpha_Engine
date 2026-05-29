<?php

declare(strict_types=1);

namespace Alpha\Support;

/**
 * AlphaString - Utilitários modernos e standalone para manipulação e validação de strings.
 * 
 * Substitui os helpers procedurais legados do OpenCart.
 */
class AlphaString
{
    /**
     * Valida se o comprimento da string (removendo espaços) está entre o mínimo e o máximo (inclusive).
     */
    public static function validateLength(string $string, int $minimum, int $maximum): bool
    {
        $len = mb_strlen(trim($string), 'UTF-8');
        return $len >= $minimum && $len <= $maximum;
    }

    /**
     * Valida se um endereço de e-mail é válido.
     */
    public static function validateEmail(string $email): bool
    {
        if (mb_strlen($email, 'UTF-8') > 96) {
            return false;
        }

        $atPos = mb_strrpos($email, '@', 0, 'UTF-8');
        if ($atPos === false) {
            return false;
        }

        if (function_exists('idn_to_ascii')) {
            $local = mb_substr($email, 0, $atPos, 'UTF-8');
            $domain = mb_substr($email, $atPos + 1, null, 'UTF-8');
            
            // idn_to_ascii pode lançar avisos se o domínio for inválido, por isso silenciamos e verificamos o retorno
            $asciiDomain = @idn_to_ascii($domain, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
            if ($asciiDomain !== false) {
                $email = $local . '@' . $asciiDomain;
            }
        }

        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida uma string contra uma expressão regular.
     */
    public static function validateRegex(string $string, string $pattern): bool
    {
        $option = ['regexp' => html_entity_decode($pattern, ENT_QUOTES, 'UTF-8')];
        return (bool)filter_var($string, FILTER_VALIDATE_REGEXP, ['options' => $option]);
    }

    /**
     * Valida se um IP é válido.
     */
    public static function validateIp(string $ip): bool
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Valida se o nome do arquivo é seguro/válido.
     */
    public static function validateFilename(string $filename): bool
    {
        return !preg_match('/[^a-zA-Z\p{Cyrillic}0-9\.\-\_]+/u', $filename);
    }

    /**
     * Valida se uma URL é válida.
     */
    public static function validateUrl(string $url): bool
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}
