<?php

namespace Alpha\Support\Cache;

/**
 * Class FilesystemCacheStrategy
 * 
 * Implementação de cache físico que armazena dados serializados em disco.
 * Ideal para serializar entidades pesadas da Alpha Engine quando o Redis não estiver disponível.
 */
class FilesystemCacheStrategy implements CacheStrategyInterface
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        // Utiliza o DIR_CACHE do OpenCart se definido, caso contrário usa o temp do SO
        $this->cacheDir = $cacheDir ?? (defined('DIR_CACHE') ? DIR_CACHE : sys_get_temp_dir() . '/alpha_cache/');
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Gera um nome de arquivo seguro baseado no hash da chave de cache.
     */
    private function getFilePath(string $key): string
    {
        return $this->cacheDir . 'alpha_cache_' . md5($key) . '.cache';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return $default;
        }

        $data = @unserialize($content);
        
        if ($data === false || !is_array($data) || !isset($data['expires'], $data['value'])) {
            $this->delete($key);
            return $default;
        }

        // Validação de expiração (Time To Live)
        if ($data['expires'] !== 0 && time() > $data['expires']) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $file = $this->getFilePath($key);
        $expires = $ttl > 0 ? time() + $ttl : 0;
        
        $data = [
            'expires' => $expires,
            'value'   => $value
        ];
        
        // Uso de LOCK_EX evita que dois processos gravem ao mesmo tempo e corrompam o arquivo
        $result = file_put_contents($file, serialize($data), LOCK_EX);
        
        return $result !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . 'alpha_cache_*.cache');
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (is_file($file)) {
                $success = $success && unlink($file);
            }
        }
        return $success;
    }

    public function has(string $key): bool
    {
        // Recupera utilizando uma flag única de fallback para ter certeza absoluta da existência
        return $this->get($key, $this) !== $this;
    }
}