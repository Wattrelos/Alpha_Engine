<?php

declare(strict_types=1);

namespace Alpha\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Predis\Client as RedisClient;

/**
 * AdminSessionMiddleware - Garante a segurança das rotas administrativas verificando a sessão do admin.
 */
class AdminSessionMiddleware
{
    private ?RedisClient $redis = null;
    private bool $useRedis = false;
    private ?\Psr\Container\ContainerInterface $container = null;

    private const ROUTE_PERMISSION_MAP = [
        'admin.dashboard' => 'common/dashboard',
        'admin.product.list' => 'catalog/product',
        'admin.product.edit' => 'catalog/product',
        'admin.product.update' => 'catalog/product',
        'admin.category.list' => 'catalog/category',
        'admin.category.create' => 'catalog/category',
        'admin.category.edit' => 'catalog/category',
        'admin.category.update' => 'catalog/category',
        'admin.category.delete' => 'catalog/category',
        'admin.manufacturer.list' => 'catalog/manufacturer',
        'admin.manufacturer.create' => 'catalog/manufacturer',
        'admin.manufacturer.store' => 'catalog/manufacturer',
        'admin.manufacturer.edit' => 'catalog/manufacturer',
        'admin.manufacturer.update' => 'catalog/manufacturer',
        'admin.manufacturer.delete' => 'catalog/manufacturer',
        'admin.supplier.list' => 'procurement/supplier',
        'admin.supplier.create' => 'procurement/supplier',
        'admin.supplier.store' => 'procurement/supplier',
        'admin.supplier.edit' => 'procurement/supplier',
        'admin.supplier.update' => 'procurement/supplier',
        'admin.supplier.delete' => 'procurement/supplier',
        'admin.customer.list' => 'customer/customer',
        'admin.customer.show' => 'customer/customer',
        'admin.customer.create' => 'customer/customer',
        'admin.customer.edit' => 'customer/customer',
        'admin.customer.address.create' => 'customer/customer',
        'admin.customer.address.edit' => 'customer/customer',
        'admin.customer.address.delete' => 'customer/customer',
        'admin.setting.edit' => 'setting/setting',
        'admin.setting.update' => 'setting/setting',
        'admin.orders.index' => 'sale/order',
        'admin.orders.show' => 'sale/order',
        'admin.orders.invoice' => 'sale/order',
        'admin.orders.update_status' => 'sale/order',
        'admin.user.list' => 'user/user',
        'admin.user.create' => 'user/user',
        'admin.user.edit' => 'user/user',
        'admin.user.delete' => 'user/user',
        'admin.user_group.list' => 'user/user_group',
        'admin.user_group.create' => 'user/user_group',
        'admin.user_group.edit' => 'user/user_group',
        'admin.user_group.delete' => 'user/user_group',
    ];

    public function __construct(?\Psr\Container\ContainerInterface $container = null)
    {
        $this->container = $container;
        $redisHost = $_ENV['REDIS_HOST'] ?? '';
        $redisEnabled = filter_var($_ENV['REDIS_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if ($redisEnabled && !empty($redisHost)) {
            try {
                $this->redis = new RedisClient([
                    'host' => $redisHost,
                    'port' => $_ENV['REDIS_PORT'] ?? 6379,
                    'password' => ($_ENV['REDIS_PASSWORD'] ?? '') ?: null,
                    'timeout' => 1.0
                ]);
                $this->redis->connect();
                $this->useRedis = true;
            } catch (\Exception $e) {
                $this->useRedis = false;
            }
        } else {
            $this->useRedis = false;
        }
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        // Detecta OOBE se a tabela de usuários estiver vazia
        try {
            $userRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\UserRepository::class);
            if (count($userRepo->findAll()) === 0) {
                $response = new Response();
                return $response->withHeader('Location', '/setup')->withStatus(302);
            }
        } catch (\Exception $e) {
            // Ignora
        }

        $cookies = $request->getCookieParams();
        $sessionId = $cookies['admin_session_id'] ?? '';

        $sessionData = null;

        if ($this->useRedis && $this->redis) {
            // Tenta buscar os dados do administrador guardados na RAM do Redis
            $sessionData = $this->redis->get("sessao:admin:" . $sessionId);

            if ($sessionData) {
                // Estende o tempo do administrador no Redis por mais 2 horas
                $this->redis->expire("sessao:admin:" . $sessionId, 7200);
            }
        } else {
            // Fallback para sessão local PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_name('admin_session_id');
                if (!empty($sessionId)) {
                    session_id($sessionId);
                }
                session_start();
            }

            $expire = $_SESSION['logged_admin_expire'] ?? 0;
            if ($expire > time()) {
                $sessionData = $_SESSION['logged_admin'] ?? null;
                if ($sessionData) {
                    $_SESSION['logged_admin_expire'] = time() + 7200;
                }
            }
        }

        if (!$sessionData) {
            // Sem sessão válida: redireciona para a tela de login do painel administrativo
            $response = new Response();
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        // Injeta os dados do administrador na requisição para consumo posterior
        $user = json_decode($sessionData);
        $request = $request->withAttribute('logged_admin', $user);

        // --- Carrega permissões do papel (UserGroup) ---
        $userGroupId = isset($user->user_group_id) ? (int)$user->user_group_id : 0;
        $permissions = ['access' => [], 'modify' => []];

        if ($userGroupId > 0) {
            try {
                $userGroupRepo = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Model\Domain\Repositories\UserGroupRepository::class);
                $userGroup = $userGroupRepo->find($userGroupId);
                if ($userGroup instanceof \Alpha\Model\Domain\Entities\UserGroup) {
                    $permissions = $userGroup->getPermissionArray();
                }
            } catch (\Exception $e) {
                // Ignora erros
            }
        }

        // Injeta os dados do administrador e suas permissões globalmente no Twig
        if ($this->container && $this->container->has(\Twig\Environment::class)) {
            $twig = $this->container->get(\Twig\Environment::class);
            $twig->addGlobal('logged_admin', $user);
            $twig->addGlobal('logged_admin_permissions', $permissions);
        }

        // --- Verificação de privilégios / permissões ---
        $permissionKey = null;
        try {
            $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            $routeName = $route ? $route->getName() : '';
            $permissionKey = self::ROUTE_PERMISSION_MAP[$routeName] ?? null;
        } catch (\RuntimeException $e) {
            // Se a rota não foi resolvida (ex: testes unitários sem dispatcher), ignora
        }

        if ($permissionKey) {
            $accessList = $permissions['access'] ?? [];
            $modifyList = $permissions['modify'] ?? [];
            $isModify = in_array(strtoupper($request->getMethod()), ['POST', 'PUT', 'DELETE', 'PATCH']);

            if ($isModify) {
                if ($userGroupId !== 1 && !in_array($permissionKey, $modifyList)) {
                    return $this->render403('Você não tem privilégios de alteração para este recurso.');
                }
            } else {
                if ($userGroupId !== 1 && !in_array($permissionKey, $accessList)) {
                    return $this->render403('Você não tem permissão para visualizar esta página.');
                }
            }
        }

        return $handler->handle($request);
    }

    private function render403(string $message): Response
    {
        $response = new Response();
        try {
            $twig = null;
            if ($this->container && $this->container->has(\Slim\Views\Twig::class)) {
                $twig = $this->container->get(\Slim\Views\Twig::class);
            } elseif ($this->container && $this->container->has(\Twig\Environment::class)) {
                $twigEnv = $this->container->get(\Twig\Environment::class);
                $html = $twigEnv->render('admin/pages/errors/403.html.twig', ['message' => $message]);
                $response->getBody()->write($html);
                return $response->withStatus(403);
            }

            if ($twig) {
                $html = $twig->fetch('admin/pages/errors/403.html.twig', ['message' => $message]);
                $response->getBody()->write($html);
                return $response->withStatus(403);
            }
        } catch (\Exception $e) {
            // Em caso de qualquer falha na renderização do template, retorna string simples
        }

        $response->getBody()->write("<h1>403 Forbidden</h1><p>" . htmlspecialchars($message) . "</p>");
        return $response->withStatus(403);
    }
}
