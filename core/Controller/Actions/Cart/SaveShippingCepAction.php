<?php

namespace Alpha\Controller\Actions\Cart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Support\Registry;

/**
 * SaveShippingCepAction - Salva o CEP e os dados de endereço do ViaCEP na sessão.
 */
class SaveShippingCepAction implements ActionInterface
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);
        $cep = trim((string)($body['cep'] ?? ''));
        $viaCepData = $body['via_cep'] ?? null;

        if (empty($cep) || strlen(preg_replace('/\D/', '', $cep)) !== 8) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'CEP inválido.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $session = $this->registry->get('session');
        if ($session) {
            $session->data['shipping_cep'] = preg_replace('/\D/', '', $cep);
            
            if ($viaCepData && is_array($viaCepData)) {
                $session->data['shipping_via_cep'] = $viaCepData;
                
                $currentAddress = $session->data['shipping_address'] ?? [];
                if (!is_array($currentAddress)) {
                    $currentAddress = [];
                }

                $session->data['shipping_address'] = array_merge(
                    $currentAddress,
                    [
                        'postcode' => $viaCepData['cep'] ?? $cep,
                        'address_1' => $viaCepData['logradouro'] ?? '',
                        'address_2' => $viaCepData['complemento'] ?? '',
                        'city' => $viaCepData['localidade'] ?? '',
                        'zone' => $viaCepData['uf'] ?? '',
                        'neighborhood' => $viaCepData['bairro'] ?? '',
                        'country_id' => 30 // Fixo Brasil (Alpha Engine standard)
                    ]
                );
            }
        }

        $response->getBody()->write(json_encode([
            'success' => true
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
