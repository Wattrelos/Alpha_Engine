<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Auth\Services\CustomerAuthService;
use Alpha\Support\CookieHelper;
use Slim\Routing\RouteContext;

/**
 * RegisterAction - Processa a criação de conta do cliente via POST /cadastro (AJAX) com Auto-Login imediato.
 */
class RegisterAction implements ActionInterface
{
    private CustomerRepository $customerRepository;
    private CustomerAuthService $authService;

    public function __construct(CustomerRepository $customerRepository, CustomerAuthService $authService)
    {
        $this->customerRepository = $customerRepository;
        $this->authService = $authService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody();
        if (empty($params)) {
            $rawBody = (string)$request->getBody();
            $params = json_decode($rawBody, true) ?? [];
        }

        // Delega o cadastro e as validações para o CustomerRepository
        $result = $this->customerRepository->registerCustomer($params);

        if (!empty($result['errors'])) {
            // Retorna erros estruturados para serem hidratados via JS (data-oc-toggle="ajax")
            $response->getBody()->write(json_encode([
                'error' => $result['errors']
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $customerId = (int)($result['customer_id'] ?? 0);
        $customer = $customerId > 0 ? $this->customerRepository->find($customerId) : null;

        // Auto-Login: inicia a sessão do novo cliente imediatamente
        $userData = [
            'id'                => $customerId,
            'name'              => $customer ? trim($customer->getFirstname() . ' ' . $customer->getLastname()) : trim(($params['firstname'] ?? '') . ' ' . ($params['lastname'] ?? '')),
            'email'             => $customer ? $customer->getEmail() : ($params['email'] ?? ''),
            'telephone'         => $customer ? $customer->getTelephone() : ($params['telephone'] ?? ''),
            'customer_group_id' => $customer ? $customer->getCustomerGroupId() : (int)($result['customer_group_id'] ?? 1),
            'role'              => 'client_premium'
        ];

        $sessionId = $this->authService->createSession($userData);
        $cookieValue = CookieHelper::makeCookieHeader($request, 'session_id', $sessionId, 7200);

        // Obtém o parser de rotas para gerar o redirecionamento dinâmico
        $routeParser = null;
        try {
            $routeContext = RouteContext::fromRequest($request);
            $routeParser = $routeContext->getRouteParser();
        } catch (\Throwable $e) {
            // Contextos sem roteador do Slim (e.g. testes diretos)
        }

        $lang = $request->getAttribute('lang', 'pt-br');
        $defaultRedirect = $routeParser ? $routeParser->urlFor('account.index', ['lang' => $lang]) : '/' . $lang . '/account';

        $redirect = trim((string)($params['redirect'] ?? $request->getQueryParams()['redirect'] ?? ''));
        $redirectUrl = (!empty($redirect) && str_starts_with($redirect, '/')) ? $redirect : $defaultRedirect;

        // Captura tokens CSRF atuais da sessão para repassar ao cliente AJAX
        $csrfName = (string)$request->getAttribute('csrf_name', '');
        $csrfValue = (string)$request->getAttribute('csrf_value', '');
        if (empty($csrfName) && !empty($_SESSION['csrf'])) {
            foreach ($_SESSION['csrf'] as $k => $v) {
                $csrfName = (string)$k;
                $csrfValue = (string)$v;
                break;
            }
        }

        // Registro e Auto-Login efetuados com sucesso!
        $response->getBody()->write(json_encode([
            'success'     => true,
            'customer_id' => $customerId,
            'redirect'    => $redirectUrl,
            'csrf'        => [
                'name'  => $csrfName,
                'value' => $csrfValue
            ]
        ], JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Set-Cookie', $cookieValue)
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}

