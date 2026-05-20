<?php

namespace Alpha\Middleware\Concrete;

use Alpha\Middleware\MiddlewareInterface;
use Opencart\System\Engine\Registry;

/**
 * SecurityMiddleware - Intercepta o Registry para validar o estado da sessão.
 */
class SecurityMiddleware implements MiddlewareInterface
{
    public function handle(Registry $registry, callable $next): mixed
    {
        $session = $registry->get('session');
        $config = $registry->get('config');

        // Exemplo: Bloqueio de acesso se a manutenção estiver ativa para não-admins
        if ($config->get('config_maintenance') && !$registry->get('user')?->isLogged()) {
            // Lógica de intercepção: podemos alterar o Registry ou forçar um resultado
            // Se decidirmos que o fluxo deve parar, não chamamos $next($registry)
        }

        // Alpha Engine: Podemos injetar serviços globais customizados no Registry aqui
        // antes que o Controller os solicite.
        if (!$registry->has('alpha_initialized')) {
            $registry->set('alpha_initialized', true);
        }

        // Segue para o próximo middleware ou para o Controller
        return $next($registry);
    }
}