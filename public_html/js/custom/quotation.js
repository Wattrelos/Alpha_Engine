/**
 * Alpha Engine - Quotation & Material Takeoff Tool (RF036 / RF037)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Detecta idioma ativo da rota (ex: pt-br, en, es)
    const langMatch = window.location.pathname.match(/^\/(pt-br|en|es)/);
    const currentLang = langMatch ? langMatch[1] : 'pt-br';

    // ── 1. Autocomplete de SKUs do Catálogo na ferramenta Takeoff ──
    const skuSearchInput = document.getElementById('sku-search-input');
    const skuResultsDropdown = document.getElementById('sku-results-dropdown');
    const selectedProductIdInput = document.getElementById('selected-product-id');
    const itemNameInput = document.getElementById('item-name-input');
    const unitPriceInput = document.getElementById('unit-price-input');
    const quantityInput = document.getElementById('quantity-input');
    const addItemBtn = document.getElementById('btn-add-takeoff-item');
    const takeoffTableBody = document.getElementById('takeoff-table-body');
    const finalizeBoqBtn = document.getElementById('btn-finalize-boq');
    const uploadSpreadsheetBtn = document.getElementById('btn-upload-spreadsheet');
    const spreadsheetFileInput = document.getElementById('spreadsheet-file-input');
    const importStatusMessage = document.getElementById('import-status-message');

    let debounceTimer = null;

    if (skuSearchInput && skuResultsDropdown) {
        skuSearchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                skuResultsDropdown.style.display = 'none';
                skuResultsDropdown.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/api/produtos/buscar-takeoff?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            skuResultsDropdown.innerHTML = '';
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'sku-item';
                                div.innerHTML = `
                                    <div>
                                        <strong>${item.name}</strong>
                                        <small style="display:block; color:#64748b;">SKU: ${item.model || item.id}</small>
                                    </div>
                                    <div style="font-weight:600; color:#ff6b00;">${item.formatted_price}</div>
                                `;
                                div.addEventListener('click', () => {
                                    if (selectedProductIdInput) selectedProductIdInput.value = item.id;
                                    if (itemNameInput) itemNameInput.value = item.name;
                                    if (unitPriceInput) unitPriceInput.value = item.price;
                                    skuResultsDropdown.style.display = 'none';
                                    skuSearchInput.value = item.name;
                                });
                                skuResultsDropdown.appendChild(div);
                            });
                            skuResultsDropdown.style.display = 'block';
                        } else {
                            skuResultsDropdown.style.display = 'none';
                        }
                    })
                    .catch(() => {
                        skuResultsDropdown.style.display = 'none';
                    });
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!skuSearchInput.contains(e.target) && !skuResultsDropdown.contains(e.target)) {
                skuResultsDropdown.style.display = 'none';
            }
        });
    }

    // ── 2. Importação de Planilhas em Lote (CSV / TSV) ──
    if (uploadSpreadsheetBtn && spreadsheetFileInput) {
        uploadSpreadsheetBtn.addEventListener('click', () => {
            if (!spreadsheetFileInput.files || spreadsheetFileInput.files.length === 0) {
                alert('Por favor, selecione um arquivo de planilha (.csv ou .tsv) primeiro.');
                return;
            }

            const file = spreadsheetFileInput.files[0];
            const formData = new FormData();
            formData.append('action', 'import_spreadsheet');
            formData.append('spreadsheet_file', file);

            uploadSpreadsheetBtn.disabled = true;
            uploadSpreadsheetBtn.innerHTML = '<span>⏳</span> Processando...';

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                uploadSpreadsheetBtn.disabled = false;
                uploadSpreadsheetBtn.innerHTML = '<span>⬆️</span> Processar Planilha';

                if (data.success) {
                    if (importStatusMessage) {
                        importStatusMessage.style.display = 'block';
                        importStatusMessage.style.background = '#ecfdf5';
                        importStatusMessage.style.border = '1px solid #10b981';
                        importStatusMessage.style.color = '#065f46';
                        importStatusMessage.innerHTML = `✓ Sucesso! <strong>${data.imported_count}</strong> itens importados da planilha. Atualizando...`;
                    }
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    if (importStatusMessage) {
                        importStatusMessage.style.display = 'block';
                        importStatusMessage.style.background = '#fee2e2';
                        importStatusMessage.style.border = '1px solid #ef4444';
                        importStatusMessage.style.color = '#991b1b';
                        const errs = (data.errors || ['Erro ao processar planilha']).join('<br>');
                        importStatusMessage.innerHTML = `❌ ${errs}`;
                    }
                }
            })
            .catch(err => {
                uploadSpreadsheetBtn.disabled = false;
                uploadSpreadsheetBtn.innerHTML = '<span>⬆️</span> Processar Planilha';
                console.error(err);
                alert('Erro de comunicação ao enviar arquivo.');
            });
        });
    }

    // ── 3. Adicionar Item ao BoQ Manualmente via AJAX ──
    if (addItemBtn && takeoffTableBody) {
        addItemBtn.addEventListener('click', () => {
            const itemName = itemNameInput ? itemNameInput.value.trim() : '';
            const unit = document.getElementById('unit-select') ? document.getElementById('unit-select').value : 'un';
            const quantity = quantityInput ? parseFloat(quantityInput.value) : 1;
            const unitPrice = unitPriceInput ? parseFloat(unitPriceInput.value) : 0;
            const productId = selectedProductIdInput ? selectedProductIdInput.value : '';
            const notes = document.getElementById('item-notes-input') ? document.getElementById('item-notes-input').value : '';

            if (!itemName || isNaN(quantity) || quantity <= 0) {
                alert('Por favor, informe a descrição do material e a quantidade válida.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_item');
            formData.append('item_name', itemName);
            formData.append('unit', unit);
            formData.append('quantity', quantity);
            formData.append('unit_price', unitPrice);
            formData.append('product_id', productId);
            formData.append('notes', notes);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erro ao adicionar item ao BoQ.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de comunicação com o servidor.');
            });
        });
    }

    // ── 4. Remover Item do BoQ ──
    document.querySelectorAll('.btn-remove-takeoff-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const itemId = e.currentTarget.getAttribute('data-item-id');
            if (!confirm('Deseja remover este material da lista?')) return;

            const formData = new FormData();
            formData.append('action', 'remove_item');
            formData.append('item_id', itemId);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });
    });

    // ── 5. Finalizar e Submeter BoQ ao Cliente ──
    if (finalizeBoqBtn) {
        finalizeBoqBtn.addEventListener('click', () => {
            if (!confirm('Deseja finalizar o levantamento de materiais e disponibilizar para aprovação do cliente?')) return;

            const formData = new FormData();
            formData.append('action', 'finalize_boq');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'BoQ enviado com sucesso!');
                    window.location.href = window.location.pathname.replace('/takeoff', '/proposta');
                }
            });
        });
    }

    // ── 6. Botão "Adicionar ao Orçamento" na PDP e Cards de Produto ──
    document.querySelectorAll('.egen-add-to-quote-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = btn.getAttribute('data-product-id');
            const productName = btn.getAttribute('data-product-name') || 'Produto Selecionado';
            const qtyInput = document.getElementById('input-quantity');
            const quantity = qtyInput ? (parseFloat(qtyInput.value) || 1) : 1;

            // Busca projetos do cliente no endpoint resiliente
            const fetchUrl = `/${currentLang}/api/projetos/meus-projetos`;

            fetch(fetchUrl)
                .then(async (res) => {
                    if (!res.ok) {
                        const fallbackRes = await fetch('/api/projetos/meus-projetos');
                        return fallbackRes.json();
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data.logged) {
                        alert('Para adicionar itens a um orçamento, por favor faça login na sua conta.');
                        window.location.href = data.login_url || `/${currentLang}/login`;
                        return;
                    }

                    if (!data.projects || data.projects.length === 0) {
                        // Sem projetos: redireciona para novo projeto
                        if (confirm(`Você ainda não possui um projeto de obra cadastrado. Deseja criar um novo projeto com o item "${productName}"?`)) {
                            window.location.href = `/${currentLang}/projetos/novo?pre_product_id=${productId}&qty=${quantity}`;
                        }
                        return;
                    }

                    // Exibe Modal de Escolha do Projeto
                    showQuoteSelectionModal(productId, productName, quantity, data.projects);
                })
                .catch(err => {
                    console.error('Erro na requisição de projetos:', err);
                    alert('Erro ao carregar seus projetos. Verifique se está autenticado na sua conta.');
                });
        });
    });

    function showQuoteSelectionModal(productId, productName, quantity, projects) {
        let existingModal = document.getElementById('egen-quote-modal-wrapper');
        if (existingModal) existingModal.remove();

        const modalOverlay = document.createElement('div');
        modalOverlay.id = 'egen-quote-modal-wrapper';
        modalOverlay.className = 'egen-quote-modal-overlay';

        let projectsHtml = '';
        projects.forEach(p => {
            projectsHtml += `
                <div class="egen-quote-project-item" data-project-id="${p.id}">
                    <div>
                        <strong style="color:#1e293b; display:block;">${p.title}</strong>
                        <small style="color:#64748b;">📍 ${p.city} - ${p.state} | Status: ${p.status}</small>
                    </div>
                    <span class="rfq-badge rfq-badge--open">Selecionar</span>
                </div>
            `;
        });

        modalOverlay.innerHTML = `
            <div class="egen-quote-modal">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid #e2e8f0; padding-bottom:0.75rem;">
                    <h3 class="egen-quote-modal-title">
                        <span>📋</span> Adicionar ao Orçamento
                    </h3>
                    <button type="button" id="close-quote-modal" style="background:none; border:none; font-size:1.5rem; color:#64748b; cursor:pointer;">&times;</button>
                </div>
                <p style="color:#475569; font-size:0.95rem; margin-bottom:1.25rem;">
                    Item: <strong>${productName}</strong> (Quantidade: <strong>${quantity}</strong>)
                </p>
                <h4 style="font-size:0.95rem; color:#1e293b; margin-bottom:0.75rem;">Selecione a qual obra/projeto deseja vincular:</h4>
                <div style="max-height:240px; overflow-y:auto; margin-bottom:1.25rem;">
                    ${projectsHtml}
                </div>
                <div style="display:flex; justify-content:space-between; gap:0.5rem;">
                    <a href="/${currentLang}/projetos/novo?pre_product_id=${productId}&qty=${quantity}" class="rfq-btn rfq-btn-secondary" style="font-size:0.85rem;">
                        <span>➕</span> Criar Novo Projeto
                    </a>
                    <button type="button" id="cancel-quote-modal" class="rfq-btn rfq-btn-secondary">Cancelar</button>
                </div>
            </div>
        `;

        document.body.appendChild(modalOverlay);

        modalOverlay.querySelector('#close-quote-modal').addEventListener('click', () => modalOverlay.remove());
        modalOverlay.querySelector('#cancel-quote-modal').addEventListener('click', () => modalOverlay.remove());
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) modalOverlay.remove();
        });

        modalOverlay.querySelectorAll('.egen-quote-project-item').forEach(item => {
            item.addEventListener('click', () => {
                const projectId = item.getAttribute('data-project-id');
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                formData.append('project_id', projectId);

                const postUrl = `/${currentLang}/api/projetos/adicionar-item`;

                fetch(postUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const fallbackPost = await fetch('/api/projetos/adicionar-item', {
                            method: 'POST',
                            body: formData
                        });
                        return fallbackPost.json();
                    }
                    return res.json();
                })
                .then(resData => {
                    modalOverlay.remove();
                    if (resData.success) {
                        alert(resData.message || 'Produto adicionado ao orçamento com sucesso!');
                    } else {
                        alert(resData.message || 'Erro ao adicionar ao orçamento.');
                    }
                })
                .catch(err => {
                    modalOverlay.remove();
                    console.error('Erro ao adicionar item:', err);
                    alert('Erro ao enviar requisição para o orçamento.');
                });
            });
        });
    }
});
