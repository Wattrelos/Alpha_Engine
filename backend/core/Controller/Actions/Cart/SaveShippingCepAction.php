<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Psr\Container\ContainerInterface;

/**
 * SaveShippingCepAction - Salva o CEP e os dados de endereço do ViaCEP na sessão.
 */
class SaveShippingCepAction implements ActionInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $raw = (string)$request->getBody();
            $body = !empty($raw) ? json_decode($raw, true) : [];
        }
        $cep = trim((string)($body['cep'] ?? ''));
        $viaCepData = $body['via_cep'] ?? null;

        if (empty($cep) || strlen(preg_replace('/\D/', '', $cep)) !== 8) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'CEP inválido.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        $sanitizedCep = preg_replace('/\D/', '', $cep);
        $_SESSION['shipping_cep'] = $sanitizedCep;

        $currentAddress = $_SESSION['shipping_address'] ?? [];
        if (!is_array($currentAddress)) {
            $currentAddress = [];
        }

        if ($viaCepData && is_array($viaCepData)) {
            $_SESSION['shipping_via_cep'] = $viaCepData;
            $_SESSION['shipping_address'] = array_merge(
                $currentAddress,
                [
                    'postcode' => $viaCepData['cep'] ?? $sanitizedCep,
                    'street' => $viaCepData['logradouro'] ?? '',
                    'complement' => $viaCepData['complemento'] ?? '',
                    'city' => $viaCepData['localidade'] ?? '',
                    'zone' => $viaCepData['uf'] ?? '',
                    'neighborhood' => $viaCepData['bairro'] ?? '',
                    'country_id' => 30 // Fixo Brasil (Alpha Engine standard)
                ]
            );
        } else {
            $_SESSION['shipping_address'] = array_merge(
                $currentAddress,
                [
                    'postcode' => $sanitizedCep,
                    'country_id' => 30
                ]
            );
        }

        $session = $this->container->has('session') ? $this->container->get('session') : null;
        if ($session && is_object($session) && property_exists($session, 'data') && is_array($session->data)) {
            $session->data['shipping_cep'] = $_SESSION['shipping_cep'];
            $session->data['shipping_address'] = $_SESSION['shipping_address'];
            if (isset($_SESSION['shipping_via_cep'])) {
                $session->data['shipping_via_cep'] = $_SESSION['shipping_via_cep'];
            }
        }

        $response->getBody()->write(json_encode([
            'success' => true
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
