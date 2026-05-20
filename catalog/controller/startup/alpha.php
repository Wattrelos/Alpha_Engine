<?php

namespace Opencart\Catalog\Controller\Startup;

use Alpha\Middleware\RegistryPipeline;
use Alpha\Middleware\Concrete\SecurityMiddleware;
use Alpha\Middleware\Concrete\MaintenanceMiddleware;

/**
 * Alpha Startup - Gerencia a inicialização da Alpha Engine e Middlewares.
 */
class Alpha extends \Opencart\System\Engine\Controller
{
    /**
     * Método index executado automaticamente pelo motor de startup do OpenCart.
     */
    public function index(): ?\Opencart\System\Engine\Action
    {
        // 1. Instancia o pipeline da Alpha Engine
        $pipeline = new RegistryPipeline();

        // 2. Adiciona os Middlewares desejados
        // A ordem aqui importa: verificamos manutenção antes da segurança geral
        $pipeline->pipe(new MaintenanceMiddleware());
        $pipeline->pipe(new SecurityMiddleware());

        // 3. Processa o Registry através da pilha
        // Retornamos o resultado do processamento para que o OpenCart capture possíveis redirecionamentos
        return $pipeline->process($this->registry, function ($registry) {
            $this->logger->write('Alpha Engine: Pipeline de middlewares concluído com sucesso.');
            
            // Marca no Registry que o sistema passou pelas verificações de segurança
            $registry->set('alpha_engine_status', true);

            return null; // Fluxo normal segue
        });
    }
}