<?php

namespace Alpha\Controller\Actions\Product;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Alpha\Controller\Actions\ActionInterface;
use Alpha\Model\Domain\Repositories\StockAlertRepository;
use Twig\Environment as TwigEnvironment;

/**
 * UnsubscribeStockAlertAction - Processa o cancelamento (Opt-out) com 1 clique a partir do token.
 * 
 * Conforme ADR 0008 e ADR 0007 (LGPD).
 */
class UnsubscribeStockAlertAction implements ActionInterface
{
    private StockAlertRepository $stockAlertRepository;
    private TwigEnvironment $twig;

    public function __construct(
        StockAlertRepository $stockAlertRepository,
        TwigEnvironment $twig
    ) {
        $this->stockAlertRepository = $stockAlertRepository;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        $token = trim((string)($params['token'] ?? ''));

        $success = false;
        $message = '';

        if (!empty($token) && strlen($token) === 64) {
            $success = $this->stockAlertRepository->unsubscribe($token);
            $message = $success 
                ? 'Sua solicitação de aviso foi cancelada com sucesso. Você não receberá mais notificações sobre este produto.'
                : 'Não encontramos nenhum alerta pendente vinculado a este link.';
        } else {
            $message = 'Link de cancelamento inválido ou expirado.';
        }

        // Renderiza uma página amigável com a confirmação
        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelamento de Alerta de Estoque | AG Sonhos</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .unsub-container {
            max-width: 560px;
            margin: 80px auto;
            padding: 32px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333333;
        }
        .unsub-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .unsub-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e293b;
        }
        .unsub-text {
            font-size: 16px;
            line-height: 1.6;
            color: #64748b;
            margin-bottom: 24px;
        }
        .unsub-btn {
            display: inline-block;
            background: #fc9003;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .unsub-btn:hover {
            background: #e07f00;
        }
    </style>
</head>
<body style="background: #f8fafc; margin: 0; padding: 20px;">
    <div class="unsub-container">
        <div class="unsub-icon">{$this->renderIcon($success)}</div>
        <h1 class="unsub-title">{$this->renderTitle($success)}</h1>
        <p class="unsub-text">{$message}</p>
        <a href="/" class="unsub-btn">Ir para a Página Inicial</a>
    </div>
</body>
</html>
HTML;

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    private function renderIcon(bool $success): string
    {
        return $success ? '✅' : 'ℹ️';
    }

    private function renderTitle(bool $success): string
    {
        return $success ? 'Alerta Cancelado com Sucesso' : 'Aviso';
    }
}
