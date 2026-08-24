<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * GetCustomerProjectsJsonAction - Retorna lista de projetos ativos do cliente em formato JSON para exibição no modal de cotação.
 */
class GetCustomerProjectsJsonAction implements ActionInterface
{
    public function __construct(
        private ProjectRfqRepository $rfqRepository,
        private ContainerInterface $container
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');
        $lang = $request->getAttribute('lang') ?: 'pt-br';

        if (!$customer || !$customer->isLogged()) {
            $response->getBody()->write(json_encode([
                'logged' => false,
                'login_url' => '/' . $lang . '/login',
                'projects' => []
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $customerId = (int)$customer->getId();
        $rfqs = $this->rfqRepository->findByCustomerId($customerId);

        $projects = array_map(function ($rfq) {
            return [
                'id' => $rfq->getId(),
                'title' => $rfq->getTitle(),
                'category' => $rfq->getCategory(),
                'city' => $rfq->getAddressCity(),
                'state' => $rfq->getAddressState(),
                'status' => $rfq->getStatus()
            ];
        }, $rfqs);

        $response->getBody()->write(json_encode([
            'logged' => true,
            'projects' => $projects
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
