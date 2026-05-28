<?php

namespace Alpha\Controller\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface ActionInterface
{
    /**
     * Garante que toda Action do sistema SEJA executável como uma função
     * e retorne obrigatoriamente uma resposta HTTP válida do PSR-7.
     */
    public function __invoke(Request $request, Response $response, array $args): Response;
}
