<?php

namespace Alpha\Middleware;

use Opencart\System\Engine\Registry;

/**
 * RegistryPipeline - Orquestrador de middlewares na Alpha Engine.
 */
class RegistryPipeline
{
    protected array $middlewares = [];

    /**
     * Adiciona um middleware à pilha.
     */
    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Executa a pilha de middlewares sobre o Registry.
     * 
     * @param Registry $registry
     * @param callable $destination A ação final (geralmente o index do controller).
     */
    public function process(Registry $registry, callable $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            $this->createSlice(),
            $destination
        );

        return $pipeline($registry);
    }

    private function createSlice(): callable
    {
        return function ($next, $middleware) {
            return function ($registry) use ($next, $middleware) {
                return $middleware->handle($registry, $next);
            };
        };
    }
}