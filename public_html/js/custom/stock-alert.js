/**
 * Subsistema de Alerta de Estoque ("Avise-me quando chegar" - ADR 0008)
 */
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('stock-alert-modal');
    const btnOpen = document.getElementById('btn-open-stock-alert');
    const btnClose = document.getElementById('stock-alert-btn-close');
    const form = document.getElementById('form-stock-alert');
    const feedback = document.getElementById('stock-alert-feedback');
    const btnSubmit = document.getElementById('stock-alert-btn-submit');

    const inputProdId = document.getElementById('stock-alert-product-id');
    const inputVarId = document.getElementById('stock-alert-variant-id');
    const previewVariant = document.getElementById('stock-alert-preview-variant');
    const previewImg = document.getElementById('stock-alert-preview-img');

    if (!modal || !form) {
        return;
    }

    // Função para abrir o modal
    window.openStockAlertModal = function (variantId, variantName, variantThumb) {
        if (inputVarId) {
            inputVarId.value = variantId || '';
        }

        if (previewVariant) {
            if (variantName) {
                previewVariant.textContent = 'Variação: ' + variantName;
                previewVariant.style.display = 'block';
            } else {
                previewVariant.style.display = 'none';
            }
        }

        if (variantThumb && previewImg) {
            previewImg.src = variantThumb;
        }

        feedback.className = 'stock-alert-feedback';
        feedback.style.display = 'none';
        feedback.textContent = '';
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Quero ser avisado!';

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (btnOpen) {
        btnOpen.addEventListener('click', function () {
            // Se houver variação selecionada, puxa os dados dela
            const selectedVariant = document.querySelector('.variant-selector-item.variant--active');
            if (selectedVariant) {
                const vId = selectedVariant.getAttribute('data-id');
                const vName = selectedVariant.querySelector('.prod-show-opt-text')?.textContent || '';
                const vThumb = selectedVariant.getAttribute('data-thumb') || '';
                window.openStockAlertModal(vId, vName, vThumb);
            } else {
                window.openStockAlertModal(null, null, null);
            }
        });
    }

    if (btnClose) {
        btnClose.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // Submissão AJAX do formulário
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        feedback.className = 'stock-alert-feedback';
        feedback.style.display = 'none';
        feedback.textContent = '';

        const privacyChecked = document.getElementById('stock-alert-consent-privacy')?.checked;
        if (!privacyChecked) {
            feedback.className = 'stock-alert-feedback error';
            feedback.style.display = 'block';
            feedback.textContent = 'É necessário concordar com o recebimento do aviso de disponibilidade.';
            return;
        }

        const formData = new FormData(form);

        // Se os inputs CSRF estiverem presentes no form de compra da PDP, garante que estejam no payload
        const purchaseForm = document.getElementById('form-product-purchase');
        if (purchaseForm) {
            const csrfInputs = purchaseForm.querySelectorAll('input[type="hidden"]');
            csrfInputs.forEach(inp => {
                if (inp.name && inp.name.startsWith('csrf') && !formData.has(inp.name)) {
                    formData.append(inp.name, inp.value);
                }
            });
        }

        const currentLang = document.documentElement.lang || 'pt-br';
        const submitUrl = '/' + (currentLang.startsWith('pt') ? 'pt-br' : currentLang) + '/catalog/stock-alert/subscribe';

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        const csrfNameMeta = document.querySelector('meta[name="csrf-name"]')?.getAttribute('content');
        const csrfValueMeta = document.querySelector('meta[name="csrf-value"]')?.getAttribute('content');
        if (csrfNameMeta && csrfValueMeta) {
            headers['X-CSRF-Name'] = csrfNameMeta;
            headers['X-CSRF-Value'] = csrfValueMeta;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cadastrando...';

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: headers,
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.success) {
                feedback.className = 'stock-alert-feedback success';
                feedback.style.display = 'block';
                feedback.textContent = result.message || 'Aviso cadastrado com sucesso!';
                btnSubmit.innerHTML = '<i class="fa-solid fa-check"></i> Cadastrado com Sucesso!';

                setTimeout(function () {
                    closeModal();
                }, 3000);
            } else {
                feedback.className = 'stock-alert-feedback error';
                feedback.style.display = 'block';
                feedback.textContent = result.message || 'Verifique as informações preenchidas.';
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Tentar Novamente';
            }
        } catch (err) {
            feedback.className = 'stock-alert-feedback error';
            feedback.style.display = 'block';
            feedback.textContent = 'Erro de comunicação com o servidor. Verifique sua conexão e tente novamente.';
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Tentar Novamente';
        }
    });
});
