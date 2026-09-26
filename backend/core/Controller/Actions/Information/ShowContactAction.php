<?php

namespace Alpha\Controller\Actions\Information;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Model\Domain\Repositories\InformationRepository;
use Twig\Environment as TwigEnvironment;
use Alpha\Controller\Actions\ActionInterface;
use Slim\Routing\RouteContext;

class ShowContactAction implements ActionInterface
{
    private InformationRepository $informationRepository;
    private TwigEnvironment $twig;

    public function __construct(
        InformationRepository $informationRepository,
        TwigEnvironment $twig
    ) {
        $this->informationRepository = $informationRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $lang = $request->getAttribute('lang', 'pt-br');
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();

        $errors = [];
        $success = false;

        // Recupera as configurações de contato formatadas e cacheadas via repositório
        $storeData = $this->informationRepository->getContactSettings();

        // Recupera os dados padrão da página de contato (configurações da loja, etc)
        $contactData = [
            'store'     => $storeData['name'] ?? '',
            'address'   => isset($storeData['address']) ? nl2br($storeData['address']) : '',
            'telephone' => $storeData['telephone'] ?? '',
            'open'      => isset($storeData['open']) ? nl2br($storeData['open']) : '',
            'comment'   => $storeData['raw']['comment'] ?? '',
            'name'      => '',
            'email'     => '',
            'enquiry'   => '',
        ];

        // Se for uma requisição POST, processamos o envio do formulário
        if ($request->getMethod() === 'POST') {
            $postData = $request->getParsedBody();

            $errors = $this->informationRepository->validateContactForm($postData);

            if (empty($errors)) {
                // Envia a mensagem
                $this->informationRepository->sendEnquiry($postData);
                $success = true;

                // Limpa os campos preenchidos para não exibi-los no formulário novamente
                $contactData['name'] = $contactData['email'] = $contactData['enquiry'] = '';
            } else {
                // Mantém os campos preenchidos em caso de erro
                $contactData['name'] = $postData['name'] ?? '';
                $contactData['email'] = $postData['email'] ?? '';
                $contactData['enquiry'] = $postData['enquiry'] ?? '';
            }
        }

        // Tags de SEO
        $seoData = [
            'title'       => 'Fale Conosco | meusite',
            'description' => 'Entre em contato com a equipe meusite. Tire suas dúvidas, envie sugestões ou solicite orçamentos.',
            'keywords'    => 'contato, fale conosco, suporte, email, telefone, meusite',
            'canonical'   => $routeParser->urlFor('contact', ['lang' => $lang])
        ];

        // Monta os Breadcrumbs
        $breadcrumbs = [];
        $breadcrumbs[] = [
            'text' => 'Home',
            'href' => $routeParser->urlFor('home', ['lang' => $lang])
        ];
        $breadcrumbs[] = [
            'text' => 'Fale Conosco',
            'href' => $routeParser->urlFor('contact', ['lang' => $lang])
        ];

        // Mescla todos os dados para o template twig
        $templateData = array_merge($contactData, [
            'breadcrumbs' => $breadcrumbs,
            'seo'         => $seoData,
            'errors'      => $errors,
            'success'     => $success,
            'action'      => $routeParser->urlFor('contact', ['lang' => $lang]),
            'store'       => $storeData
        ]);

        $html = $this->twig->render('pages/information/contact.twig', $templateData);

        $response->getBody()->write($html);
        return $response;
    }
}
