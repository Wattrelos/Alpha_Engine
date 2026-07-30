<?php

declare(strict_types=1);

namespace Alpha\Support;

/**
 * LgpdSanitizer - Utilitário de Conformidade LGPD e Higienização de Logs para a Alpha Engine.
 * 
 * Sanitiza e mascara Dados Pessoais Identificáveis (PII) em logs de auditoria, depuração e exceções,
 * garantindo conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).
 */
class LgpdSanitizer
{
    /** @var string[] Lista de chaves de array que devem ser estritamente omitidas/redigidas */
    private const SENSITIVE_KEYS = [
        'password', 'pass', 'senha', 'token', 'jwt', 'secret',
        'cvv', 'card_number', 'credit_card', 'cc_num', 'auth_token',
        'access_token', 'refresh_token', 'private_key', 'api_key'
    ];

    /**
     * Sanitiza uma mensagem de log em formato de texto simples aplicandos regexes de mascaramento de PII.
     */
    public static function sanitizeLogMessage(string $message): string
    {
        // 1. Mascara CPFs (ex: 123.456.789-00 -> 123.***.***-00)
        $message = preg_replace('/\b(\d{3})\.\d{3}\.\d{3}-(\d{2})\b/', '$1.***.***-$2', $message);

        // 2. Mascara CNPJs (ex: 12.345.678/0001-99 -> 12.***.***/****-99)
        $message = preg_replace('/\b(\d{2})\.\d{3}\.\d{3}\/\d{4}-(\d{2})\b/', '$1.***.***/****-$2', $message);

        // 3. Mascara Números de Cartão de Crédito (ex: 1234 5678 9012 3456 -> ****-****-****-3456)
        $message = preg_replace('/\b(\d{4})[\s-]?\d{4}[\s-]?\d{4}[\s-]?(\d{4})\b/', '****-****-****-$2', $message);

        // 4. Mascara E-mails (ex: joao.silva@dominio.com -> j***@dominio.com)
        $message = preg_replace_callback(
            '/\b([a-zA-Z0-9._%+-]{1})[a-zA-Z0-9._%+-]+@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/',
            fn(array $m) => $m[1] . '***@' . $m[2],
            $message
        );

        // 5. Redige ocorrências de senhas em query strings ou JSON embutido (ex: password=123456 -> password=[REDACTED])
        $message = preg_replace('/(password|senha|token|secret|jwt)\s*[:=]\s*["\']?[^"\'&\s,]+["\']?/i', '$1=[REDACTED]', $message);

        return $message;
    }

    /**
     * Higieniza recursivamente um array de dados (ex: $_POST, $_SESSION, JSON payloads).
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $keyLower = strtolower((string)$key);

            if (in_array($keyLower, self::SENSITIVE_KEYS, true)) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                if (str_contains($keyLower, 'email')) {
                    $sanitized[$key] = self::maskEmail($value);
                } elseif (str_contains($keyLower, 'cpf')) {
                    $sanitized[$key] = self::maskCpf($value);
                } elseif (str_contains($keyLower, 'cnpj')) {
                    $sanitized[$key] = self::maskCnpj($value);
                } else {
                    $sanitized[$key] = self::sanitizeLogMessage($value);
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Mascara a parte local de um endereço de e-mail.
     */
    public static function maskEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        $parts = explode('@', $email, 2);
        $local = $parts[0];
        $domain = $parts[1];

        $maskedLocal = mb_substr($local, 0, 1) . '***';
        return $maskedLocal . '@' . $domain;
    }

    /**
     * Mascara os dígitos centrais de um CPF.
     */
    public static function maskCpf(string $cpf): string
    {
        $digits = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($digits) !== 11) {
            return $cpf;
        }

        return sprintf('%s.***.***-%s', substr($digits, 0, 3), substr($digits, 9, 2));
    }

    /**
     * Mascara os dígitos centrais de um CNPJ.
     */
    public static function maskCnpj(string $cnpj): string
    {
        $digits = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($digits) !== 14) {
            return $cnpj;
        }

        return sprintf('%s.***.***/****-%s', substr($digits, 0, 2), substr($digits, 12, 2));
    }
}
