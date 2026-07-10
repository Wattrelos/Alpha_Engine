<?php

namespace Alpha\Events;

/**
 * EventInterface - Contrato básico para todos os eventos de domínio da aplicação.
 */
interface EventInterface
{
    /**
     * Retorna o nome identificador do evento (ex: "order.created").
     *
     * @return string
     */
    public function getName(): string;
}
