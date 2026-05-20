<?php

namespace Alpha;

use Opencart\System\Engine\Factory;
use Opencart\System\Engine\Registry;
use Opencart\System\Library\Log;

/**
 * AlphaContainer - O interceptor inteligente da Alpha Engine.
 * 
 * Estende a Factory nativa do OpenCart para permitir a substituição
 * progressiva de modelos legados por Repositórios ou Mappers Alpha.
 */
class AlphaContainer extends Factory
{
    /**
     * Intercepta o carregamento de modelos.
     * 
     * @param string $route Caminho do modelo (ex: 'catalog/product')
     * @return object|\Exception
     */
    public function model(string $route): object
    {
        // 1. Normaliza a rota para identificar o componente
        $sanitized_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);
        
        // 2. Mapeamento de "Shortcuts": Se a rota do modelo coincidir com algo migrado
        // podemos retornar o Repositório diretamente do Registry.
        $repository_factory = $this->registry->get('alpha_repository_factory');

        if ($repository_factory) {
            // Exemplo: Se pedirem o modelo 'account/customer', entregamos o Repositório migrado
            // Isso evita a necessidade de modelos "Bridge" em muitos casos.
            if ($sanitized_route === 'account/customer') {
                return $repository_factory->get(\Alpha\Model\Domain\Repositories\CustomerRepository::class);
            }

            if ($sanitized_route === 'catalog/product') {
                return $repository_factory->get(\Alpha\Model\Domain\Repositories\ProductRepository::class);
            }
        }

        // 3. Rastreamento de Débito Técnico: Logs de Depreciação
        // Captura chamadas que ainda dependem do motor de carregamento legado (Loader/Factory nativa).
        /** @var Log|null $log */
        $log = $this->registry->get('log');
        $config = $this->registry->get('config');

        if ($log && $config->get('config_error_log')) {
            $request = $this->registry->get('request');
            $current_route = $request->get['route'] ?? 'N/A';
            $ip = $request->server['REMOTE_ADDR'] ?? 'CLI';

            $log->write(sprintf(
                "[Alpha DEPRECATION] Carregamento de Modelo Legado: '%s' | Originado na rota: '%s' | IP: %s",
                $route,
                $current_route,
                $ip
            ));
        }

        // 4. Delega para a Factory nativa se não houver interceptação Alpha
        return parent::model($route);
    }

    /**
     * Intercepta o carregamento de bibliotecas.
     * 
     * @param string       $route
     * @param array<mixed> $args
     * @return object
     */
    public function library(string $route, array $args = []): object
    {
        // 1. Normaliza a rota
        $sanitized_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

        // 2. Exemplo de Intercepção: Se tentarem carregar uma biblioteca que agora é gerida pela Alpha Engine
        // como um validador de documentos ou um driver de exportação customizado.
        // if ($sanitized_route === 'tool/my_custom_library') { ... }

        // 3. Rastreamento de Débito Técnico para Bibliotecas
        /** @var Log|null $log */
        $log = $this->registry->get('log');
        $config = $this->registry->get('config');

        if ($log && $config->get('config_error_log')) {
            $request = $this->registry->get('request');
            $current_route = $request->get['route'] ?? 'N/A';
            $ip = $request->server['REMOTE_ADDR'] ?? 'CLI';

            // Verifica se a classe existe no padrão PSR-4 da Alpha antes de logar como legado
            // Isso ajuda a diferenciar o que é biblioteca nativa do OC e o que é Alpha.
            $alpha_class = 'Alpha\\Library\\' . str_replace(['_', '/'], ['', '\\'], ucwords($sanitized_route, '_/'));
            
            if (!class_exists($alpha_class)) {
                $log->write(sprintf(
                    "[Alpha DEPRECATION] Carregamento de Biblioteca Legada: '%s' | Originado na rota: '%s' | IP: %s",
                    $route,
                    $current_route,
                    $ip
                ));
            }
        }

        // 4. Delega para a Factory nativa
        return parent::library($route, $args);
    }

    /**
     * Método utilitário para checar se uma rota já possui domínio Alpha
     */
    private function isAlphaDomain(string $route): bool
    {
        // Implementar lógica de verificação baseada em nomes de arquivos core/
        return false;
    }
}