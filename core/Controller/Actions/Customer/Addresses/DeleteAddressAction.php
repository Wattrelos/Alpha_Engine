<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Addresses;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

/**
 * DeleteAddressAction — Exclui um endereço do cliente logado.
 *
 * Regras de negócio protegidas aqui:
 *  - Não é possível excluir o único endereço cadastrado.
 *  - Não é possível excluir o endereço padrão.
 *  - Não é possível excluir endereço de outro cliente.
 */
class DeleteAddressAction implements ActionInterface
{
    public function __construct(
        private readonly AddressRepository $addressRepository,
        private readonly ContainerInterface $container,
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser  = $routeContext->getRouteParser();
        $lang         = $request->getAttribute('lang', 'pt-br');

        $backUrl = $routeParser->urlFor('account.addresses', ['lang' => $lang]);

        // ── Segurança extra (SessionMiddleware já protege a rota) ─────────
        $customer = $this->container->get('customer');
        if (!$customer || !$customer->isLogged()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $customerId = (int) $customer->getId();
        $addressId  = (int) ($args['address_id'] ?? 0);

        // ── Validações de negócio via AddressRepository ───────────────────
        $errors = $this->addressRepository->validateDelete($customerId, $addressId);

        if (!empty($errors)) {
            // validateDelete já cobre: endereço inexistente, não pertence ao
            // cliente, único endereço cadastrado e endereço padrão do cliente.
            // Redireciona silenciosamente — a página de lista exibirá o estado atual.
            return $response
                ->withHeader('Location', $backUrl)
                ->withStatus(302);
        }

        // ── Exclui ────────────────────────────────────────────────────────
        $this->addressRepository->delete($addressId, $customerId);

        return $response
            ->withHeader('Location', $backUrl)
            ->withStatus(302);
    }
}
