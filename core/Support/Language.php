<?php

namespace Alpha\Support;

use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;

class Language
{
    private string $code;
    private SymfonyTranslator $symfonyTranslator;
    private array $loadedNamespaces = [];
    private array $nestedData = [];
    private string $localesDir;
    public function __construct(
        string $code = 'pt-br',
        string $localesDir = '/var/www/html/agsonhos/Locales'
    ) {
        $this->code = $code;
        $this->localesDir = $localesDir;
        $this->initializeSymfonyTranslator();
    }

    private function initializeSymfonyTranslator(): void
    {
        $this->symfonyTranslator = new SymfonyTranslator($this->code);
        $this->symfonyTranslator->addLoader('array', new ArrayLoader());
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        if ($this->code !== $code) {
            $this->code = $code;
            $this->initializeSymfonyTranslator();
            
            // Recarrega todos os namespaces ativos para o novo idioma
            $namespaces = array_keys($this->loadedNamespaces);
            $this->loadedNamespaces = [];
            $this->nestedData = [];
            foreach ($namespaces as $ns) {
                $this->load($ns);
            }
        }
        return $this;
    }

    /**
     * Carrega as traduções de um namespace.
     * Tenta buscar no novo diretório JSON: Locales/{lang}/{lang}.{namespace}.json
     */
    public function load(string $route): array
    {
        if (isset($this->loadedNamespaces[$route])) {
            return $this->flattenArray($this->nestedData[$route] ?? []);
        }

        // Normaliza a rota para buscar o arquivo JSON correspondente
        $namespace = str_replace(['/', '\\'], '.', $route);

        $jsonFile = $this->localesDir . '/' . $this->code . '/' . $this->code . '.' . $namespace . '.json';
        
        if (!is_file($jsonFile)) {
            // Tenta usar a rota original caso o namespace normalized não bata
            $jsonFile = $this->localesDir . '/' . $this->code . '/' . $this->code . '.' . $route . '.json';
        }

        if (is_file($jsonFile)) {
            $content = file_get_contents($jsonFile);
            $data = json_decode($content, true) ?: [];
            
            $this->nestedData[$route] = $data;
            
            $flat = $this->flattenArray($data);
            $this->symfonyTranslator->addResource('array', $flat, $this->code, $route);
            $this->loadedNamespaces[$route] = true;
            
            return $flat;
        }

        return [];
    }

    /**
     * Retorna a tradução associada a uma chave específica.
     * Implementa o fallback DRY para o namespace global 'common'.
     */
    public function get(string $key, string $namespace = 'common'): string
    {
        $translated = $this->symfonyTranslator->trans($key, [], $namespace);
        
        // Se a tradução não foi encontrada no namespace e não era a global, tenta a global 'common'
        if ($translated === $key && $namespace !== 'common') {
            $translated = $this->symfonyTranslator->trans($key, [], 'common');
        }

        // Fallback: Varre outros namespaces carregados se a chave não foi resolvida
        if ($translated === $key) {
            foreach (array_keys($this->loadedNamespaces) as $ns) {
                if ($ns !== $namespace && $ns !== 'common') {
                    $test = $this->symfonyTranslator->trans($key, [], $ns);
                    if ($test !== $key) {
                        return $test;
                    }
                }
            }
        }

        return $translated;
    }

    public function set(string $key, string $value): void
    {
        $this->symfonyTranslator->addResource('array', [$key => $value], $this->code, 'common');
        
        if (!isset($this->nestedData['common'])) {
            $this->nestedData['common'] = [];
        }
        $this->nestedData['common'][$key] = $value;
    }

    /**
     * Retorna a árvore de dados aninhados para um namespace carregado.
     * Útil para injetar dados no Twig.
     */
    public function getNestedData(string $namespace): array
    {
        return $this->nestedData[$namespace] ?? [];
    }

    public function getSymfonyTranslator(): SymfonyTranslator
    {
        return $this->symfonyTranslator;
    }

    /**
     * Método utilitário para aplanar um array aninhado em chaves separadas por pontos.
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
}
