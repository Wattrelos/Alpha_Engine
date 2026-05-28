<?php

namespace Alpha\Auth\Services;

use Predis\Client as RedisClient;

/*
* O AuthService.php é o cérebro da autenticação. Ele isola toda a lógica de segurança (interação com o Redis)
* do código visual (Controllers), seguindo o princípio de "Separação de Responsabilidades".
* Ele é responsável por verificar as credenciais do usuário e criar/deletar sessões seguras na memória RAM.
*/

class AuthService
{
    private $redis;

    public function __construct()
    {
        // Conecta ao Redis usando as variáveis seguras do seu arquivo .env
        $this->redis = new RedisClient([
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'password' => $_ENV['REDIS_PASSWORD'] ?: null
        ]);
    }

    /**
     * Valida as credenciais do usuário.
     * No longo prazo, aqui você injetaria seu Model do MySQL para checar a senha criptografada.
     */
    public function authenticate(string $email, string $password): ?array
    {
        // [Simulação]: Busca no banco de dados. 
        // Em produção seria: $user = $this->userModel->findByEmail($email);
        if ($email === 'cliente@email.com' && $password === '123456') {
            return [
                'id' => 4589,
                'name' => 'João Silva',
                'role' => 'client_premium'
            ];
        }

        return null; // Credenciais inválidas
    }

    /**
     * Cria uma sessão única e ultra rápida dentro da memória RAM do Redis.
     */
    public function createSession(array $userData): string
    {
        // 1. Cria um identificador de sessão único, longo e seguro
        $sessionId = bin2hex(random_bytes(32));

        // 2. Define o tempo de validade inicial (Ex: 2 horas = 7200 segundos)
        $tempoValidade = 7200;

        // 3. Guarda os dados do usuário no Redis em formato JSON
        $this->redis->set("sessao:" . $sessionId, json_encode($userData));
        $this->redis->expire("sessao:" . $sessionId, $tempoValidade);

        return $sessionId;
    }

    /**
     * Destrói a sessão do usuário imediatamente do Redis (Logout).
     */
    public function destroySession(string $sessionId): void
    {
        $this->redis->del("sessao:" . $sessionId);
    }
}
