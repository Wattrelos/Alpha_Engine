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

/**
 * EditAddressAction — GET mostra formulário pré-preenchido, POST valida e persiste.
 */
class EditAddressAction implements ActionInterface
{
    public function __construct(
        private readonly TwigEnvironment   $twig,
        private readonly CustomerAddressesRepository $addressRepository,
        private readonly ContainerInterface $container,
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');

        // Segurança extra (SessionMiddleware já protege a rota)
        if (!$customer || !$customer->isLogged()) {
            $lang = $request->getAttribute('lang', 'pt-br');
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $customerId = (int) $customer->getId();
        $addressId  = (int) ($args['address_id'] ?? 0);

        $routeContext = RouteContext::fromRequest($request);
        $routeParser  = $routeContext->getRouteParser();
        $lang         = $request->getAttribute('lang', 'pt-br');

        // ── Verifica pertencimento ────────────────────────────────────────
        $address = $this->addressRepository->find($addressId);

        if (!$address || $address->getCustomerId() !== $customerId) {
            return $response
                ->withHeader('Location', $routeParser->urlFor('account.addresses', ['lang' => $lang]))
                ->withStatus(302);
        }

        // ── Breadcrumbs ───────────────────────────────────────────────────
        $breadcrumbs = [
            ['text' => 'Início',          'href' => $routeParser->urlFor('home',             ['lang' => $lang])],
            ['text' => 'Minha Conta',     'href' => $routeParser->urlFor('account.index',    ['lang' => $lang])],
            ['text' => 'Meus Endereços',  'href' => $routeParser->urlFor('account.addresses',['lang' => $lang])],
            ['text' => 'Editar Endereço', 'href' => $routeParser->urlFor('account.address.edit', ['lang' => $lang, 'address_id' => (string)$addressId])],
        ];

        $actionUrl = $routeParser->urlFor('account.address.edit', ['lang' => $lang, 'address_id' => (string)$addressId]);
        $backUrl   = $routeParser->urlFor('account.addresses', ['lang' => $lang]);

        // ── POST: validar e salvar ────────────────────────────────────────
        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody() ?? [];

            // Validação básica
            $errors = $this->validate($body);

            if (!empty($errors)) {
                // Repopula o formulário com os dados enviados + erros
                $html = $this->twig->render('pages/users/addresses/edit.twig', array_merge(
                    $this->formData($body, $lang, $addressId),
                    [
                        'breadcrumbs'   => $breadcrumbs,
                        'action'        => $actionUrl,
                        'back'          => $backUrl,
                        'heading_title' => 'Editar Endereço',
                        'error_warning' => 'Por favor, corrija os campos destacados.',
                    ],
                    $errors
                ));
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
            }

            $saveData = array_merge($body, [
                'address_id' => $addressId,
                'country_id' => (int)($body['country_id'] ?? 76),
                'default'    => !empty($body['default']),
            ]);

            try {
                $this->addressRepository->save($saveData, $customerId);
            } catch (\RuntimeException $e) {
                // Segurança: tentativa de editar endereço de outro cliente
                return $response
                    ->withHeader('Location', $backUrl)
                    ->withStatus(302);
            }

            return $response
                ->withHeader('Location', $backUrl)
                ->withStatus(302);
        }

        // ── GET: exibe formulário pré-preenchido ──────────────────────────
        $zoneCode = $this->resolveZoneCode($address->getZone() ? $address->getZone()->getId() : 0);

        // Verifica se é o endereço padrão
        $isDefault = (int)$customer->getAddressId() === (int)$address->getId();

        $data = [
            'breadcrumbs'   => $breadcrumbs,
            'heading_title' => 'Editar Endereço',
            'action'        => $actionUrl,
            'back'          => $backUrl,

            // Endereço
            'postcode'      => $address->getPostalCode(),
            'street'     => $address->getStreet(),
            'number'        => $address->getNumber() ?: '',
            'complement'     => $address->getComplement(),
            'neighborhood'  => $address->getDistrict(),
            'city'          => $address->getCity() ? $address->getCity()->getName() : '',
            'zone_id'       => $zoneCode,          // campo de texto exibe código UF (ex: SP)
            'country_id'    => $address->getCountry() ? $address->getCountry()->getId() : 76,
            'default'       => $isDefault,

            // Labels
            'entry_postcode'  => 'CEP',
            'entry_street' => 'Logradouro',
            'entry_complement' => 'Complemento',
            'entry_city'      => 'Cidade',
            'button_continue' => 'Atualizar Endereço',
        ];

        $html = $this->twig->render('pages/users/addresses/edit.twig', $data);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function validate(array $body): array
    {
        $errors = [];

        if (strlen(trim($body['postcode'] ?? '')) < 8) {
            $errors['error_postcode'] = 'Informe um CEP válido.';
        }
        if (empty(trim($body['street'] ?? ''))) {
            $errors['error_street'] = 'O logradouro é obrigatório.';
        }
        if (empty(trim($body['city'] ?? ''))) {
            $errors['error_city'] = 'A cidade é obrigatória.';
        }

        return $errors;
    }

    private function formData(array $body, string $lang, int $addressId): array
    {
        return [
            'postcode'     => $body['postcode']     ?? '',
            'street'    => $body['street']    ?? '',
            'number'       => $body['number']       ?? '',
            'complement'    => $body['complement']    ?? '',
            'neighborhood' => $body['neighborhood'] ?? '',
            'city'         => $body['city']         ?? '',
            'zone_id'      => $body['zone_id']      ?? '',
            'country_id'   => (int)($body['country_id'] ?? 76),
            'default'      => !empty($body['default']),

            'entry_postcode'  => 'CEP',
            'entry_street' => 'Logradouro',
            'entry_complement' => 'Complemento',
            'entry_city'      => 'Cidade',
            'button_continue' => 'Atualizar Endereço',
        ];
    }

    /**
     * Resolve o código UF (ex: "SP") a partir do ID numérico da zona para exibição no template.
     */
    private function resolveZoneCode(int $zoneId): string
    {
        if ($zoneId <= 0) {
            return '';
        }
        /** @var \Alpha\Mappers\EntityMappers\GeoZoneMapper $zoneMapper */
        $zoneMapper = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(\Alpha\Mappers\EntityMappers\GeoZoneMapper::class);
        $zone = $zoneMapper->findById($zoneId);
        if ($zone) {
            $isoCode = $zone->getIsoCode();
            return str_contains($isoCode, '-') ? explode('-', $isoCode)[1] : $isoCode;
        }
        return '';
    }
}
