async function updateReturnStatus(id, payload) {
    try {
        const response = await fetch(`/LPDHED2dC7Gjrg2b/devolucoes/${id}/status`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (response.status === 409) {
            const data = await response.json();

            if (data.error === "DATA_STALE") {
                // TRATAMENTO DA FALHA DE LOCK OTIMISTA
                exibirModalDeConflito({
                    mensagem: "Não foi possível salvar! Outro administrador alterou esta devolução enquanto você lia a página.",
                    dadosAtuais: data.current_data,
                    acaoAprovar: () => {
                        // Força o frontend a engolir os novos dados e atualizar a tela
                        atualizarDadosNaTela(data.current_data);
                    }
                });
                return;
            }
        }

        if (!response.ok) {
            throw new Error("Erro de processamento no servidor.");
        }

        const data = await response.json();
        
        exibirMensagemSucesso("Status atualizado com sucesso!");

        // Recarrega a página após uma pequena pausa para atualizar a linha do tempo (timeline)
        setTimeout(() => {
            window.location.reload();
        }, 1200);

    } catch (error) {
        exibirMensagemErro(error.message || "Falha na comunicação com o servidor.");
    }
}

// Exibe uma mensagem de sucesso (Toast)
function exibirMensagemSucesso(mensagem) {
    mostrarToast(mensagem, 'success');
}

// Exibe uma mensagem de erro (Toast)
function exibirMensagemErro(mensagem) {
    mostrarToast(mensagem, 'error');
}

// Mostra toast flutuante estilizado de forma animada e premium
function mostrarToast(mensagem, tipo) {
    const oldToast = document.getElementById('ag-toast');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.id = 'ag-toast';
    toast.style.position = 'fixed';
    toast.style.bottom = '30px';
    toast.style.right = '30px';
    toast.style.padding = '14px 28px';
    toast.style.borderRadius = '10px';
    toast.style.color = '#fff';
    toast.style.fontWeight = '600';
    toast.style.fontSize = '0.95rem';
    toast.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
    toast.style.zIndex = '9999';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '10px';
    toast.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
    toast.style.transform = 'translateY(120px) scale(0.9)';
    toast.style.opacity = '0';

    if (tipo === 'success') {
        toast.style.backgroundColor = '#10b981'; // Verde Esmeralda
        toast.innerHTML = `<i class="fas fa-check-circle" style="font-size: 1.1rem;"></i> ${mensagem}`;
    } else {
        toast.style.backgroundColor = '#ef4444'; // Vermelho Vibrante
        toast.innerHTML = `<i class="fas fa-exclamation-circle" style="font-size: 1.1rem;"></i> ${mensagem}`;
    }

    document.body.appendChild(toast);

    // Animação de entrada
    setTimeout(() => {
        toast.style.transform = 'translateY(0) scale(1)';
        toast.style.opacity = '1';
    }, 50);

    // Animação de saída e remoção automática
    setTimeout(() => {
        toast.style.transform = 'translateY(-20px) scale(0.95)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 400);
    }, 4500);
}

// Atualiza de forma reativa os dados no DOM
function atualizarDadosNaTela(dadosAtuais) {
    // 1. Atualiza a versão oculta no formulário
    const versionInput = document.getElementById('return_version');
    if (versionInput) {
        versionInput.value = dadosAtuais.version;
    }

    // 2. Atualiza a versão visível na tabela informativa
    const versionInfo = document.getElementById('info_version');
    if (versionInfo) {
        versionInfo.textContent = `#${dadosAtuais.version}`;
    }

    // 3. Atualiza a data de última modificação
    const dateModInfo = document.getElementById('info_date_modified');
    if (dateModInfo) {
        dateModInfo.textContent = dadosAtuais.date_modified;
    }

    // 4. Atualiza a pílula de status
    const statusPill = document.getElementById('current_status_pill');
    if (statusPill) {
        statusPill.textContent = dadosAtuais.status_name;
        
        statusPill.className = 'status-pill';
        const name = dadosAtuais.status_name.toLowerCase();
        if (name.includes('cancelado') || name.includes('recusado') || name.includes('rejeitado')) {
            statusPill.classList.add('status-cancelled');
        } else if (name.includes('completo') || name.includes('reembolsado')) {
            statusPill.classList.add('status-approved');
        } else {
            statusPill.classList.add('status-pending');
        }
    }

    // 5. Ajusta a seleção nos dropdowns do formulário de atualização
    const statusSelect = document.getElementById('return_status_id');
    if (statusSelect && dadosAtuais.return_status_id) {
        statusSelect.value = dadosAtuais.return_status_id;
    }
    const actionSelect = document.getElementById('return_action_id');
    if (actionSelect && dadosAtuais.return_action_id) {
        actionSelect.value = dadosAtuais.return_action_id;
    }

    // Limpa o comentário para evitar envio duplicado em nova tentativa
    const commentTextarea = document.getElementById('comment');
    if (commentTextarea) {
        commentTextarea.value = '';
    }

    exibirMensagemSucesso("Formulário atualizado com os dados mais recentes!");
}

// Cria e exibe dinamicamente o Modal de Conflito de Concorrência
function exibirModalDeConflito({ mensagem, dadosAtuais, acaoAprovar }) {
    const oldModal = document.getElementById('ag-concurrency-modal');
    if (oldModal) oldModal.remove();

    const overlay = document.createElement('div');
    overlay.id = 'ag-concurrency-modal';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100vw';
    overlay.style.height = '100vh';
    overlay.style.backgroundColor = 'rgba(15, 23, 42, 0.75)'; // Tom escuro suave (Slate-900)
    overlay.style.backdropFilter = 'blur(8px)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = '99999';
    overlay.style.transition = 'all 0.3s ease';
    overlay.style.opacity = '0';

    const card = document.createElement('div');
    card.style.backgroundColor = 'var(--color-card-bg, #ffffff)';
    card.style.border = '1px solid var(--color-border, #e2e8f0)';
    card.style.borderRadius = '16px';
    card.style.padding = '2rem';
    card.style.maxWidth = '520px';
    card.style.width = '90%';
    card.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.4)';
    card.style.transform = 'scale(0.9) translateY(20px)';
    card.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
    card.style.color = 'var(--color-text-main, #0f172a)';
    card.style.display = 'flex';
    card.style.flexDirection = 'column';
    card.style.alignItems = 'center';
    card.style.textAlign = 'center';

    // Ícone de Alerta Estilizado
    const iconBox = document.createElement('div');
    iconBox.style.width = '64px';
    iconBox.style.height = '64px';
    iconBox.style.borderRadius = '50%';
    iconBox.style.backgroundColor = '#fef3c7'; // Amarelo suave
    iconBox.style.color = '#d97706'; // Amarelo escuro (Amber)
    iconBox.style.display = 'flex';
    iconBox.style.alignItems = 'center';
    iconBox.style.justifyContent = 'center';
    iconBox.style.fontSize = '2rem';
    iconBox.style.marginBottom = '1.25rem';
    iconBox.style.animation = 'pulse 2s infinite';
    iconBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';

    const title = document.createElement('h3');
    title.textContent = 'Conflito de Concorrência';
    title.style.margin = '0 0 0.75rem 0';
    title.style.fontSize = '1.4rem';
    title.style.fontWeight = '700';

    const desc = document.createElement('p');
    desc.textContent = mensagem;
    desc.style.fontSize = '0.95rem';
    desc.style.color = 'var(--color-text-muted, #64748b)';
    desc.style.lineHeight = '1.6';
    desc.style.margin = '0 0 1.5rem 0';

    // Tabela com detalhes da modificação
    const detailsContainer = document.createElement('div');
    detailsContainer.style.backgroundColor = 'var(--color-bg-soft, #f8fafc)';
    detailsContainer.style.border = '1px solid var(--color-border, #e2e8f0)';
    detailsContainer.style.borderRadius = '10px';
    detailsContainer.style.padding = '1.25rem';
    detailsContainer.style.marginBottom = '1.5rem';
    detailsContainer.style.width = '100%';
    detailsContainer.style.boxSizing = 'border-box';
    detailsContainer.style.fontSize = '0.875rem';

    detailsContainer.innerHTML = `
        <div style="font-weight: 700; text-align: left; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: 0.75rem; color: #1e293b; display: flex; align-items: center; gap: 6px;">
            <i class="fas fa-database text-muted"></i> Registro Atual no Servidor
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; text-align: left;">
            <span style="color: var(--color-text-muted);">Status Atual:</span>
            <span style="font-weight: 600; text-align: right; color: #1e293b;">${dadosAtuais.status_name}</span>
            <span style="color: var(--color-text-muted);">Modificado em:</span>
            <span style="font-weight: 600; text-align: right; color: #1e293b;">${dadosAtuais.date_modified}</span>
            <span style="color: var(--color-text-muted);">Versão Recente:</span>
            <span style="font-weight: 600; text-align: right; color: var(--color-primary);">#${dadosAtuais.version}</span>
        </div>
    `;

    // Botões de Ação
    const btnWrapper = document.createElement('div');
    btnWrapper.style.display = 'flex';
    btnWrapper.style.gap = '12px';
    btnWrapper.style.width = '100%';

    const btnCancel = document.createElement('button');
    btnCancel.textContent = 'Manter como está';
    btnCancel.className = 'btn btn-secondary';
    btnCancel.style.flex = '1';
    btnCancel.style.padding = '12px 18px';
    btnCancel.style.backgroundColor = 'var(--color-bg-soft, #f1f5f9)';
    btnCancel.style.border = '1px solid var(--color-border, #cbd5e1)';
    btnCancel.style.color = 'var(--color-text-muted, #64748b)';
    btnCancel.style.borderRadius = '8px';
    btnCancel.style.cursor = 'pointer';
    btnCancel.style.fontWeight = '600';
    btnCancel.onclick = () => {
        overlay.style.opacity = '0';
        card.style.transform = 'scale(0.9) translateY(20px)';
        setTimeout(() => overlay.remove(), 300);
    };

    const btnApprove = document.createElement('button');
    btnApprove.textContent = 'Carregar novos dados';
    btnApprove.className = 'btn btn-primary';
    btnApprove.style.flex = '1';
    btnApprove.style.padding = '12px 18px';
    btnApprove.style.backgroundColor = 'var(--color-primary, #3b82f6)';
    btnApprove.style.color = '#ffffff';
    btnApprove.style.border = 'none';
    btnApprove.style.borderRadius = '8px';
    btnApprove.style.cursor = 'pointer';
    btnApprove.style.fontWeight = '600';
    btnApprove.onclick = () => {
        acaoApprovar();
        overlay.style.opacity = '0';
        card.style.transform = 'scale(0.9) translateY(20px)';
        setTimeout(() => overlay.remove(), 300);
    };

    btnWrapper.appendChild(btnCancel);
    btnWrapper.appendChild(btnApprove);

    card.appendChild(iconBox);
    card.appendChild(title);
    card.appendChild(desc);
    card.appendChild(detailsContainer);
    card.appendChild(btnWrapper);

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    // Efeito de fade-in no overlay e card
    setTimeout(() => {
        overlay.style.opacity = '1';
        card.style.transform = 'scale(1) translateY(0)';
    }, 50);
}
