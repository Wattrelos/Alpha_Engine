<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Model\Domain\Repositories\LanguageRepository;
use Alpha\Model\Domain\Repositories\SettingRepository;
use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;

/**
 * UpdateAction - Exibe e processa a edição de dados da conta do cliente.
 */
class UpdateAction implements ActionInterface
{
    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly ContainerInterface $container,
        private readonly CustomerRepository $customerRepository,
        private readonly SettingRepository $settingRepository,
        private readonly LanguageRepository $languageRepository
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customerHelper = $this->container->get('customer');
        $lang = $request->getAttribute('lang', 'pt-br');

        // Proteção de Rota
        if (!$customerHelper || !$customerHelper->isLogged()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $customerId = $customerHelper->getId();
        $customer = $this->customerRepository->find($customerId);

        if (!$customer) {
            return $response
                ->withHeader('Location', '/' . $lang . '/logout')
                ->withStatus(302);
        }

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        // Configurações e Idioma
        $configSettings = $this->settingRepository->getSetting('config', 0);
        $languageCode = $configSettings['config_language_catalog'] ?? 'pt-br';
        $language = $this->languageRepository->getByCode($languageCode);
        if (!$language) {
            $language = $this->languageRepository->find(2); // Fallback pt-br
        }
        $languageCodeStr = $language ? $language->getCode() : 'pt-br';

        // Carrega traduções
        $languageData = $this->loadLanguageData('account/edit', $languageCodeStr);

        $errors = [];
        $success = null;

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $data = is_array($parsedBody) ? $parsedBody : [];

            // Valida os dados usando as regras de negócio do repositório de domínio
            $errors = $this->customerRepository->validateEditData($data, $customerId);

            if (empty($errors)) {
                // Atualiza a entidade do cliente
                $customer->setFirstname(trim($data['firstname'] ?? ''));
                $customer->setLastname(trim($data['lastname'] ?? ''));
                $customer->setEmail(trim($data['email'] ?? ''));
                $customer->setTelephone(trim($data['telephone'] ?? ''));
                
                if (isset($data['cpf_cnpj'])) {
                    $customer->setCpfCnpj(trim($data['cpf_cnpj']));
                }
                if (isset($data['persontype'])) {
                    $customer->setPersontype(trim($data['persontype']));
                }

                // Salva no banco de dados
                $this->customerRepository->updateProfile($customer);

                // Atualiza a sessão
                $_SESSION['logged_user'] = json_encode([
                    'id'                => $customer->getId(),
                    'name'              => trim($customer->getFirstname() . ' ' . $customer->getLastname()),
                    'email'             => $customer->getEmail(),
                    'telephone'         => $customer->getTelephone(),
                    'customer_group_id' => $customer->getCustomerGroupId(),
                    'role'              => 'client_premium'
                ]);

                $_SESSION['customer_firstname'] = $customer->getFirstname();
                $_SESSION['customer_lastname']  = $customer->getLastname();
                $_SESSION['customer_email']     = $customer->getEmail();
                $_SESSION['customer_telephone'] = $customer->getTelephone();

                // Define mensagem de sucesso na sessão e redireciona para a home da conta
                $session = $this->container->has('session') ? $this->container->get('session') : null;
                if ($session) {
                    $session->data['success'] = $languageData['text_success'] ?? 'Sucesso: Seus dados foram atualizados com sucesso.';
                }

                return $response
                    ->withHeader('Location', $routeParser->urlFor('account.index', ['lang' => $lang]))
                    ->withStatus(302);
            }
        }

        // Breadcrumbs
        $breadcrumbs = [
            ['text' => 'Início', 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => 'Minha Conta', 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => 'Editar Informações', 'href' => $routeParser->urlFor('account.edit', ['lang' => $lang])]
        ];

        // Se for GET ou houver erros, renderiza o formulário com dados atuais
        $formData = [
            'firstname' => $customer->getFirstname(),
            'lastname'  => $customer->getLastname(),
            'email'     => $customer->getEmail(),
            'telephone' => $customer->getTelephone(),
            'cpf_cnpj'  => $customer->getCpfCnpj(),
            'persontype'=> $customer->getPersontype()
        ];

        $viewData = array_merge([
            'direction'   => 'ltr',
            'lang'        => $languageCodeStr,
            'title'       => ($languageData['heading_title'] ?? 'Editar Informações') . ' | AgSonhos',
            'description' => 'Edite suas informações cadastrais.',
            'breadcrumbs' => $breadcrumbs,
            'action'      => $routeParser->urlFor('account.edit', ['lang' => $lang]),
            'back'        => $routeParser->urlFor('account.index', ['lang' => $lang]),
            'formData'    => $formData,
            'errors'      => $errors,
            'config_telephone_required' => $configSettings['config_telephone_required'] ?? false
        ], $languageData);

        $html = $this->twig->render('pages/users/accounts/update-account.html.twig', $viewData);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function loadLanguageData(string $route, string $languageCode): array
    {
        $data = [];
        $file = '/var/www/html/agsonhos/core/language_legacy/' . $languageCode . '/' . $route . '.php';
        
        if (is_file($file)) {
            $_ = [];
            include $file;
            foreach ($_ as $key => $value) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
