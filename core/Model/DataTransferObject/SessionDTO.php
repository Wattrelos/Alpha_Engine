<?php

namespace Alpha\Model\DataTransferObject;

use Alpha\Model\Domain\Entities\Session;

/**
 * SessionDTO - Extrai e tipa dados da string serializada da sessão.
 * 
 * Melhoras Alpha Engine:
 * - Parsing Seguro: Trata a desserialização de dados de forma resiliente.
 * - Tipagem Estrita: Garante que IDs de cliente e carrinho sejam inteiros válidos (PHP 8.4).
 * - Abstração: Isola a complexidade do estado da sessão da lógica de domínio.
 */
class SessionDTO
{
    private array $items = [];

    public function __construct(Session $session)
    {
        $rawData = $session->getData();
        
        if (!empty($rawData)) {
            // Tenta desserializar considerando o formato padrão do PHP usado pelo SessionHandler
            $decoded = @unserialize($rawData);
            
            // Fallback para JSON caso o handler tenha sido configurado para tal
            if ($decoded === false && str_starts_with($rawData, '{')) {
                $decoded = json_decode($rawData, true);
            }

            $this->items = is_array($decoded) ? $decoded : [];
        }
    }

    /**
     * Retorna o ID do cliente logado na sessão.
     */
    public function getCustomerId(): int
    {
        return (int)($this->items['customer_id'] ?? 0);
    }

    /**
     * Retorna o ID do carrinho associado à sessão.
     */
    public function getCartId(): int
    {
        return (int)($this->items['cart_id'] ?? 0);
    }

    /**
     * Retorna o ID do idioma ativo.
     */
    public function getLanguageId(): int
    {
        return (int)($this->items['language_id'] ?? 0);
    }

    /**
     * Retorna o código da moeda ativa (ex: 'BRL').
     */
    public function getCurrencyCode(): string
    {
        return (string)($this->items['currency'] ?? '');
    }

    /**
     * Obtém um valor genérico da sessão com suporte a valor padrão.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * Verifica a existência de uma chave na sessão.
     */
    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }
}