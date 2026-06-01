<?php

declare(strict_types=1);

namespace Alpha\Auth\Services;

/**
 * AuthServiceInterface - Contrato para os serviços de autenticação da Alpha Engine.
 */
interface AuthServiceInterface
{
    /**
     * Valida as credenciais de um usuário (cliente ou admin).
     * 
     * @param string $identifier E-mail ou Nome de Usuário
     * @param string $password Senha em texto limpo
     * @param string $ip Endereço IP do cliente (opcional)
     * @return array|null Dados formatados do usuário ou null se credenciais incorretas
     */
    public function authenticate(string $identifier, string $password, string $ip = ''): ?array;

    /**
     * Cria e persiste uma sessão para o usuário logado.
     * 
     * @param array $userData Dados do usuário obtidos no login
     * @return string O ID da sessão gerada
     */
    public function createSession(array $userData): string;

    /**
     * Remove a sessão do armazenamento persistente (Redis ou session).
     * 
     * @param string $sessionId ID da sessão a ser destruída
     */
    public function destroySession(string $sessionId): void;
}
