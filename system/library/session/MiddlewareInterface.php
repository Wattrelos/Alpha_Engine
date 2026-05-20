<?php

namespace Alpha\Middleware;

use Opencart\System\Engine\Registry;

/**
 * MiddlewareInterface - Contrato para interceptores da Alpha Engine.
 */
interface MiddlewareInterface
{
    /**
     * Processa a intercepção do Registry.
     * 
     * @param Registry $registry
     * @param callable $next O próximo middleware ou a execução do controller.
     */
    public function handle(Registry $registry, callable $next): mixed;
}