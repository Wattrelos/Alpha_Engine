/**
 * Alpha Engine - Form Validator & Masking Utility
 * 
 * Centraliza e automatiza a formatação de máscaras em tempo real,
 * validações de senhas equivalentes e submissões AJAX de formulários.
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. MÁSCARAS E EVENTOS EM TEMPO REAL (DELEGADOS)

    document.addEventListener('input', (e) => {
        const target = e.target;

        // Máscara de Telefone: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
        if (target.name === 'telephone' || target.getAttribute('data-mask') === 'telephone') {
            let valor = target.value.replace(/\D/g, '');
            if (valor.length > 10) {
                target.value = valor.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (valor.length > 5) {
                target.value = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (valor.length > 2) {
                target.value = valor.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else {
                target.value = valor.replace(/^(\d*)/, '($1');
            }
        }

        // Máscara e Validação de CPF/CNPJ
        if (target.name === 'cpf_cnpj' || target.getAttribute('data-mask') === 'cpf_cnpj') {
            let valor = target.value.replace(/\D/g, '');
            let isCnpj = valor.length > 11;
            
            if (isCnpj) {
                target.value = valor.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2}).*/, '$1.$2.$3/$4-$5');
                validarCNPJ(valor);
            } else {
                target.value = valor.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2}).*/, '$1.$2.$3-$4');
                validarCPF(valor);
            }
        }
    });

    // Validar CPF
    function validarCPF(cpf) {
        const statusEl = document.getElementById('cpf_cnp-status') || document.getElementById('cpf-status');
        if (!statusEl) return;
        
        let valid = false;
        if (cpf.length === 11) {
            if (/^(?!^(\d)\1+$)\d{11}$/.test(cpf)) {
                let soma;
                for (let i = 9; i < 11; i++) {
                    soma = 0;
                    for (let j = 0; j < i; j++) {
                        soma += parseInt(cpf.charAt(j)) * ((i + 1) - j);
                    }
                    soma = ((10 * soma) % 11) % 10;
                    if (parseInt(cpf.charAt(i)) !== soma) {
                        valid = false;
                        break;
                    }
                    valid = true;
                }
            }
        }
        
        if (valid) {
            statusEl.textContent = '✔️';
            statusEl.style.color = 'green';
        } else {
            statusEl.textContent = cpf.length === 11 ? '❌' : '';
            statusEl.style.color = 'red';
        }
    }

    // Validar CNPJ
    function validarCNPJ(cnpj) {
        const statusEl = document.getElementById('cpf_cnp-status') || document.getElementById('cnpj-status');
        if (!statusEl) return;

        let valid = false;
        if (cnpj.length === 14) {
            if (/^(?!^(\d)\1+$)\d{14}$/.test(cnpj)) {
                let tamanho = cnpj.length - 2;
                let numeros = cnpj.substring(0, tamanho);
                let digitos = cnpj.substring(tamanho);
                let soma = 0;
                let pos = tamanho - 7;
                for (let i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2) pos = 9;
                }
                let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
                if (resultado == digitos.charAt(0)) {
                    tamanho = tamanho + 1;
                    numeros = cnpj.substring(0, tamanho);
                    soma = 0;
                    pos = tamanho - 7;
                    for (let i = tamanho; i >= 1; i--) {
                        soma += numeros.charAt(tamanho - i) * pos--;
                        if (pos < 2) pos = 9;
                    }
                    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
                    if (resultado == digitos.charAt(1)) {
                        valid = true;
                    }
                }
            }
        }

        if (valid) {
            statusEl.textContent = '✔️';
            statusEl.style.color = 'green';
        } else {
            statusEl.textContent = cnpj.length === 14 ? '❌' : '';
            statusEl.style.color = 'red';
        }
    }

    // 2. COMPATIBILIDADE DE SENHAS EM TEMPO REAL
    document.addEventListener('input', (e) => {
        const target = e.target;
        if (target.name === 'password' || target.name === 'confirm') {
            const form = target.closest('form');
            if (!form) return;
            const password = form.querySelector('input[name="password"]');
            const confirm = form.querySelector('input[name="confirm"]');
            const errorConfirm = form.querySelector('#error-confirm');
            
            if (password && confirm) {
                if (password.value === confirm.value) {
                    confirm.setCustomValidity('');
                    if (errorConfirm) {
                        errorConfirm.textContent = '';
                        confirm.classList.remove('is-invalid');
                    }
                } else {
                    confirm.setCustomValidity('As senhas não conferem.');
                    if (confirm.value.length > 0 && errorConfirm) {
                        errorConfirm.textContent = 'As senhas não conferem.';
                        confirm.classList.add('is-invalid');
                    }
                }
            }
        }
    });

    // 3. SUBMISSÃO DE FORMULÁRIO AJAX (data-oc-toggle="ajax")
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.getAttribute('data-oc-toggle') === 'ajax') {
            e.preventDefault();
            
            // Limpa avisos e classes CSS de erro anteriores
            form.querySelectorAll('.egen-invalid-feedback, .text-danger, [id^="error-"]').forEach(el => {
                el.textContent = '';
            });
            form.querySelectorAll('.is-invalid, .egen-form-input-error').forEach(el => {
                el.classList.remove('is-invalid', 'egen-form-input-error');
            });
            const globalAlert = document.getElementById('alert');
            if (globalAlert) {
                globalAlert.innerHTML = '';
            }

            // Exibir estado visual de carregamento no botão de envio
            const submitBtn = form.querySelector('button[type="submit"]');
            let originalBtnText = '';
            if (submitBtn) {
                originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Processando...';
            }

            // Prepara o FormData e injeta automaticamente os tokens CSRF das meta-tags se ausentes
            const formData = new FormData(form);
            const metaNameKey = document.querySelector('meta[name="csrf-key-name"]')?.content || 'csrf_name';
            const metaValueKey = document.querySelector('meta[name="csrf-key-value"]')?.content || 'csrf_value';
            const metaName = document.querySelector('meta[name="csrf-name"]')?.content;
            const metaValue = document.querySelector('meta[name="csrf-value"]')?.content;

            if (metaName && metaValue && !formData.has(metaNameKey)) {
                formData.append(metaNameKey, metaName);
                formData.append(metaValueKey, metaValue);
            }

            fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                return response.json().then(data => {
                    return { status: response.status, data: data };
                });
            })
            .then(({ status, data }) => {
                if (status === 200 && data.redirect) {
                    // Redireciona em caso de sucesso
                    const isAuthForm = form.action.includes('/login') || form.action.includes('/cadastro');
                    if (isAuthForm && typeof guestCart !== 'undefined' && guestCart.getItems().length > 0) {
                        const syncPayload = { items: guestCart.getItems() };
                        if (metaName && metaValue) {
                            syncPayload[metaNameKey] = metaName;
                            syncPayload[metaValueKey] = metaValue;
                        }
                        fetch('/api/carrinho/sincronizar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(syncPayload)
                        })
                        .then(res => res.json())
                        .then(syncData => {
                            if (syncData.success) {
                                guestCart.clear();
                            }
                            window.location.href = data.redirect;
                        })
                        .catch(err => {
                            console.error('Falha ao sincronizar carrinho:', err);
                            window.location.href = data.redirect;
                        });
                    } else {
                        window.location.href = data.redirect;
                    }
                } else {

                    // Reabilita o botão
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }

                    if (data.error) {
                        // Varre os erros enviados pelo backend (CustomerRepository, etc.) e os injeta nos campos
                        Object.keys(data.error).forEach(key => {
                            const errorMsg = data.error[key];
                            
                            // Erro geral do formulário (warning)
                            if (key === 'warning') {
                                if (globalAlert) {
                                    globalAlert.innerHTML = `
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fa-solid fa-circle-exclamation"></i> ${errorMsg}
                                        </div>`;
                                } else {
                                    alert(errorMsg);
                                }
                                return;
                            }

                            // Erros específicos de inputs individuais
                            const input = form.querySelector(`[name="${key}"]`);
                            const errorEl = form.querySelector(`#error-${key}`) || document.getElementById(`error-${key}`);
                            
                            if (input) {
                                input.classList.add('is-invalid', 'egen-form-input-error');
                            }
                            if (errorEl) {
                                errorEl.textContent = errorMsg;
                                errorEl.classList.add('d-block');
                            }
                        });
                    }
                }
            })
            .catch(err => {
                console.error('Falha ao processar formulário:', err);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        }
    });
});
