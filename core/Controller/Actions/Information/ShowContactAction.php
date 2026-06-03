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

        $globals = $this->twig->getGlobals();
        $settings = $globals['settings'] ?? [];

        // Recupera os dados padrão da página de contato (configurações da loja, etc)
        $contactData = [
            'store'     => $settings['config_name'] ?? '',
            'address'   => isset($settings['config_address']) ? nl2br($settings['config_address']) : '',
            'telephone' => $settings['config_telephone'] ?? '',
            'open'      => isset($settings['config_open']) ? nl2br($settings['config_open']) : '',
            'comment'   => $settings['config_comment'] ?? '',
            'name'      => '',
            'email'     => '',
            'enquiry'   => '',
        ];

        // Se for uma requisição POST, processamos o envio do formulário
        if ($request->getMethod() === 'POST') {
            $postData = $request->getParsedBody();

            if (method_exists($this->informationRepository, 'validateContactForm')) {
                $errors = $this->informationRepository->validateContactForm($postData);
            } else {
                if (empty($postData['name']) || mb_strlen($postData['name']) < 3 || mb_strlen($postData['name']) > 32) {
                    $errors['name'] = 'O nome deve ter entre 3 e 32 caracteres!';
                }
                if (empty($postData['email']) || !filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'O endereço de e-mail não parece ser válido!';
                }
                if (empty($postData['enquiry']) || mb_strlen($postData['enquiry']) < 10 || mb_strlen($postData['enquiry']) > 3000) {
                    $errors['enquiry'] = 'A mensagem deve ter entre 10 e 3000 caracteres!';
                }
            }

            if (empty($errors)) {
                // Envia a mensagem
                if (method_exists($this->informationRepository, 'sendEnquiry')) {
                    $this->informationRepository->sendEnquiry($postData);
                }
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
            'title'       => 'Fale Conosco | AgSonhos',
            'description' => 'Entre em contato com a equipe AgSonhos. Tire suas dúvidas, envie sugestões ou solicite orçamentos.',
            'keywords'    => 'contato, fale conosco, suporte, email, telefone, agsonhos',
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
            'action'      => $routeParser->urlFor('contact', ['lang' => $lang])
        ]);

        $html = $this->twig->render('pages/information/contact.twig', $templateData);

        $response->getBody()->write($html);
        return $response;
    }
}
