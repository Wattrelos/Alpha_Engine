<?php

namespace Alpha\Events;

/**
 * StockReplenishedListener - Observer que reage à reposição de estoque
 * e despacha a mensagem assíncrona para a fila RabbitMQ 'notification.stock_alert'.
 * 
 * Conforme ADR 0008.
 */
class StockReplenishedListener implements ListenerInterface
{
    private QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function handle(EventInterface $event): void
    {
        if ($event instanceof StockReplenishedEvent) {
            $payload = [
                'event'        => $event->getName(),
                'store_id'     => $event->getStoreId(),
                'product_id'   => $event->getProductId(),
                'variant_id'   => $event->getVariantId(),
                'new_quantity' => $event->getNewQuantity(),
                'timestamp'    => date('c')
            ];

            try {
                $this->queueService->publish('notification.stock_alert', $payload);
            } catch (\Throwable $e) {
                // Em caso de indisponibilidade transitória do broker RabbitMQ, registra no log para que o cron atue
                error_log("StockReplenishedListener Fallback: " . $e->getMessage());
            }
        }
    }
}
