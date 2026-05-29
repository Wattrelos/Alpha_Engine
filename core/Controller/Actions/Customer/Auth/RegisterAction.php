<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerRepository;

/**
 * RegisterAction - Processa a criação de conta do cliente via POST /cadastro (AJAX).
 */
class RegisterAction implements ActionInterface
{
    private CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody();

        // Delega o cadastro e as validações para o CustomerRepository
        $result = $this->customerRepository->registerCustomer($params);

        if (!empty($result['errors'])) {
            // Retorna erros estruturados para serem hidratados via JS (data-oc-toggle="ajax")
            $response->getBody()->write(json_encode([
                'error' => $result['errors']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Registro efetuado com sucesso! Redireciona para o login
        $response->getBody()->write(json_encode([
            'redirect' => '/login?success=1'
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
