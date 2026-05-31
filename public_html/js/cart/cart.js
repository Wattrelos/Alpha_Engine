/**
 * Alpha Engine - Guest Shopping Cart Controller (LocalStorage)
 * 
 * Gerencia o ciclo de vida do carrinho de compras de clientes não logados (anônimos),
 * interceptando submissões de adição, controlando a quantidade, exclusões e a renderização
 * dinâmica da página de carrinho e do contador de cabeçalho.
 */

const guestCart = {
    getKey() {
        return 'egen_cart';
    },

    getItems() {
        try {
            return JSON.parse(localStorage.getItem(this.getKey())) || [];
        } catch (e) {
            console.error('Erro ao ler egen_cart do localStorage', e);
            return [];
        }
    },

    saveItems(items) {
        localStorage.setItem(this.getKey(), JSON.stringify(items));
        this.updateHeaderCount();
        window.dispatchEvent(new CustomEvent('egen-cart-updated', { detail: items }));
    },

    add(productId, quantity, options) {
        const items = this.getItems();
        const optionStr = JSON.stringify(options || {});

        let found = false;
        for (let item of items) {
            const itemOptStr = JSON.stringify(item.option || {});
            if (item.product_id === productId && itemOptStr === optionStr) {
                item.quantity += quantity;
                found = true;
                break;
            }
        }

        if (!found) {
            items.push({
                product_id: productId,
                quantity: quantity,
                option: options || {}
            });
        }

        this.saveItems(items);
    },

    remove(productId, optionString) {
        let items = this.getItems();
        items = items.filter(item => {
            const itemOptStr = JSON.stringify(item.option || {});
            return !(item.product_id === productId && itemOptStr === optionString);
        });
        this.saveItems(items);
    },

    updateQuantity(productId, optionString, quantity) {
        let items = this.getItems();
        for (let item of items) {
            const itemOptStr = JSON.stringify(item.option || {});
            if (item.product_id === productId && itemOptStr === optionString) {
                item.quantity = Math.max(1, quantity);
                break;
            }
        }
        this.saveItems(items);
    },

    clear() {
        this.saveItems([]);
    },

    count() {
        return this.getItems().reduce((acc, item) => acc + item.quantity, 0);
    },

    updateHeaderCount() {
        const num = this.count();
        const badges = document.querySelectorAll('.egen-cart-badge');
        badges.forEach(badge => {
            badge.textContent = num;
            badge.style.display = num > 0 ? 'inline-flex' : 'none';
        });
    }
};

// Exibe alertas flutuantes elegantes na página
function showCartAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert');
    if (alertContainer) {
        alertContainer.innerHTML = `
            <div class="egen-alert egen-alert--${type}" style="margin-top: 1rem; animation: slideDown 0.3s ease-out;">
                <i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i> 
                ${message}
            </div>
        `;
        // Auto-remove após 4 segundos
        setTimeout(() => {
            alertContainer.innerHTML = '';
        }, 4000);
    } else {
        alert(message);
    }
}

// ─────────────────────────────────────────────────────────
// EVENT LISTENERS E RENDERIZAÇÃO
// ─────────────────────────────────────────────────────────

function initCartSystem() {
    const logged = document.body.getAttribute('data-logged') === 'true';

    // 1. Atualizar o badge inicial do cabeçalho
    if (!logged) {
        guestCart.updateHeaderCount();
    }

    // 2. Interceptar a submissão de formulários de adição ao carrinho para Visitantes ou formulários específicos de listagem (como prod-card-form)
    document.addEventListener('submit', (e) => {
        const form = e.target;
        const action = form.getAttribute('action') || '';
        
        if (action.includes('/carrinho/adicionar')) {
            const isProdCard = form.classList.contains('prod-card-form');
            const isProductPurchase = form.classList.contains('egen-product-purchase-form');
            
            // Intercepta se for visitante OU se for um formulário de listagem (prod-card-form) OU de compra de produto (egen-product-purchase-form)
            if (!logged || isProdCard || isProductPurchase) {
                e.preventDefault();
                e.stopPropagation();

                const formData = new FormData(form);
                const productId = parseInt(formData.get('product_id'));
                const quantity = parseInt(formData.get('quantity') || '1');

                if (!productId) return;

                if (!logged) {
                    // Extrai opções dinâmicas
                    const options = {};
                    for (let [key, val] of formData.entries()) {
                        if (key.startsWith('option[')) {
                            const match = key.match(/option\[(\d+)\]/);
                            if (match) {
                                const optId = match[1];
                                if (key.endsWith('[]')) {
                                    if (!options[optId]) options[optId] = [];
                                    options[optId].push(val);
                                } else {
                                    options[optId] = val;
                                }
                            }
                        }
                    }

                    guestCart.add(productId, quantity, options);
                    showCartAlert('Produto adicionado ao carrinho com sucesso!', 'success');
                } else {
                    // Usuário logado, mas veio de um form de listagem (permanece na página)
                    fetch(action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Atualiza os contadores do mini-cart/header badge localmente
                            const badges = document.querySelectorAll('.egen-cart-badge');
                            badges.forEach(badge => {
                                let currentCount = parseInt(badge.textContent || '0');
                                let newCount = currentCount + quantity;
                                badge.textContent = newCount;
                                badge.style.display = newCount > 0 ? 'inline-flex' : 'none';
                            });
                            showCartAlert('Produto adicionado ao carrinho com sucesso!', 'success');
                        } else if (data.error) {
                            showCartAlert(data.error.warning || 'Erro ao adicionar o produto.', 'danger');
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao adicionar produto ao carrinho:', err);
                        showCartAlert('Erro de rede ou servidor ao adicionar o produto.', 'danger');
                    });
                }
            }
        }
    });

    // 3. Renderização Dinâmica da Página de Carrinho (/carrinho) para Visitantes
    const path = window.location.pathname;
    if (path.includes('/carrinho') && !logged) {
        renderGuestCartPage();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCartSystem);
} else {
    initCartSystem();
}

// Renderiza dinamicamente a estrutura e tabelas do carrinho no DOM
function renderGuestCartPage() {
    const container = document.querySelector('.egen-cart-container');
    if (!container) return;

    const items = guestCart.getItems();

    if (items.length === 0) {
        container.innerHTML = `
            <h1 class="egen-cart-title">Carrinho de Compras</h1>
            <div class="egen-cart-empty">
                <div class="egen-cart-empty__icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <p class="egen-cart-empty__text">Seu carrinho de compras está vazio!</p>
                <a href="/" class="egen-btn-primary">Continuar</a>
            </div>
        `;
        return;
    }

    // Exibe loading
    container.innerHTML = `
        <h1 class="egen-cart-title">Carrinho de Compras</h1>
        <div style="text-align: center; padding: 3rem; color: var(--egen-text-muted);">
            <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--egen-primary-color); margin-bottom: 1rem;"></i>
            <p>Carregando itens do seu carrinho...</p>
        </div>
    `;

    // Busca detalhes dos produtos na base pelo novo endpoint da API
    fetch('/api/carrinho/dados', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ items: items })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success || data.products.length === 0) {
            guestCart.clear();
            renderGuestCartPage();
            return;
        }

        // Reconstrói a página do carrinho utilizando o retorno hidratado da API
        let productsRows = '';
        data.products.forEach(p => {
            let optionsHTML = '';
            if (p.option && p.option.length > 0) {
                optionsHTML = '<div class="egen-cart-options">';
                p.option.forEach(opt => {
                    optionsHTML += `<small class="egen-cart-option-item"><strong>${opt.name}:</strong> ${opt.value}</small>`;
                });
                optionsHTML += '</div>';
            }

            productsRows += `
                <tr data-product-id="${p.product_id}" data-option-raw='${p.option_raw}'>
                    <td class="text-center egen-cart-table__image-col">
                        <a href="${p.href}">
                            <img src="${p.thumb}" alt="${p.name}" title="${p.name}" class="egen-cart-img" />
                        </a>
                    </td>
                    <td class="egen-cart-table__info-col">
                        <a href="${p.href}" class="egen-cart-product-name">${p.name}</a>
                        ${optionsHTML}
                    </td>
                    <td>${p.model}</td>
                    <td class="text-center">
                        <div class="egen-cart-qty-wrapper">
                            <input type="number" value="${p.quantity}" min="1" class="egen-form-input egen-cart-qty-input guest-qty-input" />
                            <div class="egen-cart-actions-btn">
                                <button type="button" title="Atualizar" class="egen-btn-icon egen-btn-icon--update guest-btn-update">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                                <button type="button" title="Remover" class="egen-btn-icon egen-btn-icon--remove guest-btn-remove">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="text-right">${p.price}</td>
                    <td class="text-right font-weight-bold">${p.total}</td>
                </tr>
            `;
        });

        let summaryRows = '';
        data.totals.forEach(t => {
            const isGrand = t.title.toLowerCase() === 'total';
            summaryRows += `
                <div class="egen-cart-total-row ${isGrand ? 'egen-cart-total-row--grand' : ''}">
                    <span class="egen-cart-total-label">${t.title}</span>
                    <span class="egen-cart-total-value">${t.text}</span>
                </div>
            `;
        });

        container.innerHTML = `
            <h1 class="egen-cart-title">Carrinho de Compras</h1>
            <div class="egen-cart-layout">
                <!-- Tabela de Produtos -->
                <div class="egen-cart-main">
                    <div class="egen-table-responsive">
                        <table class="egen-cart-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Imagem</th>
                                    <th>Produto</th>
                                    <th>Modelo</th>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-right">Preço Unitário</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${productsRows}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Resumo Lateral -->
                <div class="egen-cart-sidebar">
                    <div class="egen-cart-summary-card">
                        <h2 class="egen-cart-summary-title">Resumo do Pedido</h2>
                        <div class="egen-cart-totals-list">
                            ${summaryRows}
                        </div>
                        <div class="egen-cart-checkout-actions">
                            <a href="/checkout" class="egen-btn-primary egen-btn-checkout">
                                Finalizar Compra <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="/" class="egen-btn-secondary egen-btn-continue">
                                <i class="fa-solid fa-arrow-left"></i> Continuar Comprando
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Ativa os listeners nos novos botões injetados
        attachGuestCartActions();
    })
    .catch(err => {
        console.error('Erro ao processar dados do carrinho do visitante:', err);
        container.innerHTML = `
            <h1 class="egen-cart-title">Carrinho de Compras</h1>
            <div class="egen-alert egen-alert--danger">
                <i class="fa-solid fa-circle-exclamation"></i> Falha ao carregar o carrinho. Por favor, tente novamente mais tarde.
            </div>
        `;
    });
}

// Vincula ações de clique e mudança de quantidade no DOM dinâmico do visitante
function attachGuestCartActions() {
    // 1. Botão de Remover Item
    document.querySelectorAll('.guest-btn-remove').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            if (tr) {
                const productId = parseInt(tr.getAttribute('data-product-id'));
                const optionRaw = tr.getAttribute('data-option-raw');
                
                guestCart.remove(productId, optionRaw);
                showCartAlert('Item removido do carrinho.', 'success');
                renderGuestCartPage();
            }
        });
    });

    // 2. Botão de Atualizar Quantidade
    document.querySelectorAll('.guest-btn-update').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            if (tr) {
                const productId = parseInt(tr.getAttribute('data-product-id'));
                const optionRaw = tr.getAttribute('data-option-raw');
                const qtyInput = tr.querySelector('.guest-qty-input');
                const quantity = parseInt(qtyInput ? qtyInput.value : '1');

                guestCart.updateQuantity(productId, optionRaw, quantity);
                showCartAlert('Quantidade atualizada.', 'success');
                renderGuestCartPage();
            }
        });
    });
}
