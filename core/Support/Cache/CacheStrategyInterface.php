<?php

namespace Alpha\Support\Cache;

/**
 * Interface CacheStrategyInterface
 * 
 * Define o contrato padrão para os mecanismos de cache da Alpha Engine,
 * com inspiração na PSR-16 (Simple Cache) para facilitar a troca de drivers (File, Redis, Memcached).
 */
interface CacheStrategyInterface
{
    /**
     * Recupera um item do cache.
     *
     * @param string $key Chave única do item.
     * @param mixed $default Valor retornado caso a chave não exista ou esteja expirada.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persiste um dado no cache.
     *
     * @param string $key Chave única do item.
     * @param mixed $value O dado a ser armazenado (deve ser serializável).
     * @param int $ttl Tempo de vida útil (Time To Live) em segundos.
     * @return bool True em caso de sucesso, false caso contrário.
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Remove um item específico do cache.
     *
     * @param string $key Chave única do item.
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Limpa todo o cache gerenciado por esta estratégia.
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * Verifica se um item existe no cache e ainda é válido (não expirou).
     *
     * @param string $key Chave única do item.
     * @return bool
     */
    public function has(string $key): bool;
}