let estaProcessando = false;

// Intercepta a submissão do formulário principal de checkout para usar fluxo AJAX com idempotência
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkout-main-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validação local rápida para garantir que o método de pagamento foi selecionado
            const paymentMethodSelected = form.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethodSelected) {
                exibirMensagemErro("Por favor, selecione uma forma de pagamento.");
                return;
            }

            // Valida campos obrigatórios visíveis da etapa atual
            const inputsVisiveis = Array.from(form.querySelectorAll('[data-required="true"]')).filter(input => {
                return !input.closest('.egen-checkout-form-hidden, .egen-checkout-step:not(.active)');
            });

            let formValido = true;
            for (const input of inputsVisiveis) {
                if (!input.value || !input.value.trim()) {
                    input.classList.add('is-invalid');
                    formValido = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            }

            if (!formValido) {
                exibirMensagemErro("Por favor, preencha todos os campos obrigatórios visíveis.");
                return;
            }

            // Coleta os dados de todos os campos do formulário
            const formData = new FormData(form);
            const dadosPedido = {};
            formData.forEach((value, key) => {
                dadosPedido[key] = value;
            });

            const actionUrl = form.getAttribute('action') || window.location.pathname;
            await finalizarCompra(actionUrl, dadosPedido);
        });
    }
});

async function finalizarCompra(actionUrl, dadosPedido) {
    if (estaProcessando) return; // Camada interna de proteção no JS

    try {
        estaProcessando = true;
        configurarBotãoVisual("carregando"); // Desabilita o botão na tela

        // O token deve ser gerado UMA VEZ por tentativa de compra
        const idempotencyKey = gerarUUID();

        const response = await fetch(actionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Idempotency-Key': idempotencyKey
            },
            body: JSON.stringify(dadosPedido)
        });

        // TRATAMENTO DA FALHA: Se for uma requisição duplicada que "escapou"
        if (response.status === 422) {
            const dadosErro = await response.json();
            if (dadosErro.error === "DUPLICATE_REQUEST") {
                console.warn("Clique duplo detectado e bloqueado pelo backend.");
                // Não exibe erro na tela ainda! Espera a requisição original responder.
                return;
            }
        }

        if (response.status === 201 || response.status === 200) {
            const pedido = await response.json();
            redirecionarParaSucesso(pedido.id);
        } else {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.message || "Erro no processamento do pedido.");
        }

    } catch (error) {
        exibirMensagemErro(error.message || "Erro ao processar pedido. Tente novamente.");
        estaProcessando = false;
        configurarBotãoVisual("normal"); // Reativa o botão se falhar feio
    }
}

// Gera um UUID v4 no client-side para servir como Idempotency Key
function gerarUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

// Controla o estado visual e carregamento do botão de confirmação
function configurarBotãoVisual(estado) {
    const btn = document.getElementById('btn-submit-checkout');
    if (!btn) return;

    if (estado === "carregando") {
        btn.disabled = true;
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processando...';
    } else {
        btn.disabled = false;
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
        }
    }
}

// Redireciona o usuário para a página de sucesso limpando o carrinho local do visitante
function redirecionarParaSucesso(pedidoId) {
    if (typeof guestCheckout !== 'undefined' && typeof guestCheckout.clearGuestCartAfterOrder === 'function') {
        guestCheckout.clearGuestCartAfterOrder();
    }

    const lang = document.body.getAttribute('data-lang') || 'pt-br';
    window.location.href = `/${lang}/checkout/sucesso`;
}

// Exibe mensagem de erro na tela usando o sistema global de notificações
function exibirMensagemErro(msg) {
    if (typeof window.showNotification === 'function') {
        window.showNotification(msg, 'danger');
    } else {
        alert(msg);
    }
}
