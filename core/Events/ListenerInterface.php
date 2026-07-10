<?php

namespace Alpha\Events;

/**
 * ListenerInterface - Contrato básico para os assinantes (Observers) de eventos de domínio.
 */
interface ListenerInterface
{
    /**
     * Manipula o evento disparado.
     *
     * @param EventInterface $event
     * @return void
     */
    public function handle(EventInterface $event): void;
}
