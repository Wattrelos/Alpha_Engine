<?php

namespace Alpha\Controller\Actions\Customer\Account;

use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\CustomerRepository;
use Alpha\Support\Language as Translator;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

/**
 * Action responsável por exibir a lista de transações do cliente logado.
 */
class TransactionAction implements ActionInterface
{
    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly CustomerRepository $customerRepository,
        private readonly ContainerInterface $container,
        private readonly Translator $translator
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $customer = $this->container->get('customer');
        $routeContext = RouteContext::fromRequest($request);
        $routeParser  = $routeContext->getRouteParser();
        $lang         = $request->getAttribute('lang', 'pt-br');

        // Proteção de rota
        if (!$customer || !$customer->isLogged()) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $this->translator->load('account/transaction');

        $breadcrumbs = [
            ['text' => $this->translator->get('text_account', 'account/transaction'), 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => $this->translator->get('text_transaction', 'account/transaction'), 'href' => $routeParser->urlFor('account.transaction', ['lang' => $lang])]
        ];

        // Serviços auxiliares
        $config   = $this->container->has('config') ? $this->container->get('config') : null;
        $currency = $this->container->has('currency') ? $this->container->get('currency') : null;
        $session  = $this->container->has('session') ? $this->container->get('session') : null;

        $currencyCode = ($session && isset($session->data['currency'])) ? $session->data['currency'] : ($config ? $config->get('config_currency') : 'BRL');

        // Busca dados das transações
        $transactionsData = $this->customerRepository->getTransactions($customer->getId(), 0, 100);
        $totalSum = $this->customerRepository->getTransactionTotal($customer->getId());

        $transactions = [];
        foreach ($transactionsData as $trans) {
            $transactions[] = [
                'date_added'  => date('d/m/Y', strtotime($trans['date_added'])),
                'description' => $trans['description'],
                'amount'      => $currency ? $currency->format($trans['amount'], $currencyCode) : 'R$ ' . number_format($trans['amount'], 2, ',', '.')
            ];
        }

        $totalFormatted = $currency ? $currency->format($totalSum, $currencyCode) : 'R$ ' . number_format($totalSum, 2, ',', '.');

        $html = $this->twig->render('pages/users/accounts/Transactions.html.twig', [
            'breadcrumbs'        => $breadcrumbs,
            'heading_title'      => $this->translator->get('heading_title', 'account/transaction'),
            'column_date_added'  => $this->translator->get('column_date_added', 'account/transaction'),
            'column_description' => $this->translator->get('column_description', 'account/transaction'),
            'column_amount'      => sprintf($this->translator->get('column_amount', 'account/transaction'), $currencyCode),
            'text_total'         => $this->translator->get('text_total', 'account/transaction'),
            'text_no_results'    => $this->translator->get('text_no_results', 'account/transaction'),
            'transactions'       => $transactions,
            'total'              => $totalFormatted,
            'continue'           => $routeParser->urlFor('account.index', ['lang' => $lang]),
            'lang'               => $lang
        ]);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
