<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\StockAlertRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;
use Psr\Container\ContainerInterface;

/**
 * SubscribeStockAlertAction - Processa a inscrição do cliente no aviso de disponibilidade ("Avise-me quando chegar").
 * 
 * Conforme ADR 0008, ADR 0005 e ADR 0007 (LGPD).
 */
class SubscribeStockAlertAction implements ActionInterface
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

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = (array)($request->getParsedBody() ?? []);

        // 1. Defesa Anti-Bot (Honeypot invisível)
        if (!empty($data['form_check_company'])) {
            // Descarta silenciosamente com resposta de sucesso simulada
            return $this->jsonResponse($response, true, 'Seu aviso foi cadastrado com sucesso!');
        }

        $serverParams = $request->getServerParams();
        $ip = (string)($serverParams['HTTP_X_FORWARDED_FOR'] ?? $serverParams['REMOTE_ADDR'] ?? '127.0.0.1');
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }
        $userAgent = (string)($serverParams['HTTP_USER_AGENT'] ?? '');

        // 2. Rate Limiting no Redis (se ativo) ou Sessão
        if ($this->isRateLimited($ip)) {
            return $this->jsonResponse($response, false, 'Muitas solicitações recentes. Por favor, aguarde alguns instantes.', 429);
        }

        // 3. Validações dos campos obrigatórios
        $productId = (int)($data['product_id'] ?? 0);
        $variantId = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
        $name = trim((string)($data['name'] ?? ''));
        $email = trim(strtolower((string)($data['email'] ?? '')));
        $phone = !empty($data['phone']) ? trim((string)$data['phone']) : null;
        $consentPrivacy = !empty($data['consent_privacy']);
        $consentMarketing = !empty($data['consent_marketing']);

        $errors = [];

        if ($productId <= 0) {
            $errors['product_id'] = 'Identificador do produto inválido.';
        } else {
            $product = $this->productRepository->getProduct($productId);
            if (!$product) {
                $errors['product_id'] = 'Produto não localizado em nosso catálogo.';
            }
        }

        if (empty($name)) {
            $errors['name'] = 'Por favor, informe o seu nome.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Por favor, informe um endereço de e-mail válido.';
        }

        if (!$consentPrivacy) {
            $errors['consent_privacy'] = 'É necessário concordar com os termos para receber o aviso deste produto.';
        }

        if (!empty($errors)) {
            return $this->jsonResponse($response, false, 'Verifique os dados informados.', 422, ['errors' => $errors]);
        }

        // 4. Identificação do Cliente Autenticado (se houver)
        $customerId = null;
        if ($this->container->has('customer')) {
            $customer = $this->container->get('customer');
            if ($customer && method_exists($customer, 'isLogged') && $customer->isLogged()) {
                $customerId = (int)$customer->getId();
            }
        }

        $storeId = 1;
        if ($this->container->has('storeId')) {
            $storeId = (int)$this->container->get('storeId');
        } elseif ($this->container->has('config')) {
            $cfg = $this->container->get('config');
            if ($cfg && method_exists($cfg, 'get') && $cfg->get('config_store_id') !== null) {
                $storeId = (int)$cfg->get('config_store_id');
            }
        }

        $languageId = (int)$request->getAttribute('language_id', 2);

        // 5. Persistência da Inscrição via Repositório
        try {
            $this->stockAlertRepository->subscribe([
                'store_id'          => $storeId,
                'language_id'       => $languageId,
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'customer_id'       => $customerId,
                'name'              => $name,
                'email'             => $email,
                'phone'             => $phone,
                'ip'                => $ip,
                'user_agent'        => $userAgent,
                'consent_privacy'   => $consentPrivacy,
                'consent_marketing' => $consentMarketing,
            ]);

            return $this->jsonResponse(
                $response, 
                true, 
                'Excelente! Assim que o estoque estiver disponível, nós avisaremos você por e-mail.'
            );
        } catch (\Throwable $e) {
            error_log("Erro ao salvar stock alert: " . $e->getMessage());
            return $this->jsonResponse($response, false, 'Não foi possível registrar o aviso no momento. Tente novamente mais tarde.', 500);
        }
    }

    private function isRateLimited(string $ip): bool
    {
        // Se houver conexão com Redis no container
        if ($this->container->has('redis')) {
            try {
                $redis = $this->container->get('redis');
                if ($redis) {
                    $key = 'rate_limit:stock_alert:' . md5($ip);
                    $current = (int)$redis->incr($key);
                    if ($current === 1) {
                        $redis->expire($key, 600); // 10 minutos
                    }
                    return $current > 5;
                }
            } catch (\Throwable) {
                // Silencioso se redis indisponível
            }
        }

        // Fallback em sessão caso o Redis não esteja ativo
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessKey = 'stock_alert_rate_' . md5($ip);
            $history = $_SESSION[$sessKey] ?? [];
            $now = time();
            $history = array_filter($history, fn($ts) => ($now - $ts) < 600);
            if (count($history) >= 5) {
                return true;
            }
            $history[] = $now;
            $_SESSION[$sessKey] = $history;
        }

        return false;
    }

    private function jsonResponse(Response $response, bool $success, string $message, int $status = 200, array $extra = []): Response
    {
        $payload = array_merge([
            'success' => $success,
            'message' => $message,
        ], $extra);

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
