<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Quotation\Provider;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Entities\Quotation\ServiceProviderProfile;
use Alpha\Model\Domain\Repositories\ProjectRfqRepository;
use Alpha\Model\Domain\Repositories\ProjectBidRepository;
use Alpha\Model\Domain\Repositories\ServiceProviderProfileRepository;
use Alpha\Services\Quotation\GeoMatchingService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

/**
 * ListOpportunitiesAction - Feed de oportunidades de serviços no raio geográfico do prestador (RF034).
 */
class ListOpportunitiesAction implements ActionInterface
{
    public function __construct(
        private TwigEnvironment $twig,
        private ProjectRfqRepository $rfqRepository,
        private ProjectBidRepository $bidRepository,
        private ServiceProviderProfileRepository $providerRepo,
        private GeoMatchingService $geoService,
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

        // Se não tiver perfil de prestador cadastrado, cria perfil inicial
        if (!$provider) {
            $provider = new ServiceProviderProfile();
            $provider->setCustomerId($customerId)
                ->setCompanyName('Prestador ' . $customer->getFirstName())
                ->setDocumentNumber($customer->getCpfCnpj() ?: '00000000000')
                ->setSpecialties('Alvenaria, Pintura, Reformas em Geral')
                ->setServiceRadiusKm(30.0)
                ->setStatus(true);

            $id = $this->providerRepo->save($provider);
            $provider->setId((int)$id);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $openProjects = $this->rfqRepository->findOpenProjects();
        $opportunities = [];

        foreach ($openProjects as $project) {
            // Verifica matching de geolocalização e raio
            if ($this->geoService->isProviderEligibleForProject($provider, $project)) {
                $existingBid = $this->bidRepository->findByRfqAndProvider($project->getId(), $provider->getId());

                $distance = null;
                if ($provider->getLatitude() !== null && $provider->getLongitude() !== null &&
                    $project->getLatitude() !== null && $project->getLongitude() !== null) {
                    $distance = $this->geoService->calculateDistance(
                        $provider->getLatitude(),
                        $provider->getLongitude(),
                        $project->getLatitude(),
                        $project->getLongitude()
                    );
                }

                $opportunities[] = [
                    'id' => $project->getId(),
                    'title' => $project->getTitle(),
                    'category' => $project->getCategory(),
                    'description' => $project->getDescription(),
                    'city' => $project->getAddressCity(),
                    'state' => $project->getAddressState(),
                    'cep' => $project->getAddressCep(),
                    'budget_expectation' => $project->getBudgetExpectation(),
                    'deadline_days' => $project->getDesiredDeadlineDays(),
                    'date_added' => $project->getDateAdded(),
                    'distance_km' => $distance,
                    'has_bid' => $existingBid !== null,
                    'my_bid' => $existingBid ? [
                        'id' => $existingBid->getId(),
                        'price' => $existingBid->getLaborPrice(),
                        'duration' => $existingBid->getEstimatedDurationDays(),
                        'status' => $existingBid->getStatus()
                    ] : null,
                    'is_assigned_to_me' => $project->getSelectedProviderId() === $provider->getId(),
                    'bid_url' => '/' . $lang . '/prestador/projetos/' . $project->getId() . '/proposta',
                    'takeoff_url' => '/' . $lang . '/prestador/projetos/' . $project->getId() . '/takeoff'
                ];
            }
        }

        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Painel do Prestador', 'href' => '/' . $lang . '/prestador/oportunidades'],
            ['text' => 'Oportunidades no Raio de Atendimento', 'href' => '/' . $lang . '/prestador/oportunidades']
        ];

        $html = $this->twig->render('pages/quotation/provider-opportunities.twig', [
            'breadcrumbs' => $breadcrumbs,
            'provider' => [
                'id' => $provider->getId(),
                'name' => $provider->getTradeName() ?: $provider->getCompanyName(),
                'radius_km' => $provider->getServiceRadiusKm(),
                'specialties' => $provider->getSpecialties(),
                'rating' => $provider->getRating()
            ],
            'opportunities' => $opportunities,
            'lang' => $lang
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
