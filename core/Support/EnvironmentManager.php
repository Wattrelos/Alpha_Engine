<?php

namespace Alpha\Support;

/**
 * EnvironmentManager - Gerenciador de Leitura e Persistência Atômica do Arquivo .env.
 * 
 * Garante atualizações seguras de variáveis de ambiente com file lock (LOCK_EX),
 * substituição atômica (tmp -> rename), sanitização de caracteres especiais e permissões restritas.
 */
class EnvironmentManager
{
    private string $envPath;
    private string $examplePath;

    public function __construct(?string $envPath = null)
    {
        $baseDir = defined('DIR_ROOT') ? DIR_ROOT : realpath(__DIR__ . '/../../') . '/';
        $this->envPath = $envPath ?? ($baseDir . '.env');
        $this->examplePath = $baseDir . '.env.example';
    }

    /**
     * Verifica se o sistema está marcado como instalado
     */
    public function isInstalled(): bool
    {
        $appInstalled = $_ENV['APP_INSTALLED'] ?? getenv('APP_INSTALLED');
        
        if ($appInstalled === null && file_exists($this->envPath)) {
            $parsed = $this->readEnv();
            $appInstalled = $parsed['APP_INSTALLED'] ?? false;
        }

        return filter_var($appInstalled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Retorna o caminho do arquivo .env
     */
    public function getEnvPath(): string
    {
        return $this->envPath;
    }

    /**
     * Lê e faz parse das variáveis do arquivo .env
     */
    public function readEnv(): array
    {
        $targetFile = file_exists($this->envPath) ? $this->envPath : (file_exists($this->examplePath) ? $this->examplePath : null);
        if (!$targetFile) {
            return [];
        }

        $lines = file($targetFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove aspas simples ou duplas envolventes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $data[$key] = $value;
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }

        return $data;
    }

    /**
     * Atualiza o arquivo .env de forma atômica e segura (LOCK_EX + atomic rename)
     */
    public function updateEnv(array $newValues): bool
    {
        $currentData = $this->readEnv();
        $mergedData = array_merge($currentData, $newValues);

        $lines = [];
        $lines[] = "# Configurações Gerais da Alpha Engine (SaaS)";
        $lines[] = "# Gerado Automaticamente pelo Setup Wizard em " . date('Y-m-d H:i:s');
        $lines[] = "";

        foreach ($mergedData as $key => $value) {
            $formattedValue = $this->formatEnvValue((string)$value);
            $lines[] = "{$key}={$formattedValue}";
        }

        $content = implode("\n", $lines) . "\n";
        $tmpPath = $this->envPath . '.tmp';

        // Escrita atômica em arquivo temporário com trava exclusiva
        if (file_put_contents($tmpPath, $content, LOCK_EX) === false) {
            return false;
        }

        // Substituição atômica de arquivo
        if (!rename($tmpPath, $this->envPath)) {
            @unlink($tmpPath);
            return false;
        }

        // Ajusta permissões do arquivo para segurança (leitura/escrita apenas pro servidor/dono)
        @chmod($this->envPath, 0640);

        // Atualiza $_ENV e getenv() em tempo de execução
        foreach ($mergedData as $key => $value) {
            $_ENV[$key] = (string)$value;
            putenv("{$key}=" . (string)$value);
        }

        return true;
    }

    /**
     * Gera uma string secreta aleatória segura de alta entropia
     */
    public static function generateRandomKey(int $length = 32): string
    {
        return bin2hex(random_bytes((int)ceil($length / 2)));
    }

    /**
     * Formata o valor de uma variável para gravação segura no arquivo .env
     */
    private function formatEnvValue(string $value): string
    {
        if (strtolower($value) === 'true' || strtolower($value) === 'false') {
            return strtolower($value);
        }

        if (is_numeric($value)) {
            return $value;
        }

        // Se contiver espaços, aspas ou caracteres especiais, envolve em aspas duplas e escapa
        if ($value === '' || preg_match('/[\s#$"\'\\\\]/', $value)) {
            $escaped = str_replace(['\\', '"', '$'], ['\\\\', '\"', '\$'], $value);
            return '"' . $escaped . '"';
        }

        return $value;
    }
}
