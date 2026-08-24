/**
 * Alpha Engine - Shipping Simulator (Calculador de Frete e Prazo)
 * 
 * Gerencia o cálculo de frete por CEP (ViaCEP), persistência de CEP na sessão
 * e exibição dinâmica das modalidades de frete disponíveis na página de produto e carrinho.
 */

(function () {
    function notify(message, type = 'danger') {
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else if (typeof window.showCartAlert === 'function') {
            window.showCartAlert(message, type);
        } else {
            alert(message);
        }
    }

    function initShippingSimulator() {
        const cepInputs = document.querySelectorAll('#shipping-cep, .shipping-cep-input');
        const savedCep = localStorage.getItem('egen_shipping_cep');

        cepInputs.forEach(input => {
            if (!input.value && savedCep) {
                const clean = savedCep.replace(/\D/g, '');
                if (clean.length === 8) {
                    input.value = clean.slice(0, 5) + '-' + clean.slice(5);
                }
            }

            // Se já tem CEP válido no input, exibe os resultados salvos
            const results = input.closest('.egen-shipping-simulator')?.querySelector('#shipping-results, .egen-shipping-simulator__results');
            if (results && input.value.replace(/\D/g, '').length === 8) {
                results.style.display = 'block';
                results.classList.remove('d-none');
            }
        });
    }

    async function handleCalculateShipping(btn) {
        const container = btn.closest('.egen-shipping-simulator') || document;
        const cepInput = container.querySelector('#shipping-cep, .shipping-cep-input');
        const shippingResults = container.querySelector('#shipping-results, .egen-shipping-simulator__results');

        if (!cepInput) return;

        const cepVal = cepInput.value.replace(/\D/g, '');
        if (cepVal.length !== 8) {
            notify('Por favor, informe um CEP válido com 8 dígitos.', 'danger');
            if (shippingResults) {
                shippingResults.style.display = 'none';
            }
            cepInput.focus();
            return;
        }

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            // 1. Consulta o ViaCEP
            const viaCepRes = await fetch(`https://viacep.com.br/ws/${cepVal}/json/`);
            if (!viaCepRes.ok) {
                throw new Error('Falha na resposta do serviço de CEP.');
            }

            const data = await viaCepRes.json();
            if (data.erro) {
                notify('CEP não encontrado. Por favor, verifique o CEP digitado.', 'danger');
                if (shippingResults) {
                    shippingResults.style.display = 'none';
                }
                return;
            }

            // Salva no localStorage
            try {
                localStorage.setItem('egen_shipping_cep', cepVal);
            } catch (e) {}

            // 2. Envia para persistir na sessão da loja
            try {
                await fetch('/api/carrinho/salvar-cep', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        cep: cepVal,
                        via_cep: data
                    })
                });
            } catch (saveErr) {
                console.warn('Aviso: Falha ao persistir CEP na sessão:', saveErr);
            }

            // 3. Atualiza o texto do resultado com a localidade do usuário
            if (shippingResults) {
                const descEl = shippingResults.querySelector('.egen-shipping-simulator__result-desc');
                if (descEl && data.localidade && data.uf) {
                    descEl.textContent = `Disponível em até 1 dia útil (${data.localidade} - ${data.uf})`;
                }
                shippingResults.style.display = 'block';
                shippingResults.classList.remove('d-none');
            }

            notify('Frete calculado com sucesso!', 'success');
        } catch (err) {
            console.error('Erro ao calcular frete:', err);
            // Fallback: se houver falha de rede na API externa mas o CEP possui 8 dígitos válidos
            if (shippingResults) {
                shippingResults.style.display = 'block';
                shippingResults.classList.remove('d-none');
            }
            notify('Frete estimado exibido.', 'info');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }

    // Delegação global de eventos para suportar renderizações estáticas e dinâmicas
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#btn-calculate-shipping, .egen-shipping-simulator__btn');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            handleCalculateShipping(btn);
        }
    });

    document.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const input = e.target.closest('#shipping-cep, .shipping-cep-input');
            if (input) {
                e.preventDefault();
                const container = input.closest('.egen-shipping-simulator') || document;
                const btn = container.querySelector('#btn-calculate-shipping, .egen-shipping-simulator__btn');
                if (btn) {
                    btn.click();
                }
            }
        }
    });

    document.addEventListener('input', function (e) {
        const input = e.target.closest('#shipping-cep, .shipping-cep-input');
        if (input) {
            let val = input.value.replace(/\D/g, '').slice(0, 8);
            if (val.length > 5) {
                val = val.slice(0, 5) + '-' + val.slice(5);
            }
            input.value = val;
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShippingSimulator);
    } else {
        initShippingSimulator();
    }

    window.initShippingSimulator = initShippingSimulator;
})();
