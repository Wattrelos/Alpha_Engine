<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBidRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * AcceptBidAction - Processa a seleção e aceite da proposta do prestador pelo cliente (RF035).
 */
class AcceptBidAction implements ActionInterface
{
    public function __construct(
        private ProjectRfqRepository $rfqRepository,
        private ProjectBidRepository $bidRepository,
        private ContainerInterface $container
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');
        $lang = $request->getAttribute('lang', 'pt-br');

        if (!$customer || !$customer->isLogged()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $rfqId = (int)($args['rfq_id'] ?? 0);
        $bidId = (int)($args['bid_id'] ?? 0);

        $rfq = $this->rfqRepository->find($rfqId);
        $bid = $this->bidRepository->find($bidId);

        if ($rfq && $bid && $rfq->getCustomerId() === (int)$customer->getId() && $bid->getRfqId() === $rfqId) {
            $this->bidRepository->acceptBid($bidId);
            $this->rfqRepository->updateStatus($rfqId, 'in_progress', $bid->getProviderId());
        }

        return $response
            ->withHeader('Location', '/' . $lang . '/account/projetos/' . $rfqId . '/propostas')
            ->withStatus(302);
    }
}
