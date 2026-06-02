<?php

namespace Alpha\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Containers\AppContainer; // 👈 Nossa fábrica de comandos

/*
* Este novo controller será responsável pelas novas rotas a serem implementas para substituir as antigas rotas legadas do OpenCart.
*
*/


class MainRouterController
{
    private $appContainer;

    public function __construct(AppContainer $appContainer)
    {
        $this->appContainer = $appContainer;
    }

    public function handle(Request $request, Response $response, array $args): Response
    {
        // 1. Descobre o nome da classe que a rota solicitou
        $actionClass = $request->getAttribute('action_class');

        // 2. Uso do Padrão Factory: A fábrica constrói o objeto com tudo o que ele precisa dentro
        $action = $this->appContainer->get($actionClass);

        // 3. Executa a ação
        return $action($request, $response, $args);
    }
}
