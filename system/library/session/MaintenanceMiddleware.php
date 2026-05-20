<?php

namespace Alpha\Middleware\Concrete;

use Alpha\Middleware\MiddlewareInterface;
use Opencart\System\Engine\Registry;
use Opencart\System\Engine\Action;

/**
 * MaintenanceMiddleware - Intercepta o fluxo se a loja estiver em manutenção.
 * 
 * Alpha Engine:
 * - Centralização de regras de exceção (API e rotas de internacionalização).
 * - Verificação de privilégios administrativos via Registry.
 */
class MaintenanceMiddleware implements MiddlewareInterface
{
    public function handle(Registry $registry, callable $next): mixed
    {
        $config = $registry->get('config');

        if ($config->get('config_maintenance')) {
            $request = $registry->get('request');
            $route = (string)($request->get['route'] ?? $config->get('action_default'));

            // Rotas que devem ser ignoradas pela manutenção
            $ignore = [
                'common/language/language',
                'common/currency/currency'
            ];

            // Verifica se o usuário está logado na administração
            $user = new \Opencart\System\Library\Cart\User($registry);

            if (substr($route, 0, 3) != 'api' && !in_array($route, $ignore) && !$user->isLogged()) {
                return new Action('common/maintenance');
            }
        }

        return $next($registry);
    }
}