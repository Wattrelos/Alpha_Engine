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
 * CreateAddressAction — GET mostra formulário vazio, POST valida e persiste novo endereço.
 */
class CreateAddressAction implements ActionInterface
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

        $routeContext = RouteContext::fromRequest($request);
        $routeParser  = $routeContext->getRouteParser();
        $lang         = $request->getAttribute('lang', 'pt-br');

        // ── Breadcrumbs ───────────────────────────────────────────────────
        $breadcrumbs = [
            ['text' => 'Início',          'href' => $routeParser->urlFor('home',             ['lang' => $lang])],
            ['text' => 'Minha Conta',     'href' => $routeParser->urlFor('account.index',    ['lang' => $lang])],
            ['text' => 'Meus Endereços',  'href' => $routeParser->urlFor('account.addresses',['lang' => $lang])],
            ['text' => 'Novo Endereço',   'href' => $routeParser->urlFor('account.address.create', ['lang' => $lang])],
        ];

        $actionUrl = $routeParser->urlFor('account.address.create', ['lang' => $lang]);
        $backUrl   = $routeParser->urlFor('account.addresses', ['lang' => $lang]);

        // ── POST: validar e salvar ────────────────────────────────────────
        if ($request->getMethod() === 'POST') {
            $body = $request->getParsedBody() ?? [];

            // Validação básica
            $errors = $this->validate($body);

            if (!empty($errors)) {
                // Repopula o formulário com os dados enviados + erros
                $html = $this->twig->render('pages/users/addresses/create.twig', array_merge(
                    $this->formData($body, $lang),
                    [
                        'breadcrumbs'   => $breadcrumbs,
                        'action'        => $actionUrl,
                        'back'          => $backUrl,
                        'heading_title' => 'Novo Endereço',
                        'error_warning' => 'Por favor, corrija os campos destacados.',
                    ],
                    $errors
                ));
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
            }

            $saveData = array_merge($body, [
                'country_id' => (int)($body['country_id'] ?? 76),
                'default'    => !empty($body['default']),
            ]);

            $this->addressRepository->save($saveData, $customerId);

            return $response
                ->withHeader('Location', $backUrl)
                ->withStatus(302);
        }

        // ── GET: exibe formulário vazio ───────────────────────────────────
        $data = [
            'breadcrumbs'   => $breadcrumbs,
            'heading_title' => 'Novo Endereço',
            'action'        => $actionUrl,
            'back'          => $backUrl,

            // Endereço vazio
            'postcode'      => '',
            'street'     => '',
            'number'        => '',
            'complement'     => '',
            'neighborhood'  => '',
            'city'          => '',
            'zone_id'       => '',
            'country_id'    => 76, // Padrão Brasil
            'default'       => false,

            // Labels
            'entry_postcode'  => 'CEP',
            'entry_street' => 'Logradouro',
            'entry_complement' => 'Complemento',
            'entry_city'      => 'Cidade',
            'button_continue' => 'Adicionar Endereço',
        ];

        $html = $this->twig->render('pages/users/addresses/create.twig', $data);
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

    private function formData(array $body, string $lang): array
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
            'button_continue' => 'Adicionar Endereço',
        ];
    }
}
