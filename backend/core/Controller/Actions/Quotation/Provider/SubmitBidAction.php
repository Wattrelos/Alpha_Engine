<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Provider;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Entities\Quotation\ProjectBid;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBidRepository;
use Alpha\Model\Domain\Repositories\ServiceProviderProfileRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * SubmitBidAction - Envio de proposta comercial pelo prestador de serviço (RF035).
 */
class SubmitBidAction implements ActionInterface
{
    public function __construct(
        private TwigEnvironment $twig,
        private ProjectRfqRepository $rfqRepository,
        private ProjectBidRepository $bidRepository,
        private ServiceProviderProfileRepository $providerRepo,
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

        $customerId = (int)$customer->getId();
        $provider = $this->providerRepo->findByCustomerId($customerId);

        if (!$provider) {
            return $response
                ->withHeader('Location', '/' . $lang . '/prestador/oportunidades')
                ->withStatus(302);
        }

        $rfqId = (int)($args['rfq_id'] ?? 0);
        $rfq = $this->rfqRepository->find($rfqId);

        if (!$rfq) {
            return $response
                ->withHeader('Location', '/' . $lang . '/prestador/oportunidades')
                ->withStatus(302);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $existingBid = $this->bidRepository->findByRfqAndProvider($rfqId, $provider->getId());
        $error = null;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $laborPrice = (float)($data['labor_price'] ?? 0.0);
            $estimatedDays = (int)($data['estimated_duration_days'] ?? 1);
            $notes = trim($data['proposal_notes'] ?? '');

            if ($rfq->getStatus() !== 'open') {
                $error = 'Este projeto não está aberto para propostas (Status: ' . $rfq->getStatus() . ').';
            } elseif ($existingBid === null && $this->bidRepository->countByRfqId($rfqId) >= 10) {
                $error = 'Esta solicitação de orçamento já atingiu o limite máximo de 10 propostas concorrentes (RN-BID-01).';
            } elseif ($laborPrice <= 0 || $estimatedDays <= 0) {
                $error = 'Por favor, informe um valor de mão de obra válido e o prazo estimado em dias.';
            } else {
                $bid = $existingBid ?? new ProjectBid();
                $bid->setRfqId($rfqId)
                    ->setProviderId($provider->getId())
                    ->setLaborPrice($laborPrice)
                    ->setEstimatedDurationDays($estimatedDays)
                    ->setProposalNotes($notes)
                    ->setStatus('submitted');

                $this->bidRepository->save($bid);

                return $response
                    ->withHeader('Location', '/' . $lang . '/prestador/oportunidades?bid_saved=1')
                    ->withStatus(302);
            }
        }

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Painel do Prestador', 'href' => '/' . $lang . '/prestador/oportunidades'],
            ['text' => 'Enviar Orçamento', 'href' => '/' . $lang . '/prestador/projetos/' . $rfqId . '/proposta']
        ];

        $html = $this->twig->render('pages/quotation/submit-bid-form.twig', [
            'breadcrumbs' => $breadcrumbs,
            'rfq' => [
                'id' => $rfq->getId(),
                'title' => $rfq->getTitle(),
                'category' => $rfq->getCategory(),
                'description' => $rfq->getDescription(),
                'city' => $rfq->getAddressCity(),
                'state' => $rfq->getAddressState(),
                'budget_expectation' => $rfq->getBudgetExpectation(),
                'deadline_days' => $rfq->getDesiredDeadlineDays()
            ],
            'bid' => $existingBid ? [
                'labor_price' => $existingBid->getLaborPrice(),
                'estimated_duration_days' => $existingBid->getEstimatedDurationDays(),
                'proposal_notes' => $existingBid->getProposalNotes()
            ] : null,
            'error' => $error,
            'lang' => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
