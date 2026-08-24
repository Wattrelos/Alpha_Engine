<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBidRepository;
use Alpha\Model\Domain\Repositories\ServiceProviderProfileRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * ShowBidComparisonAction - Painel analítico de comparação de propostas comerciais (Bid Comparison - RF035).
 */
class ShowBidComparisonAction implements ActionInterface
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

        $rfqId = (int)($args['rfq_id'] ?? 0);
        $rfq = $this->rfqRepository->find($rfqId);

        if (!$rfq || $rfq->getCustomerId() !== (int)$customer->getId()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/account/projetos')
                ->withStatus(302);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $bids = $this->bidRepository->findByRfqId($rfqId);
        $bidsData = [];

        foreach ($bids as $bid) {
            $provider = $this->providerRepo->find($bid->getProviderId());
            $bidsData[] = [
                'id' => $bid->getId(),
                'provider_id' => $bid->getProviderId(),
                'provider_name' => $provider ? ($provider->getTradeName() ?: $provider->getCompanyName() ?: 'Prestador Profissional') : 'Prestador Profissional',
                'provider_specialties' => $provider ? $provider->getSpecialties() : 'Construção e Reformas',
                'provider_rating' => $provider ? $provider->getRating() : 5.0,
                'provider_reviews' => $provider ? $provider->getTotalReviews() : 0,
                'labor_price' => $bid->getLaborPrice(),
                'estimated_duration_days' => $bid->getEstimatedDurationDays(),
                'proposal_notes' => $bid->getProposalNotes(),
                'status' => $bid->getStatus(),
                'date_added' => $bid->getDateAdded(),
                'accept_url' => '/' . $lang . '/account/projetos/' . $rfqId . '/propostas/' . $bid->getId() . '/aceitar'
            ];
        }

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Meus Projetos', 'href' => '/' . $lang . '/account/projetos'],
            ['text' => 'Comparação de Propostas (Bid Comparison)', 'href' => '/' . $lang . '/account/projetos/' . $rfqId . '/propostas']
        ];

        $html = $this->twig->render('pages/quotation/bid-comparison.twig', [
            'breadcrumbs' => $breadcrumbs,
            'rfq' => [
                'id' => $rfq->getId(),
                'title' => $rfq->getTitle(),
                'category' => $rfq->getCategory(),
                'description' => $rfq->getDescription(),
                'city' => $rfq->getAddressCity(),
                'state' => $rfq->getAddressState(),
                'budget_expectation' => $rfq->getBudgetExpectation(),
                'status' => $rfq->getStatus(),
                'selected_provider_id' => $rfq->getSelectedProviderId()
            ],
            'bids' => $bidsData,
            'lang' => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
