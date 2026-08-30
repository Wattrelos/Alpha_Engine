/**
 * Alpha Engine - Guest Checkout helper
 * 
 * Fornece métodos auxiliares para verificar o estado do carrinho de visitantes
 * antes e durante o processo de finalização de compras (checkout).
 */

const guestCheckout = {
    // Verifica se o visitante possui itens no carrinho local antes de prosseguir para o checkout
    checkCartBeforeCheckout() {
        const items = guestCart.getItems();
        if (items.length === 0) {
            console.warn('Checkout impedido: o carrinho do visitante está vazio.');
            return false;
        }
        return true;
    },

    // Retorna a lista de itens formatada para payload de checkout
    getCheckoutPayload() {
        return {
            items: guestCart.getItems(),
            timestamp: new Date().getTime()
        };
    },

    // Limpa o carrinho local após uma compra finalizada com sucesso
    clearGuestCartAfterOrder() {
        guestCart.clear();
        console.log('Carrinho do visitante limpo após confirmação do pedido.');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const logged = document.body.getAttribute('data-logged') === 'true';
    const path = window.location.pathname;

    // Se estiver na página de checkout e for visitante, sincroniza com o banco de dados
    if (path.includes('/checkout') && !logged) {
        const items = guestCart.getItems();
        if (items.length > 0) {
            // Sincroniza o carrinho local de visitante com a sessão do banco de dados
            fetch('/api/carrinho/sincronizar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ items: items })
            })
                .then(res => res.json())
                .then(syncData => {
                    if (syncData.success) {
                        console.log('Carrinho do visitante sincronizado com o banco de dados.');
                    }
                })
                .catch(err => {
                    console.error('Falha ao sincronizar carrinho do visitante no checkout:', err);
                });
        }
    }

    // ── ALTERNAR FORMULÁRIOS DA ETAPA 1 (IDENTIFICAÇÃO) ──
    const idOptions = document.querySelector('.egen-checkout-identity-options');
    const loginForm = document.getElementById('checkout-login-form');
    const registerForm = document.getElementById('checkout-register-form');

    if (idOptions && loginForm && registerForm) {
        idOptions.querySelectorAll('button[data-checkout-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                idOptions.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const action = btn.getAttribute('data-checkout-action');
                if (action === 'login') {
                    loginForm.classList.remove('egen-checkout-form-hidden');
                    registerForm.classList.add('egen-checkout-form-hidden');
                } else if (action === 'register') {
                    registerForm.classList.remove('egen-checkout-form-hidden');
                    loginForm.classList.add('egen-checkout-form-hidden');
                } else {
                    // Visitante
                    loginForm.classList.add('egen-checkout-form-hidden');
                    registerForm.classList.add('egen-checkout-form-hidden');
                }
            });
        });
    }

    // ── ALTERNAR FORMULÁRIOS DA ETAPA 2 (ENDEREÇO) ──
    const addrOptions = document.querySelector('.egen-checkout-address-options');
    const billingForm = document.getElementById('checkout-billing-address');
    const shippingForm = document.getElementById('checkout-shipping-address');

    if (addrOptions && billingForm && shippingForm) {
        addrOptions.querySelectorAll('button[data-checkout-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                addrOptions.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const action = btn.getAttribute('data-checkout-action');
                if (action === 'same-address') {
                    billingForm.classList.remove('egen-checkout-form-hidden');
                    shippingForm.classList.add('egen-checkout-form-hidden');
                } else if (action === 'different-address') {
                    billingForm.classList.remove('egen-checkout-form-hidden');
                    shippingForm.classList.remove('egen-checkout-form-hidden');
                }
            });
        });
    }

    // ── LOGICA DE LOGIN ASSÍNCRONO NO CHECKOUT ──
    const loginButton = document.getElementById('login-button');
    if (loginButton) {
        loginButton.addEventListener('click', () => {
            const emailInput = document.getElementById('login-email');
            const passwordInput = document.getElementById('login-password');
            const lang = document.body.getAttribute('data-lang') || 'pt-br';

            if (!emailInput || !passwordInput) return;

            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();

            if (!email || !password) {
                window.showNotification('Por favor, preencha todos os campos.', 'danger');
                return;
            }

            // Exibir loading
            const originalBtnHTML = loginButton.innerHTML;
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Entrando...';

            const metaNameKey = document.querySelector('meta[name="csrf-key-name"]')?.content || 'csrf_name';
            const metaValueKey = document.querySelector('meta[name="csrf-key-value"]')?.content || 'csrf_value';
            const metaName = document.querySelector('meta[name="csrf-name"]')?.content;
            const metaValue = document.querySelector('meta[name="csrf-value"]')?.content;

            const loginBody = new URLSearchParams({
                email: email,
                password: password,
                redirect: `/${lang}/checkout`
            });
            if (metaName && metaValue) {
                loginBody.append(metaNameKey, metaName);
                loginBody.append(metaValueKey, metaValue);
            }

            fetch(`/${lang}/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: loginBody
            })
                .then(res => res.json())
                .then(data => {
                    if (data.redirect) {
                        window.showNotification('Login realizado com sucesso! Redirecionando...', 'success');

                        // Sincroniza o carrinho local de visitante com o banco de dados antes de redirecionar
                        if (typeof guestCart !== 'undefined' && guestCart.getItems().length > 0) {
                            fetch('/api/carrinho/sincronizar', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ items: guestCart.getItems() })
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
                    } else if (data.error) {
                        window.showNotification(data.error, 'danger');
                        loginButton.disabled = false;
                        loginButton.innerHTML = originalBtnHTML;
                    } else {
                        window.showNotification('Erro inesperado ao realizar login.', 'danger');
                        loginButton.disabled = false;
                        loginButton.innerHTML = originalBtnHTML;
                    }
                })
                .catch(err => {
                    console.error('Erro na requisição de login:', err);
                    window.showNotification('Erro de rede ou servidor. Tente novamente.', 'danger');
                    loginButton.disabled = false;
                    loginButton.innerHTML = originalBtnHTML;
                });
        });
    }

    // Inicia os inputs de endereço com readonly se tiverem valor no carregamento
    ['input-payment-address-1', 'input-payment-neighborhood', 'input-payment-city', 'input-payment-zone',
     'input-shipping-address-1', 'input-shipping-neighborhood', 'input-shipping-city', 'input-shipping-zone'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el.value.trim() !== '' && el.value.trim() !== '--- Selecione ---' && el.value.trim() !== 'Selecione') {
            el.setAttribute('readonly', 'readonly');
        }
    });

    // ── PREENCHIMENTO AUTOMÁTICO DE CEP (ViaCEP) ──
    function setupCepAutocomplete(postcodeId, address1Id, neighborhoodId, cityId, zoneInputId) {
        const postcodeEl = document.getElementById(postcodeId);
        if (!postcodeEl) return;

        let lastSearched = '';

        const searchCep = (rawCep) => {
            if (rawCep.length !== 8 || rawCep === lastSearched) return;
            lastSearched = rawCep;

            fetch(`https://viacep.com.br/ws/${rawCep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        const setFieldVal = (id, val) => {
                            const el = document.getElementById(id);
                            if (!el) return;
                            el.value = val || '';
                            if (val) {
                                el.setAttribute('readonly', 'readonly');
                            } else {
                                el.removeAttribute('readonly');
                            }
                        };

                        setFieldVal(address1Id, data.logradouro);
                        setFieldVal(neighborhoodId, data.bairro);
                        setFieldVal(cityId, data.localidade);
                        setFieldVal(zoneInputId, data.uf);
                    } else {
                        if (typeof window.showNotification === 'function') {
                            window.showNotification('CEP não encontrado.', 'danger');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    if (typeof window.showNotification === 'function') {
                        window.showNotification('Erro ao consultar ViaCEP.', 'danger');
                    }
                });
        };

        postcodeEl.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 8);
            if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
            this.value = v;

            const clean = v.replace(/\D/g, '');
            if (clean.length === 8) {
                searchCep(clean);
            }
        });

        postcodeEl.addEventListener('blur', function () {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                searchCep(cep);
            }
        });
    }

    // Configura autocompletes
    setupCepAutocomplete('input-payment-postcode', 'input-payment-address-1', 'input-payment-neighborhood', 'input-payment-city', 'input-payment-zone');
    setupCepAutocomplete('input-shipping-postcode', 'input-shipping-address-1', 'input-shipping-neighborhood', 'input-shipping-city', 'input-shipping-zone');

    // ── LÓGICA DO CHECKOUT EM ETAPAS (MULTI-STEP FLOW) ──
    let currentStep = 1;
    // Se o cliente já estiver logado, começamos diretamente na etapa 2
    if (logged) {
        currentStep = 2;
        // Marcar a etapa 1 como concluída
        const step1Progress = document.querySelector('[data-progress-step="1"]');
        if (step1Progress) {
            step1Progress.classList.remove('active');
            step1Progress.classList.add('completed');
        }
        const step2Progress = document.querySelector('[data-progress-step="2"]');
        if (step2Progress) {
            step2Progress.classList.add('active');
        }
        const step1Content = document.getElementById('step-identity');
        if (step1Content) {
            step1Content.classList.remove('active');
        }
        const step2Content = document.getElementById('step-billing');
        if (step2Content) {
            step2Content.classList.add('active');
        }
    }

    // Gerencia o clique nos botões "Avançar" e "Voltar"
    document.querySelectorAll('[data-checkout-nav]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const direction = btn.getAttribute('data-checkout-nav');
            
            if (direction === 'next') {
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processando...';

                try {
                    // Transforma a validação em assíncrona (Aguardando o Backend)
                    const isValid = await validateStepAsync(currentStep);
                    if (isValid) navigateNext();
                } catch (error) {
                    console.error("Erro na transição de etapa:", error);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } else if (direction === 'prev') {
                navigatePrev();
            }
        });
    });

    // Controla se a entrega é diferente da cobrança
    let differentShippingAddress = false;
    const sameAddrBtn = document.querySelector('[data-checkout-action="same-address"]');
    const diffAddrBtn = document.querySelector('[data-checkout-action="different-address"]');

    if (sameAddrBtn) {
        // Inicializa com Same Address por padrão
        sameAddrBtn.classList.add('active');
        if (billingForm) {
            billingForm.classList.remove('egen-checkout-form-hidden');
        }
    }

    // Adiciona listeners para atualizar o estado de entrega
    document.querySelectorAll('[data-checkout-action]').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-checkout-action');
            if (action === 'same-address') {
                differentShippingAddress = false;
            } else if (action === 'different-address') {
                differentShippingAddress = true;
            }
        });
    });

    // ── ALTERNAR MÉTODOS DE PAGAMENTO (ETAPA 4) ──
    const paymentCards = document.querySelectorAll('.egen-payment-method-card');
    if (paymentCards.length > 0) {
        paymentCards.forEach(card => {
            const radio = card.querySelector('.egen-payment-method-radio');
            
            card.addEventListener('click', function(e) {
                if (e.target !== radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            radio.addEventListener('change', function() {
                paymentCards.forEach(c => c.classList.remove('active'));
                if (radio.checked) {
                    card.classList.add('active');
                }
            });
        });
    }

    async function validateStepAsync(step) {
        let valid = true;
        const activeStepEl = document.querySelector(`.egen-checkout-step[data-step="${step}"]`);
        if (!activeStepEl) return true;

        // Se for etapa 1, valida apenas se uma das opções foi escolhida e se o formulário visível está correto
        if (step === 1) {
            const activeIdentityBtn = document.querySelector('.egen-checkout-identity-options button.active');
            if (!activeIdentityBtn) {
                window.showNotification('Selecione uma opção de identificação para continuar.', 'danger');
                return false;
            }

            const action = activeIdentityBtn.getAttribute('data-checkout-action');
            if (action === 'login') {
                window.showNotification('Por favor, clique no botão para se autenticar ou escolha outra opção.', 'danger');
                return false;
            }

            if (action === 'register') {
                const registerForm = document.getElementById('checkout-register-form');
                const inputs = registerForm.querySelectorAll('input[data-required="true"], input[required]');
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                // Valida senhas iguais
                const password = document.getElementById('register-password');
                const confirm = document.getElementById('register-confirm');
                if (password && confirm && password.value !== confirm.value) {
                    window.showNotification('As senhas não coincidem.', 'danger');
                    confirm.classList.add('is-invalid');
                    valid = false;
                }

                if (!valid) {
                    window.showNotification('Por favor, preencha todos os campos obrigatórios de cadastro.', 'danger');
                    return false;
                }

                // Se já efetuou o cadastro e autenticou nesta sessão, prossegue diretamente
                if (window.checkoutCustomerRegistered) {
                    return true;
                }

                // Submete o cadastro via AJAX com Auto-Login
                const metaNameKey = document.querySelector('meta[name="csrf-key-name"]')?.content || 'csrf_name';
                const metaValueKey = document.querySelector('meta[name="csrf-key-value"]')?.content || 'csrf_value';
                const metaName = document.querySelector('meta[name="csrf-name"]')?.content;
                const metaValue = document.querySelector('meta[name="csrf-value"]')?.content;
                const lang = document.body.getAttribute('data-lang') || 'pt-br';

                const registerPayload = new URLSearchParams({
                    firstname: document.getElementById('register-firstname')?.value.trim() || '',
                    lastname: document.getElementById('register-lastname')?.value.trim() || '',
                    email: document.getElementById('register-email')?.value.trim() || '',
                    telephone: document.getElementById('register-telephone')?.value.trim() || '',
                    password: password ? password.value : '',
                    confirm: confirm ? confirm.value : '',
                    agree: '1',
                    redirect: `/${lang}/checkout`
                });

                if (metaName && metaValue) {
                    registerPayload.append(metaNameKey, metaName);
                    registerPayload.append(metaValueKey, metaValue);
                }

                try {
                    const res = await fetch(`/${lang}/cadastro`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: registerPayload
                    });

                    const resData = await res.json();

                    if (res.ok && resData.success) {
                        window.checkoutCustomerRegistered = true;
                        window.showNotification('Cadastro realizado com sucesso! Sessão iniciada.', 'success');

                        // Atualiza estado na página
                        document.body.setAttribute('data-logged', 'true');

                        // Atualiza tokens CSRF se retornados pelo backend
                        if (resData.csrf && resData.csrf.name && resData.csrf.value) {
                            const metaNameEl = document.querySelector('meta[name="csrf-name"]');
                            const metaValEl = document.querySelector('meta[name="csrf-value"]');
                            if (metaNameEl) metaNameEl.setAttribute('content', resData.csrf.name);
                            if (metaValEl) metaValEl.setAttribute('content', resData.csrf.value);
                            document.querySelectorAll('input[name="csrf_name"]').forEach(el => el.value = resData.csrf.name);
                            document.querySelectorAll('input[name="csrf_value"]').forEach(el => el.value = resData.csrf.value);
                        }

                        // Preenche automaticamente os dados nos passos seguintes se estiverem vazios
                        const fn = document.getElementById('register-firstname')?.value.trim() || '';
                        const ln = document.getElementById('register-lastname')?.value.trim() || '';
                        const payFn = document.getElementById('input-payment-firstname');
                        const payLn = document.getElementById('input-payment-lastname');
                        if (payFn && !payFn.value) payFn.value = fn;
                        if (payLn && !payLn.value) payLn.value = ln;

                        // Sincroniza o carrinho local com o banco de dados
                        if (typeof guestCart !== 'undefined' && guestCart.getItems().length > 0) {
                            try {
                                const syncPayload = { items: guestCart.getItems() };
                                if (metaName && metaValue) {
                                    syncPayload[metaNameKey] = metaName;
                                    syncPayload[metaValueKey] = metaValue;
                                }
                                await fetch('/api/carrinho/sincronizar', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify(syncPayload)
                                });
                                guestCart.clear();
                            } catch (e) {
                                console.error('Erro ao sincronizar carrinho:', e);
                            }
                        }

                        return true;
                    } else {
                        let errMsg = 'Erro ao realizar cadastro.';
                        if (resData.error) {
                            if (typeof resData.error === 'string') {
                                errMsg = resData.error;
                            } else if (typeof resData.error === 'object') {
                                const firstKey = Object.keys(resData.error)[0];
                                errMsg = resData.error[firstKey] || errMsg;
                                Object.keys(resData.error).forEach(field => {
                                    const input = document.getElementById(`register-${field}`) || document.querySelector(`[name="register[${field}]"]`);
                                    if (input) input.classList.add('is-invalid');
                                });
                            }
                        }
                        window.showNotification(errMsg, 'danger');
                        return false;
                    }
                } catch (err) {
                    console.error('Erro na requisição de cadastro do checkout:', err);
                    window.showNotification('Erro de rede ou servidor ao efetuar cadastro.', 'danger');
                    return false;
                }
            }

            // Se action for 'guest', verifica se é permitido
            if (action === 'guest') {
                const allowGuest = idOptions?.getAttribute('data-allow-guest') !== 'false';
                if (!allowGuest) {
                    window.showNotification('Compras como visitante estão desabilitadas nesta loja. Por favor, faça login ou cadastre-se.', 'danger');
                    return false;
                }
            }
        }

        // Se for etapa 2, valida os campos de cobrança
        if (step === 2) {
            const billingForm = document.getElementById('checkout-billing-address');
            // Só valida se o formulário de cobrança estiver visível
            if (billingForm && !billingForm.classList.contains('egen-checkout-form-hidden')) {
                const inputs = billingForm.querySelectorAll('input[data-required="true"], select[data-required="true"], input[required], select[required]');
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                if (!valid) {
                    window.showNotification('Por favor, preencha todos os campos obrigatórios do endereço de cobrança.', 'danger');
                    return false;
                }
                
                // TODO: Chamada AJAX para salvar endereço na sessão do OpenCart e buscar métodos de frete
                /* const formData = new FormData(billingForm);
                   const res = await fetch('/api/checkout/save-billing', { method: 'POST', body: formData });
                   if (!res.ok) { window.showNotification('Erro ao salvar endereço.', 'danger'); return false; } */
            } else {
                window.showNotification('Escolha se deseja entregar no mesmo endereço ou em outro.', 'danger');
                return false;
            }
        }

        // Se for etapa 3, valida os campos de entrega se estiver visível
        if (step === 3) {
            const shippingForm = document.getElementById('checkout-shipping-address');
            if (shippingForm && !shippingForm.classList.contains('egen-checkout-form-hidden')) {
                const inputs = shippingForm.querySelectorAll('input[data-required="true"], select[data-required="true"], input[required], select[required]');
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                if (!valid) {
                    window.showNotification('Por favor, preencha todos os campos obrigatórios do endereço de entrega.', 'danger');
                    return false;
                }
                
                // TODO: Chamada AJAX para buscar/confirmar métodos de Frete antes do Pagamento
                /* const res = await fetch('/api/checkout/get-shipping-methods');
                   // renderizar HTML de frete dinamicamente... */
            }
        }

        return valid;
    }

    function navigateNext() {
        if (currentStep === 1) {
            goToStep(2);
        } else if (currentStep === 2) {
            if (differentShippingAddress) {
                goToStep(3);
            } else {
                goToStep(4);
            }
        } else if (currentStep === 3) {
            goToStep(4);
        }
    }

    function navigatePrev() {
        if (currentStep === 4) {
            if (differentShippingAddress) {
                goToStep(3);
            } else {
                goToStep(2);
            }
        } else if (currentStep === 3) {
            goToStep(2);
        } else if (currentStep === 2) {
            if (!logged) {
                goToStep(1);
            }
        }
    }

    function goToStep(stepNumber) {
        document.querySelectorAll('.egen-checkout-step').forEach(el => el.classList.remove('active'));

        const nextStepEl = document.querySelector(`.egen-checkout-step[data-step="${stepNumber}"]`);
        if (nextStepEl) {
            nextStepEl.classList.add('active');
        }

        document.querySelectorAll('.egen-checkout-progress__step').forEach(stepEl => {
            const stepIndex = parseInt(stepEl.getAttribute('data-progress-step'));
            stepEl.classList.remove('active', 'completed');
            if (stepIndex === stepNumber) {
                stepEl.classList.add('active');
            } else if (stepIndex < stepNumber) {
                stepEl.classList.add('completed');
            }
        });

        currentStep = stepNumber;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
