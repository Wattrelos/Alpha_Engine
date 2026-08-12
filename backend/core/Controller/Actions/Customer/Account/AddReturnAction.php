<?php

declare(strict_types=1);

namespace Alpha\Controller\Actions\Customer\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\OrderReturnRepository;
use Alpha\Model\Domain\Repositories\OrderRepository;
use Alpha\Model\Domain\Repositories\ReturnDictionaryRepository;
use Twig\Environment as TwigEnvironment;
use Slim\Routing\RouteContext;

final class AddReturnAction implements ActionInterface
{
    private TwigEnvironment $twig;
    private OrderReturnRepository $orderReturnRepository;
    private OrderRepository $orderRepository;
    private ReturnDictionaryRepository $returnDictionaryRepository;
    private \Alpha\Support\Customer $customer;
    private \Alpha\Support\Language $translator;

    public function __construct(
        TwigEnvironment $twig,
        OrderReturnRepository $orderReturnRepository,
        OrderRepository $orderRepository,
        ReturnDictionaryRepository $returnDictionaryRepository,
        \Alpha\Support\Customer $customer,
        \Alpha\Support\Language $translator
    ) {
        $this->twig = $twig;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->orderRepository = $orderRepository;
        $this->returnDictionaryRepository = $returnDictionaryRepository;
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

        $errors = [];
        $data = [];

        // Prefill default customer info
        $data['firstname'] = $this->customer->getFirstName();
        $data['lastname'] = $this->customer->getLastName();
        $data['email'] = $this->customer->getEmail();
        $data['telephone'] = $this->customer->getTelephone();
        $data['opened'] = 0;
        $data['quantity'] = 1;

        // Prefill based on order_id and product_id (from query parameters)
        $queryParams = $request->getQueryParams();
        $orderId = isset($queryParams['order_id']) ? (int)$queryParams['order_id'] : 0;
        $productId = isset($queryParams['product_id']) ? (int)$queryParams['product_id'] : 0;

        if ($orderId > 0) {
            $order = $this->orderRepository->getOrder($orderId, $customerId);
            if ($order) {
                $data['order_id'] = $orderId;
                $data['date_ordered'] = date('Y-m-d', strtotime($order['date_added']));
                
                if ($productId > 0) {
                    $products = $this->orderRepository->getProducts($orderId);
                    foreach ($products as $product) {
                        if ((int)$product['product_id'] === $productId) {
                            $data['product'] = $product['name'];
                            $data['model'] = $product['model'];
                            $data['quantity'] = $product['quantity'];
                            break;
                        }
                    }
                }
            }
        }

        if ($request->getMethod() === 'POST') {
            $post = $request->getParsedBody();
            $data = array_merge($data, $post);

            // Validation
            if (empty($data['order_id'])) {
                $errors['order_id'] = $this->translator->get('error_order_id', 'account/returns');
            }
            if (empty($data['firstname']) || strlen($data['firstname']) < 1 || strlen($data['firstname']) > 32) {
                $errors['firstname'] = $this->translator->get('error_firstname', 'account/returns');
            }
            if (empty($data['lastname']) || strlen($data['lastname']) < 1 || strlen($data['lastname']) > 32) {
                $errors['lastname'] = $this->translator->get('error_lastname', 'account/returns');
            }
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = $this->translator->get('error_email', 'account/returns');
            }
            if (empty($data['telephone']) || strlen($data['telephone']) < 10 || strlen($data['telephone']) > 11) {
                // Se o telefone estiver ligeiramente diferente, podemos limpar caracteres não-numéricos antes de validar
                $cleanPhone = preg_replace('/\D/', '', $data['telephone']);
                if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 11) {
                    $errors['telephone'] = $this->translator->get('error_telephone', 'account/returns');
                }
            }
            if (empty($data['product']) || strlen($data['product']) < 3 || strlen($data['product']) > 255) {
                $errors['product'] = $this->translator->get('error_product', 'account/returns');
            }
            if (empty($data['model']) || strlen($data['model']) < 3 || strlen($data['model']) > 64) {
                $errors['model'] = $this->translator->get('error_model', 'account/returns');
            }
            if (empty($data['return_reason_id'])) {
                $errors['return_reason_id'] = $this->translator->get('error_reason', 'account/returns');
            }

            if (!$errors) {
                // Salvar devolução
                $this->orderReturnRepository->addReturn($data);

                // Redireciona para a lista de devoluções com mensagem de sucesso
                return $response
                    ->withHeader('Location', $routeParser->urlFor('account.returns', ['lang' => $lang]) . '?success=1')
                    ->withStatus(302);
            }
        }

        // Dicionário de motivos
        $languageId = $lang === 'en' ? 1 : 2; // pt-br/es maps to 2, en maps to 1
        $reasons = $this->returnDictionaryRepository->getReasonsByLanguage($languageId);

        $breadcrumbs = [
            ['text' => $this->translator->get('text_home', 'account/returns'), 'href' => $routeParser->urlFor('home', ['lang' => $lang])],
            ['text' => $this->translator->get('text_account', 'account/returns'), 'href' => $routeParser->urlFor('account.index', ['lang' => $lang])],
            ['text' => $this->translator->get('heading_title', 'account/returns'), 'href' => $routeParser->urlFor('account.returns', ['lang' => $lang])],
            ['text' => $this->translator->get('text_return', 'account/returns'), 'href' => $routeParser->urlFor('account.returns.add', ['lang' => $lang])]
        ];

        $viewData = [
            'breadcrumbs' => $breadcrumbs,
            'errors'      => $errors,
            'data'        => $data,
            'reasons'     => $reasons,
            'action'      => $routeParser->urlFor('account.returns.add', ['lang' => $lang]),
            'back'        => $routeParser->urlFor('account.returns', ['lang' => $lang])
        ];

        $html = $this->twig->render('pages/users/return.twig', $viewData);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
