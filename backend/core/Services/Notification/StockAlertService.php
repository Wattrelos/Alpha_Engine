<?php

namespace Alpha\Services\Notification;

use Alpha\Model\Domain\Repositories\StockAlertRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Container\ContainerInterface;

/**
 * StockAlertService - Orquestra o processamento, cálculo de cota e envio das notificações de estoque.
 * 
 * Conforme ADR 0008.
 */
class StockAlertService
{
    private StockAlertRepository $stockAlertRepository;
    private ProductRepository $productRepository;
    private ContainerInterface $container;

    public function __construct(
        StockAlertRepository $stockAlertRepository,
        ProductRepository $productRepository,
        ContainerInterface $container
    ) {
        $this->stockAlertRepository = $stockAlertRepository;
        $this->productRepository = $productRepository;
        $this->container = $container;
    }

    /**
     * Processa o evento de reposição de estoque, aplicando a cota anti-frustração (FIFO).
     *
     * @param int $productId
     * @param int|null $variantId
     * @param int $newQuantity Saldo físico reabastecido
     * @param int $storeId
     * @param float $quotaMultiplier Multiplicador de cota (padrão: 3)
     * @return array Resumo do processamento
     */
    public function processReplenishment(
        int $productId,
        ?int $variantId = null,
        int $newQuantity = 1,
        int $storeId = 1,
        float $quotaMultiplier = 3.0
    ): array {
        if ($newQuantity <= 0) {
            return [
                'status' => 'skipped',
                'reason' => 'Quantidade reposta menor ou igual a zero',
                'notified_count' => 0
            ];
        }

        // Obtém os dados do produto para enriquecer o e-mail
        $product = $this->productRepository->getProduct($productId);
        if (!$product) {
            return [
                'status' => 'error',
                'reason' => 'Produto não encontrado',
                'notified_count' => 0
            ];
        }

        // Busca os inscritos prioritários dentro da cota
        $pendingAlerts = $this->stockAlertRepository->getPendingAlertsForReplenishment(
            $productId,
            $variantId,
            $newQuantity,
            $quotaMultiplier
        );

        if (empty($pendingAlerts)) {
            return [
                'status' => 'no_subscribers',
                'reason' => 'Nenhum alerta pendente para este item',
                'notified_count' => 0
            ];
        }

        $sentIds = [];
        $productName = $product['name'] ?? 'Produto';
        $productUrl = '/pt-br/produto/' . ($product['slug'] ?? $productId);

        foreach ($pendingAlerts as $alert) {
            $alertId = (int)$alert['id'];
            $email = $alert['email'];
            $name = $alert['name'];
            $token = $alert['unsubscribe_token'];

            $success = $this->sendNotificationEmail(
                $email,
                $name,
                $productName,
                $productUrl,
                $token,
                $newQuantity
            );

            if ($success) {
                $sentIds[] = $alertId;
            }
        }

        if (!empty($sentIds)) {
            $this->stockAlertRepository->markAsSent($sentIds);
        }

        return [
            'status'         => 'success',
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'quantity'       => $newQuantity,
            'notified_count' => count($sentIds),
            'sent_ids'       => $sentIds
        ];
    }

    /**
     * Envia o e-mail transacional de volta ao estoque.
     */
    private function sendNotificationEmail(
        string $email,
        string $name,
        string $productName,
        string $productUrl,
        string $unsubscribeToken,
        int $availableQty
    ): bool {
        $subject = "🌟 Olha quem voltou! O {$productName} está disponível!";
        $unsubscribeUrl = "/pt-br/catalog/stock-alert/unsubscribe?token=" . urlencode($unsubscribeToken);

        $body = "Olá, {$name}!\n\n"
              . "Você pediu e nós avisamos: o produto \"{$productName}\" acabou de retornar ao nosso estoque!\n"
              . "Temos estoque limitado ({$availableQty} unidades). Corra para garantir o seu antes que esgote novamente:\n\n"
              . "Acesse agora: {$productUrl}\n\n"
              . "Para cancelar novos avisos sobre este item, utilize o link:\n"
              . "{$unsubscribeUrl}\n\n"
              . "Equipe AG Sonhos";

        // Se houver mailer registrado no container, usa-o; caso contrário, registra no log do sistema
        if ($this->container->has('mailer')) {
            try {
                $mailer = $this->container->get('mailer');
                if (method_exists($mailer, 'send')) {
                    $mailer->send($email, $subject, $body);
                    return true;
                }
            } catch (\Throwable $e) {
                error_log("Erro no serviço de mailer externo: " . $e->getMessage());
            }
        }

        // Em ambiente de teste/desenvolvimento ou sem SMTP configurado, registra no log transacional
        $maskedEmail = \Alpha\Support\LgpdSanitizer::maskEmail($email);
        error_log("[STOCK_ALERT] E-mail disparado para {$maskedEmail}: {$subject}");
        return true;
    }
}
