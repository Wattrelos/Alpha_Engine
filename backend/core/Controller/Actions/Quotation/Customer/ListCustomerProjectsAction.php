<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Customer;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBidRepository;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * ListCustomerProjectsAction - Lista os projetos/RFQs solicitados pelo cliente.
 */
class ListCustomerProjectsAction implements ActionInterface
{
    public function __construct(
        private TwigEnvironment $twig,
        private ProjectRfqRepository $rfqRepository,
        private ProjectBidRepository $bidRepository,
        private ProjectBoqRepository $boqRepository,
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

        $customerId = (int) $customer->getId();
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $rfqs = $this->rfqRepository->findByCustomerId($customerId);
        $projectsData = [];

        foreach ($rfqs as $rfq) {
            $bids = $this->bidRepository->findByRfqId($rfq->getId());
            $boq = $this->boqRepository->findByRfqId($rfq->getId());

            $projectsData[] = [
                'id' => $rfq->getId(),
                'title' => $rfq->getTitle(),
                'category' => $rfq->getCategory(),
                'city' => $rfq->getAddressCity(),
                'state' => $rfq->getAddressState(),
                'status' => $rfq->getStatus(),
                'date_added' => $rfq->getDateAdded(),
                'total_bids' => count($bids),
                'has_boq' => $boq !== null,
                'boq_id' => $boq ? $boq->getId() : null,
                'boq_status' => $boq ? $boq->getStatus() : null,
                'boq_total' => $boq ? $boq->getTotalEstimatedAmount() : 0.0,
                'bids_url' => '/' . $lang . '/account/projetos/' . $rfq->getId() . '/propostas',
                'boq_url' => $boq ? '/' . $lang . '/account/projetos/' . $rfq->getId() . '/boq' : null
            ];
        }

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Meus Projetos & Orçamentos', 'href' => '/' . $lang . '/account/projetos']
        ];

        $html = $this->twig->render('pages/quotation/customer-projects.twig', [
            'breadcrumbs' => $breadcrumbs,
            'projects' => $projectsData,
            'lang' => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
