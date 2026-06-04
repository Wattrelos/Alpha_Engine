<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Addresses;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\AddressRepository;
use Alpha\Model\Domain\Repositories\ZoneRepository;
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
        private readonly AddressRepository $addressRepository,
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

            // Resolve zone_id numérico a partir do código UF enviado
            $zoneId = $this->resolveZoneId($body['zone_id'] ?? '');

            $saveData = array_merge($body, [
                'address_id' => $addressId,
                'zone_id'    => $zoneId,
                'country_id' => (int)($body['country_id'] ?? 30),
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
        // Resolve código UF a partir do zone_id numérico para o campo readonly do template
        $zoneCode = $this->resolveZoneCode($address->getZoneId());

        $data = [
            'breadcrumbs'   => $breadcrumbs,
            'heading_title' => 'Editar Endereço',
            'action'        => $actionUrl,
            'back'          => $backUrl,

            // Dados pessoais
            'firstname'     => $address->getFirstname(),
            'lastname'      => $address->getLastname(),
            'company'       => $address->getCompany(),

            // Endereço
            'postcode'      => $address->getPostcode(),
            'address_1'     => $address->getAddress1(),
            'number'        => $address->getNumber() ?: '',
            'address_2'     => $address->getAddress2(),
            'neighborhood'  => $address->getNeighborhood(),
            'city'          => $address->getCity(),
            'zone_id'       => $zoneCode,          // campo de texto exibe código UF
            'country_id'    => $address->getCountryId(),
            'default'       => $address->isDefault(),

            // Labels (fallbacks para quando não há arquivo de linguagem carregado)
            'entry_firstname' => 'Nome',
            'entry_lastname'  => 'Sobrenome',
            'entry_company'   => 'Empresa',
            'entry_postcode'  => 'CEP',
            'entry_address_1' => 'Logradouro',
            'entry_address_2' => 'Complemento',
            'entry_city'      => 'Cidade',
            'button_continue' => 'Atualizar Endereço',
        ];

        $html = $this->twig->render('pages/users/addresses/edit.twig', $data);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Validação leve dos campos obrigatórios do formulário de endereço.
     * Retorna array vazio se tudo OK, ou array com chaves error_* se houver falha.
     */
    private function validate(array $body): array
    {
        $errors = [];

        if (empty(trim($body['firstname'] ?? ''))) {
            $errors['error_firstname'] = 'O nome é obrigatório.';
        }
        if (empty(trim($body['lastname'] ?? ''))) {
            $errors['error_lastname'] = 'O sobrenome é obrigatório.';
        }
        if (strlen(trim($body['postcode'] ?? '')) < 8) {
            $errors['error_postcode'] = 'Informe um CEP válido.';
        }
        if (empty(trim($body['address_1'] ?? ''))) {
            $errors['error_address_1'] = 'O logradouro é obrigatório.';
        }
        if (empty(trim($body['city'] ?? ''))) {
            $errors['error_city'] = 'A cidade é obrigatória.';
        }

        return $errors;
    }

    /**
     * Monta array de dados para repopular o formulário após erro de validação.
     */
    private function formData(array $body, string $lang, int $addressId): array
    {
        return [
            'firstname'    => $body['firstname']    ?? '',
            'lastname'     => $body['lastname']     ?? '',
            'company'      => $body['company']      ?? '',
            'postcode'     => $body['postcode']     ?? '',
            'address_1'    => $body['address_1']    ?? '',
            'number'       => $body['number']       ?? '',
            'address_2'    => $body['address_2']    ?? '',
            'neighborhood' => $body['neighborhood'] ?? '',
            'city'         => $body['city']         ?? '',
            'zone_id'      => $body['zone_id']      ?? '',
            'country_id'   => (int)($body['country_id'] ?? 30),
            'default'      => !empty($body['default']),

            'entry_firstname' => 'Nome',
            'entry_lastname'  => 'Sobrenome',
            'entry_company'   => 'Empresa',
            'entry_postcode'  => 'CEP',
            'entry_address_1' => 'Logradouro',
            'entry_address_2' => 'Complemento',
            'entry_city'      => 'Cidade',
            'button_continue' => 'Atualizar Endereço',
        ];
    }

    /**
     * Resolve o ID numérico da zona (estado) a partir do código UF (ex: "SP" → 711).
     * Retorna 0 se não encontrado.
     */
    private function resolveZoneId(string $code): int
    {
        if (empty($code)) {
            return 0;
        }
        /** @var ZoneRepository $zoneRepository */
        $zoneRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(ZoneRepository::class);
        $zone = $zoneRepository->findOneBy(['code' => strtoupper(trim($code))]);
        return $zone ? (int)$zone->getId() : 0;
    }

    /**
     * Resolve o código UF (ex: "SP") a partir do ID numérico da zona para exibição no template.
     * Retorna string vazia se não encontrado.
     */
    private function resolveZoneCode(int $zoneId): string
    {
        if ($zoneId <= 0) {
            return '';
        }
        /** @var ZoneRepository $zoneRepository */
        $zoneRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(ZoneRepository::class);
        $zone = $zoneRepository->find($zoneId);
        // Zone entity deve ter getCode() — confirmado no SubmitCheckoutAction existente
        return $zone && method_exists($zone, 'getCode') ? $zone->getCode() : '';
    }
}
