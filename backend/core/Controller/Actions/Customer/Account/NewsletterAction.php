<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Support\Language as Translator;
use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;

/**
 * NewsletterAction - Gerencia a inscrição/cancelamento da newsletter do cliente.
 * 
 * Suporta requisições GET (exibição do formulário dedicado) e POST (atualização via formulário ou AJAX).
 */
class NewsletterAction implements ActionInterface
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly ContainerInterface $container,
        private readonly TwigEnvironment $twig,
        private readonly Translator $translator
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customerHelper = $this->container->get('customer');
        $lang = $request->getAttribute('lang', 'pt-br');

        $isAjax = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest'
               || str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json');

        if (!$customerHelper || !$customerHelper->isLogged()) {
            if ($isAjax) {
                $response->getBody()->write((string)json_encode(['success' => false, 'error' => 'Acesso não autorizado. Por favor, faça login.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $customerId = (int)$customerHelper->getId();
        $customer = $this->customerRepository->find($customerId);

        if (!$customer) {
            if ($isAjax) {
                $response->getBody()->write((string)json_encode(['success' => false, 'error' => 'Cliente não encontrado.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            return $response
                ->withHeader('Location', '/' . $lang . '/logout')
                ->withStatus(302);
        }

        $method = strtoupper($request->getMethod());

        if ($method === 'POST') {
            $parsedBody = $request->getParsedBody();
            $data = is_array($parsedBody) ? $parsedBody : [];
            $newsletterStatus = isset($data['newsletter']) && ((string)$data['newsletter'] === '1' || $data['newsletter'] === true);

            $customer->setNewsletter($newsletterStatus);
            $this->customerRepository->updateProfile($customer);

            if ($isAjax) {
                $response->getBody()->write((string)json_encode(['success' => true]));
                return $response->withHeader('Content-Type', 'application/json');
            }

            $session = $this->container->has('session') ? $this->container->get('session') : null;
            if ($session) {
                $this->translator->load('account/newsletter');
                $session->data['success'] = $this->translator->get('text_success', 'account/newsletter') ?: 'A sua assinatura em nosso informativo foi modificada.';
            }

            return $response
                ->withHeader('Location', '/' . $lang . '/account')
                ->withStatus(302);
        }

        // GET: Exibe a página dedicada do informativo
        $this->translator->load('account/newsletter');

        $routeParser = null;
        try {
            $routeContext = RouteContext::fromRequest($request);
            $routeParser = $routeContext->getRouteParser();
        } catch (\Throwable) {
            $routeParser = null;
        }

        $accountIndexUrl = $routeParser ? $routeParser->urlFor('account.index', ['lang' => $lang]) : '/' . $lang . '/account';
        $accountNewsletterUrl = $routeParser ? $routeParser->urlFor('account.newsletter', ['lang' => $lang]) : '/' . $lang . '/account/newsletter';

        $breadcrumbs = [
            [
                'text' => $this->translator->get('text_account', 'account/newsletter') ?: 'Minha conta',
                'href' => $accountIndexUrl,
            ],
            [
                'text' => $this->translator->get('text_newsletter', 'account/newsletter') ?: 'Informativo',
                'href' => $accountNewsletterUrl,
            ],
        ];

        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $success = '';
        $errorWarning = '';
        if ($session) {
            if (isset($session->data['success'])) {
                $success = $session->data['success'];
                unset($session->data['success']);
            }
            if (isset($session->data['error_warning'])) {
                $errorWarning = $session->data['error_warning'];
                unset($session->data['error_warning']);
            }
        }

        $html = $this->twig->render('pages/users/accounts/newsletter.twig', [
            'title'             => ($this->translator->get('heading_title', 'account/newsletter') ?: 'Informativo') . ' | meusite',
            'heading_title'     => $this->translator->get('heading_title', 'account/newsletter') ?: 'Informativo',
            'text_newsletter'   => $this->translator->get('text_newsletter', 'account/newsletter') ?: 'Informativo',
            'entry_newsletter'  => $this->translator->get('entry_newsletter', 'account/newsletter') ?: 'Deseja receber novidades por e-mail?',
            'newsletter_status' => $customer->isNewsletter(),
            'breadcrumbs'       => $breadcrumbs,
            'action'            => $accountNewsletterUrl,
            'back'              => $accountIndexUrl,
            'success'           => $success,
            'error_warning'     => $errorWarning,
            'lang'              => $lang,
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
