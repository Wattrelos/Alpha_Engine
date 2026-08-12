<?php

namespace Alpha\Events;

/**
 * EventDispatcher - Classe central encarregada de gerenciar e notificar assinantes (Observers).
 */
class EventDispatcher
{
    /**
     * @var array<string, array<ListenerInterface>>
     */
    private array $listeners = [];

    /**
     * Registra um ouvinte para um tipo específico de evento.
     *
     * @param string $eventName
     * @param ListenerInterface $listener
     * @return void
     */
    public function addListener(string $eventName, ListenerInterface $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Dispara um evento e notifica todos os ouvintes associados.
     *
     * @param EventInterface $event
     * @return void
     */
    public function dispatch(EventInterface $event): void
    {
        $eventName = $event->getName();
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                try {
                    $listener->handle($event);
                } catch (\Throwable $e) {
                    // Impede que erros em ouvintes secundários quebrem o fluxo principal.
                    // Em produção, isso deve ser logado apropriadamente.
                    error_log("Erro no Listener executando o evento '{$eventName}': " . $e->getMessage());
                }
            }
        }
    }
}
