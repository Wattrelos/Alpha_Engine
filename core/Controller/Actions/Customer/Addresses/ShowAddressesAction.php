<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Addresses;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerAddressesRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

class ShowAddressesAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private CustomerAddressesRepository $addressRepository;
    private ContainerInterface $container;

    public function __construct(
        TwigEnvironment $twig,
        CustomerAddressesRepository $addressRepository,
        ContainerInterface $container
    ) {
        $this->twig              = $twig;
        $this->addressRepository = $addressRepository;
        $this->container         = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');

        // Segurança: redireciona se não logado (SessionMiddleware já protege,
        // mas garantimos aqui também)
        if (!$customer || !$customer->isLogged()) {
            $lang = $request->getAttribute('lang', 'pt-br');
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $customerId = (int) $customer->getId();

        $routeContext = RouteContext::fromRequest($request);
        $routeParser  = $routeContext->getRouteParser();
        $lang         = $request->getAttribute('lang', 'pt-br');

        // ── Breadcrumbs ──────────────────────────────────────────────────
        $breadcrumbs = [
            ['text' => 'Início',      'href' => $routeParser->urlFor('home',          ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Meus Endereços', 'href' => $routeParser->urlFor('account.addresses', ['lang' => $lang])],
        ];

        // ── Busca endereços do cliente ────────────────────────────────────
        $rawAddresses = $this->addressRepository->getAddresses($customerId);

        $addresses = array_map(function (array $addr) use ($routeParser, $lang): array {
            return array_merge($addr, [
                'edit'   => $routeParser->urlFor('account.address.edit',   ['lang' => $lang, 'address_id' => (string)$addr['address_id']]),
                'delete' => $routeParser->urlFor('account.address.delete', ['lang' => $lang, 'address_id' => (string)$addr['address_id']]),
            ]);
        }, $rawAddresses);

        // ── Dados do template ─────────────────────────────────────────────
        $data = [
            'breadcrumbs'   => $breadcrumbs,
            'heading_title' => 'Meus Endereços',
            'addresses'     => $addresses,
            'add'           => $routeParser->urlFor('account.address.create', ['lang' => $lang]),
            'continue'      => $routeParser->urlFor('account.index',          ['lang' => $lang]),
            'text_no_results' => 'Você ainda não possui endereços cadastrados.',
        ];

        $html = $this->twig->render('pages/users/addresses/index.twig', $data);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
