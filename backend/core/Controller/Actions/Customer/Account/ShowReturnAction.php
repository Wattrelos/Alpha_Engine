<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\OrderReturnRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

final class ShowReturnAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private OrderReturnRepository $orderReturnRepository;
    private \Alpha\Support\Customer $customer;
    private \Alpha\Support\Language $translator;

    public function __construct(
        TwigEnvironment $twig,
        OrderReturnRepository $orderReturnRepository,
        \Alpha\Support\Customer $customer,
        \Alpha\Support\Language $translator
    ) {
        $this->twig = $twig;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->customer = $customer;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $lang = $request->getAttribute('lang', 'pt-br');
        $customerId = $this->customer->isLogged() ? (int)$this->customer->getId() : 0;
        if (!$customerId) {
            return $response
                ->withHeader('Location', '/' . $lang . '/login')
                ->withStatus(302);
        }

        $this->translator->load('account/returns');
        $this->twig->addGlobal('Returns', $this->translator->getNestedData('account/returns'));

        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $returnId = (int)($args['id'] ?? 0);
        $returnInfo = $this->orderReturnRepository->getReturn($returnId);

        if (!$returnInfo) {
            $html = $this->twig->render('pages/errors/404.html.twig', [
                'title'       => 'Devolução Não Encontrada | meusite',
                'description' => 'A devolução solicitada não existe ou não pertence a esta conta.',
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html; charset=utf-8');
        }

        // Busca histórico da devolução
        $historiesData = $this->orderReturnRepository->getHistories($returnId, 0, 20);
        $histories = [];
        foreach ($historiesData as $history) {
            $histories[] = [
                'date_added' => date('d/m/Y H:i:s', strtotime($history['date_added'])),
                'comment'    => nl2br(htmlspecialchars($history['comment'])),
                'status'     => $history['status']
            ];
        }

        $breadcrumbs = [
            ['text' => $this->translator->get('text_home', 'account/returns'), 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => $this->translator->get('text_account', 'account/returns'), 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => $this->translator->get('heading_title', 'account/returns'), 'href' => $routeParser->urlFor('account.returns', ['lang' => $lang])],
            ['text' => 'Detalhes da Devolução #' . $returnId, 'href' => $routeParser->urlFor('account.returns.show', ['lang' => $lang, 'id' => (string)$returnId])]
        ];

        $viewData = [
            'breadcrumbs' => $breadcrumbs,
            'return'      => [
                'id'           => $returnInfo['id'],
                'order_id'     => $returnInfo['order_id'],
                'date_ordered' => date('d/m/Y', strtotime($returnInfo['date_ordered'])),
                'date_added'   => date('d/m/Y H:i:s', strtotime($returnInfo['date_added'])),
                'firstname'    => $returnInfo['firstname'],
                'lastname'     => $returnInfo['lastname'],
                'email'        => $returnInfo['email'],
                'telephone'        => $returnInfo['telephone'],
                'product'      => $returnInfo['product'],
                'model'        => $returnInfo['model'],
                'quantity'     => $returnInfo['quantity'],
                'opened'       => $returnInfo['opened'] ? $this->translator->get('text_opened', 'account/returns') : $this->translator->get('text_unopened', 'account/returns'),
                'reason'       => $returnInfo['reason'],
                'action'       => $returnInfo['action'] ?: $this->translator->get('text_tbc', 'account/returns'),
                'status'       => $returnInfo['status'],
                'comment'      => nl2br(htmlspecialchars($returnInfo['comment']))
            ],
            'histories'   => $histories,
            'back'        => $routeParser->urlFor('account.returns', ['lang' => $lang])
        ];

        $html = $this->twig->render('pages/users/return-info.html.twig', $viewData);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
