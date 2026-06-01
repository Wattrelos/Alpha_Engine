document.addEventListener('DOMContentLoaded', () => {
    const btnShipping = document.getElementById('btn-calculate-shipping');
    const cepInput = document.getElementById('shipping-cep');
    const shippingResults = document.getElementById('shipping-results');

    if (btnShipping && cepInput && shippingResults) {
        // Se já houver CEP salvo ao carregar a página, exibe a opção de frete diretamente
        if (cepInput.value.replace(/\D/g, '').length === 8) {
            shippingResults.style.display = 'block';
        }

        btnShipping.addEventListener('click', function() {
            const cepVal = cepInput.value.replace(/\D/g, '');
            if (cepVal.length !== 8) {
                alert('Por favor, informe um CEP válido com 8 dígitos.');
                shippingResults.style.display = 'none';
                return;
            }

            btnShipping.disabled = true;
            btnShipping.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Consulta o ViaCEP
            fetch(`https://viacep.com.br/ws/${cepVal}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        alert('CEP não encontrado. Por favor, verifique o CEP digitado.');
                        shippingResults.style.display = 'none';
                        btnShipping.disabled = false;
                        btnShipping.innerHTML = 'Calcular';
                        return;
                    }

                    // Envia para persistir na sessão
                    fetch('/api/carrinho/salvar-cep', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            cep: cepVal,
                            via_cep: data
                        })
                    })
                    .then(res => res.json())
                    .then(saveResult => {
                        btnShipping.disabled = false;
                        btnShipping.innerHTML = 'Calcular';
                        if (saveResult.success) {
                            shippingResults.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao salvar CEP:', err);
                        btnShipping.disabled = false;
                        btnShipping.innerHTML = 'Calcular';
                    });
                })
                .catch(err => {
                    console.error('Erro ao consultar ViaCEP:', err);
                    alert('Erro ao consultar o serviço de frete. Tente novamente mais tarde.');
                    btnShipping.disabled = false;
                    btnShipping.innerHTML = 'Calcular';
                    shippingResults.style.display = 'none';
                });
        });
        
        cepInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnShipping.click();
            }
        });
    }
});
