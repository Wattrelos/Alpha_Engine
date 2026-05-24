<?php

namespace Alpha\Middleware\Concrete;

use Alpha\Middleware\MiddlewareInterface;
use Opencart\System\Engine\Registry;
use Opencart\System\Engine\Action;
use Alpha\Mappers\EntityMappers\ApiSessionMapper;

/**
 * ApiAuthMiddleware - Valida a autenticação de requisições de API via token_session.
 * 
 * Melhoras Alpha Engine:
 * - Desacoplamento: Remove a necessidade de queries manuais nos controladores.
 * - Segurança: Valida o token_session e o IP de origem utilizando o ApiSessionMapper.
 * - Contextualização: Injeta a entidade ApiSession validada no Registry para uso posterior.
 */
class ApiAuthMiddleware implements MiddlewareInterface
{
    public function handle(Registry $registry, callable $next): mixed
    {
        $request = $registry->get('request');
        $route = (string)($request->get['route'] ?? '');

        // 1. Intercepta requisições destinadas à API
        if (str_starts_with($route, 'api/')) {
            $token = $request->get['api_token'] ?? $request->post['api_token'] ?? '';

            if (!$token) {
                return new Action('error/not_found');
            }

            // 2. Resolve o ApiSessionMapper via MapperFactory registrado no Registry
            /** @var ApiSessionMapper $apiSessionMapper */
            $apiSessionMapper = $registry->get('alpha_mapper_factory')->get(ApiSessionMapper::class);

            // 3. Validação centralizada do token utilizando a nova estrutura de surrogate key
            $apiSession = $apiSessionMapper->getByToken((string)$token);
            $currentIp = (string)($request->server['REMOTE_ADDR'] ?? '');

            // 4. Verificação de existência e correspondência de IP (Security Check)
            if (!$apiSession || ($apiSession->getIp() && $apiSession->getIp() !== $currentIp)) {
                return new Action('error/not_found');
            }

            // 5. Atualização automática do IP e data de modificação para rastreabilidade
            $apiSession->setIp($currentIp);
            $apiSession->setDateModified(date('Y-m-d H:i:s'));
            
            $apiSessionMapper->save($apiSession);

            // 6. Disponibiliza a entidade de sessão validada para os controllers
            $registry->set('api_session_entity', $apiSession);
        }

        return $next($registry);
    }
}
