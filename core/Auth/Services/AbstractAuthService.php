<?php

declare(strict_types=1);

namespace Alpha\Auth\Services;

use Predis\Client as RedisClient;

/**
 * AbstractAuthService - Implementação base contendo lógica comum de sessões com Redis/$_SESSION.
 */
abstract class AbstractAuthService implements AuthServiceInterface
{
    protected ?RedisClient $redis = null;
    protected bool $useRedis = false;

    // Configurações personalizadas pelas subclasses
    protected string $cookieName = 'session_id';
    protected string $redisPrefix = 'sessao:';
    protected string $sessionKey = 'logged_user';
    protected int $sessionLifetime = 7200; // 2 horas padrão

    public function __construct()
    {
        try {
            $this->redis = new RedisClient([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                'timeout' => 1.0
            ]);
            $this->redis->connect();
            $this->useRedis = true;
        } catch (\Exception $e) {
            $this->useRedis = false;
        }
    }

    /**
     * Cria e persiste a sessão do usuário.
     */
    public function createSession(array $userData): string
    {
        if ($this->useRedis && $this->redis) {
            $sessionId = bin2hex(random_bytes(32));
            $this->redis->set($this->redisPrefix . $sessionId, json_encode($userData));
            $this->redis->expire($this->redisPrefix . $sessionId, $this->sessionLifetime);
        } else {
            // Fallback para sessão local PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_name($this->cookieName);
                session_start();
            } else {
                session_regenerate_id(true);
            }
            $sessionId = session_id();
            if (empty($sessionId)) {
                $sessionId = bin2hex(random_bytes(32));
                session_id($sessionId);
            }
            $_SESSION[$this->sessionKey] = json_encode($userData);
            $_SESSION['expire'] = time() + $this->sessionLifetime;
            $_SESSION[$this->sessionKey . '_expire'] = time() + $this->sessionLifetime;
        }

        return $sessionId;
    }

    /**
     * Remove a sessão ativa.
     */
    public function destroySession(string $sessionId): void
    {
        if ($this->useRedis && $this->redis) {
            $this->redis->del($this->redisPrefix . $sessionId);
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_name($this->cookieName);
                if (!empty($sessionId)) {
                    session_id($sessionId);
                }
                session_start();
            }
            unset($_SESSION[$this->sessionKey]);
            unset($_SESSION[$this->sessionKey . '_expire']);

            if (empty($_SESSION)) {
                session_destroy();
            }
        }
    }
}
