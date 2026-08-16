<?php
require __DIR__ . '/../../backend/vendor/autoload.php';

define('APPLICATION', 'catalog');
require_once __DIR__ . '/../../backend/config.php';

use Containers\AppBootstrap;
use Alpha\Events\EventDispatcher;
use Alpha\Events\OrderCreatedEvent;
use Alpha\Model\Domain\DTOs\OrderDataDTO;

echo " [*] Inicializando o Bootstrap do App...\n";
$bootstrap = AppBootstrap::boot();
$container = $bootstrap->getContainer();

echo " [*] Recuperando o EventDispatcher do container...\n";
if ($container->has(EventDispatcher::class)) {
    $dispatcher = $container->get(EventDispatcher::class);
    echo "  -> EventDispatcher obtido com sucesso!\n";

    // Criar um DTO de simulação
    $orderData = [
        'total' => 350.00,
        'email' => 'teste@email.com',
        'firstname' => 'João',
        'lastname' => 'Silva',
        'telephone' => '11999999999',
        'payment_method' => 'Pix',
        'shipping_method' => 'Retirar na Loja'
    ];
    $orderDto = new OrderDataDTO($orderData);

    echo " [*] Disparando evento de teste 'order.created' para o ID 999...\n";
    $event = new OrderCreatedEvent(999, $orderDto);
    $dispatcher->dispatch($event);
    echo " [*] Evento disparado!\n";
} else {
    echo "  -> Erro: EventDispatcher não está no container.\n";
}
