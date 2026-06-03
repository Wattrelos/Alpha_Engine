<?php
namespace Alpha\View;

use RuntimeException;
use Throwable;
use Psr\Container\ContainerInterface;

/**
 * Class ViewRenderer
 * 
 * Carregador de Views proprietário da Alpha Engine.
 * Substitui o problemático $this->load->view() do OpenCart para prevenir WSOD (Erros Fantasmas).
 * Envelopa a execução do Twig e eventos nativos em um rastreador rigoroso de falhas.
 */
class ViewRenderer {
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Renderiza uma view garantindo proteção contra vazamento de buffer e captura de Throwable.
     *
     * @param string $route Rota da view (ex: 'checkout/cart')
     * @param array $data Dados a serem injetados no template
     * @param string $code Código HTML alternativo
     * @return string O HTML processado
     * @throws RuntimeException
     */
    public function render(string $route, array $data = [], string $code = ''): string {
        $event = $this->container->get('event');

        try {
            // Dispara evento nativo 'before' para compatibilidade com extensões (ex: injeção de scripts)
            if ($event) {
                $event->trigger('view/' . $route . '/before', [&$route, &$data, &$code]);
            }

            // Inicia um buffer isolado para blindar vazamentos caso o Twig sofra Crash
            ob_start();

            // Recupera a instância global do Template (já configurada com os paths no framework.php)
            $template = $this->container->get('template');

            // Processa o template usando a assinatura do OpenCart 4
            $output = $template->render($route, $data, $code);

            // Limpa o buffer com sucesso
            if (ob_get_level()) ob_end_clean();

            // Dispara evento nativo 'after' para permitir modificações no HTML final
            if ($event) {
                $event->trigger('view/' . $route . '/after', [&$route, &$data, &$output]);
            }

            return $output;

        } catch (Throwable $t) {
            // Captura tanto \Exception quanto \Error (Erro Fatal PHP 8)
            // Previne que o buffer fique aberto e cause o WSOD
            if (ob_get_level()) {
                ob_end_clean();
            }

            $errorMessage = sprintf(
                "[Alpha ViewRenderer] Erro fatal (WSOD evitado) ao renderizar a view '%s'. \nArquivo: %s (Linha %d). \nDetalhes: %s",
                $route, $t->getFile(), $t->getLine(), $t->getMessage()
            );

            // Força a escrita no disco físico (trace imediato)
            file_put_contents(DIR_LOGS . 'alpha_view_trace.log', "[" . date('Y-m-d H:i:s') . "] " . $errorMessage . PHP_EOL, FILE_APPEND);

            // Relança de forma estruturada para o Framework capturar ou gerar JSON caso seja AJAX
            throw new RuntimeException($errorMessage, (int)$t->getCode(), $t);
        }
    }
}