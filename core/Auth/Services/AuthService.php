<?php

namespace Alpha\Auth\Services;

use Predis\Client as RedisClient;
use Alpha\Model\Domain\Entities\Customer;
use Alpha\Model\Domain\Repositories\CustomerRepository;

/**
 * O AuthService.php é o cérebro da autenticação. Ele isola toda a lógica de segurança (interação com o Redis)
 * do código visual (Controllers), seguindo o princípio de "Separação de Responsabilidades".
 * Ele é responsável por verificar as credenciais do usuário e criar/deletar sessões seguras na memória RAM.
 */
class AuthService
{
    private ?RedisClient $redis = null;
    private ?CustomerRepository $customerRepository = null;
    private bool $useRedis = false;

    public function __construct(?CustomerRepository $customerRepository = null)
    {
        $this->customerRepository = $customerRepository;

        try {
            // Conecta ao Redis usando as variáveis seguras do seu arquivo .env
            $this->redis = new RedisClient([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                'timeout' => 1.0 // Timeout baixo para responder rápido se o Redis estiver offline
            ]);
            $this->redis->connect();
            $this->useRedis = true;
        } catch (\Exception $e) {
            $this->useRedis = false;
        }
    }

    /**
     * Valida as credenciais do usuário.
     */
    public function authenticate(string $email, string $password): ?array
    {
        if ($this->customerRepository === null) {
            return null;
        }

        $customer = $this->customerRepository->findByEmail($email);
        if ($customer && $email === $customer->getEmail() && password_verify($password, $customer->getPassword())) {
            return [
                'id' => $customer->getId(),
                'name' => trim($customer->getFirstname() . ' ' . $customer->getLastname()),
                'role' => 'client_premium'
            ];
        }

        return null; // Credenciais inválidas
    }

    public function createSession(array $userData): string
    {
        // Define o tempo de validade inicial (Ex: 2 horas = 7200 segundos)
        $tempoValidade = 7200;

        if ($this->useRedis && $this->redis) {
            // 1. Cria um identificador de sessão único, longo e seguro para o Redis
            $sessionId = bin2hex(random_bytes(32));
            // 2. Guarda os dados do usuário no Redis em formato JSON
            $this->redis->set("sessao:" . $sessionId, json_encode($userData));
            $this->redis->expire("sessao:" . $sessionId, $tempoValidade);
        } else {
            // Fallback para sessão local PHP com AlphaSessionHandler
            if (session_status() === PHP_SESSION_NONE) {
                session_name('session_id');
                session_start();
            }
            $sessionId = session_id();
            $_SESSION['logged_user'] = json_encode($userData);
            $_SESSION['expire'] = time() + $tempoValidade;
        }

        return $sessionId;
    }

    /**
     * Destrói a sessão do usuário imediatamente do Redis (ou $_SESSION caso inativo) para Logout.
     */
    public function destroySession(string $sessionId): void
    {
        if ($this->useRedis && $this->redis) {
            $this->redis->del("sessao:" . $sessionId);
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_name('session_id');
                if (!empty($sessionId)) {
                    session_id($sessionId);
                }
                session_start();
            }
            session_destroy();
            $_SESSION = [];
        }
    }
}
