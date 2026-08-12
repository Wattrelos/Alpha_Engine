<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Psr\Container\ContainerInterface;

class NewsletterAction implements ActionInterface
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly ContainerInterface $container
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customerHelper = $this->container->get('customer');

        if (!$customerHelper || !$customerHelper->isLogged()) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Acesso não autorizado. Por favor, faça login.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $customerId = $customerHelper->getId();
        $customer = $this->customerRepository->find($customerId);

        if (!$customer) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Cliente não encontrado.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $parsedBody = $request->getParsedBody();
        $data = is_array($parsedBody) ? $parsedBody : [];
        $newsletterStatus = isset($data['newsletter']) ? (bool)$data['newsletter'] : false;

        $customer->setNewsletter($newsletterStatus);
        $this->customerRepository->updateProfile($customer);

        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
