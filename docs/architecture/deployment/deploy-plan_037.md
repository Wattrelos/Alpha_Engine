# DP-37: Plano de Implementação — Fase 2 (Consolidação de Estilos de Pedidos)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-09 20:50:03
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/37

## Descrição

# Plano de Implementação — Fase 2 (Consolidação de Estilos de Pedidos)

Este plano descreve o processo de migração e unificação das folhas de estilos internas contidas nas páginas de histórico e detalhes de pedidos para o arquivo global `personalizada.css`, eliminando duplicações e otimizando os arquivos Twig correspondentes.

---

## 🎯 Objetivo

* **Unificar** seletores repetidos em [orders.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/orders.twig) e [order-history.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/order-history.twig).
* **Migrar** os estilos resultantes para o arquivo [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css).
* **Remover** as tags `<style>` internas destas páginas para mantê-las limpas e otimizadas.

---

## 🛠️ Alterações Propostas

### 1. Folhas de Estilo (CSS)

#### [MODIFY] [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)
* Adicionar as regras de estilos unificados de Pedidos ao final do arquivo. As variações específicas de comportamento e layouts flex/grid de cada página serão tratadas de maneira organizada usando escopo de ID `#account-orders-page` e `#account-order-detail-page`.

### 2. Templates Twig (Views)

#### [MODIFY] [orders.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/orders.twig)
* Remover todo o bloco `<style>` interno (linhas 121 a 412).

#### [MODIFY] [order-history.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/order-history.twig)
* Remover todo o bloco `<style>` interno (linhas 162 a 542).

---

## 📋 Código CSS Unificado a ser Adicionado a `personalizada.css`

```css
/* ══════════════════════════════════════════════════
   ORDERS AREA — Alpha Engine Premium UI (shared)
   ══════════════════════════════════════════════════ */

#account-orders-page,
#account-order-detail-page {
    min-height: 100vh;
    background: #0d0f14;
    color: #e2e8f0;
    font-family: 'Inter', 'Outfit', system-ui, sans-serif;
}

/* ── Hero ── */
.orders-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
    border-bottom: 1px solid rgba(99, 102, 241, 0.2);
    padding: 3rem 2rem 2.5rem;
}
.orders-hero-inner {
    max-width: 1100px;
    margin: 0 auto;
}
.orders-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #94a3b8;
    margin-bottom: 1.5rem;
}
.orders-breadcrumb a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.2s;
}
.orders-breadcrumb a:hover { color: #a5b4fc; }
.orders-breadcrumb .sep { color: #475569; }
.orders-breadcrumb .current { color: #a5b4fc; font-weight: 500; }

.orders-title {
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    font-weight: 700;
    color: #f1f5f9;
    margin: 0 0 0.5rem;
    letter-spacing: -0.02em;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.orders-title-icon { font-size: 1.6rem; }
.orders-subtitle {
    color: #94a3b8;
    font-size: 0.95rem;
    margin: 0;
}

/* ── Wrapper ── */
.orders-wrapper {
    max-width: 1100px;
    margin: 0 auto;
}
#account-orders-page .orders-wrapper {
    padding: 2rem 1.5rem 4rem;
}
#account-order-detail-page .orders-wrapper {
    padding: 2.5rem 1.5rem 5rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* ── Card / Table ── */
.orders-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    overflow: hidden;
    backdrop-filter: blur(8px);
}
#account-orders-page .orders-card {
    margin-bottom: 2rem;
}
.orders-table-wrap {
    overflow-x: auto;
}
.orders-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}
.orders-table thead tr {
    background: rgba(99, 102, 241, 0.08);
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.orders-table th {
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
}
#account-orders-page .orders-table th {
    padding: 0.9rem 1.2rem;
    white-space: nowrap;
}
#account-order-detail-page .orders-table th {
    padding: 0.9rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.orders-table th.col-id,
.orders-table td.col-id,
.orders-table th.col-items,
.orders-table td.col-items,
.orders-table th.col-total,
.orders-table td.col-total,
.orders-table th.col-right,
.orders-table td.col-right {
    text-align: right;
}
.orders-table th.col-action,
.orders-table td.col-action,
.orders-table th.col-center,
.orders-table td.col-center {
    text-align: center;
}

.order-row {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background 0.2s;
}
.order-row:last-child { border-bottom: none; }
#account-orders-page .order-row:hover { background: rgba(99, 102, 241, 0.06); }
#account-order-detail-page .order-row:hover { background: rgba(99, 102, 241, 0.04); }

.orders-table td {
    vertical-align: middle;
}
#account-orders-page .orders-table td {
    padding: 1rem 1.25rem;
}
#account-order-detail-page .orders-table td {
    padding: 1.25rem 1.5rem;
}

/* Status Pills */
.status-pill,
.timeline-status-pill {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 100px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.status-pill.status-completo,
.status-pill.status-entregue,
.timeline-status-pill.status-completo,
.timeline-status-pill.status-entregue {
    background: rgba(34, 197, 94, 0.12);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.3);
}
.status-pill.status-cancelado,
.status-pill.status-recusado,
.timeline-status-pill.status-cancelado,
.timeline-status-pill.status-recusado {
    background: rgba(239, 68, 68, 0.12);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.3);
}
.status-pill.status-enviado,
.status-pill.status-em-transporte,
.timeline-status-pill.status-enviado,
.timeline-status-pill.status-em-transporte {
    background: rgba(59, 130, 246, 0.12);
    color: #60a5fa;
    border-color: rgba(59, 130, 246, 0.3);
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #94a3b8;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    transition: all 0.2s;
}
.btn-back:hover {
    color: #e2e8f0;
    background: rgba(255,255,255,0.05);
}

/* ── Specific elements (List View) ── */
.orders-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-chip {
    background: rgba(99, 102, 241, 0.12);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 100px;
    padding: 0.35rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.stat-chip-value {
    font-weight: 700;
    color: #a5b4fc;
    font-size: 1rem;
}
.stat-chip-label {
    color: #94a3b8;
    font-size: 0.82rem;
}
.order-num {
    font-weight: 700;
    color: #a5b4fc;
    font-size: 0.95rem;
    font-variant-numeric: tabular-nums;
}
.items-badge {
    background: rgba(148, 163, 184, 0.1);
    border-radius: 100px;
    padding: 0.2rem 0.7rem;
    font-size: 0.8rem;
    color: #cbd5e1;
}
.order-total-value {
    color: #e2e8f0;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.date-text {
    color: #94a3b8;
    font-size: 0.85rem;
    font-variant-numeric: tabular-nums;
}
.btn-view {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.3);
    color: #a5b4fc;
    text-decoration: none;
    padding: 0.4rem 0.85rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-view:hover {
    background: rgba(99, 102, 241, 0.3);
    border-color: rgba(99, 102, 241, 0.6);
    color: #c7d2fe;
    transform: translateY(-1px);
}
.orders-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.2rem;
    border-top: 1px solid rgba(255,255,255,0.06);
    font-size: 0.85rem;
    color: #94a3b8;
}
.orders-empty {
    text-align: center;
    padding: 5rem 2rem;
    background: rgba(255,255,255,0.02);
    border: 1px dashed rgba(255,255,255,0.1);
    border-radius: 16px;
    margin-bottom: 2rem;
}
.empty-icon { font-size: 4rem; margin-bottom: 1.5rem; }
.empty-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0 0 0.75rem;
}
.empty-desc {
    color: #94a3b8;
    margin: 0 0 2rem;
}
.btn-shop {
    display: inline-block;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    text-decoration: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
}
.btn-shop:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    color: #fff;
}
.orders-footer { display: flex; align-items: center; }

/* ── Specific elements (Detail View) ── */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr 1fr;
    }
}
.detail-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 1.5rem;
    backdrop-filter: blur(8px);
}
.detail-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 0.75rem;
}
.detail-card-content {
    font-size: 0.9rem;
    color: #cbd5e1;
    line-height: 1.6;
}
.meta-item {
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px dashed rgba(255, 255, 255, 0.06);
    font-size: 0.85rem;
    color: #94a3b8;
}
.meta-item strong {
    color: #a5b4fc;
}
.orders-card-header {
    padding: 1.25rem 1.5rem;
    background: rgba(99, 102, 241, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}
.orders-card-header-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0;
}
.product-name-text {
    color: #e2e8f0;
    font-weight: 600;
}
.product-option-text {
    color: #94a3b8;
}
.model-text {
    color: #94a3b8;
    background: rgba(255, 255, 255, 0.04);
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
}
.totals-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    padding: 1.5rem;
    background: rgba(0, 0, 0, 0.15);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    gap: 0.75rem;
}
.total-row {
    display: flex;
    justify-content: space-between;
    width: 100%;
    max-width: 320px;
    font-size: 0.9rem;
    color: #cbd5e1;
}
.total-row-title {
    color: #94a3b8;
}
.total-row-val {
    color: #f1f5f9;
    font-weight: 600;
}
.total-row--final {
    font-size: 1.2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 0.75rem;
    margin-top: 0.25rem;
}
.total-row--final .total-row-title {
    color: #a5b4fc;
    font-weight: 700;
}
.total-row--final .total-row-val {
    color: #ffffff;
    font-weight: 800;
}
.timeline-wrap {
    padding: 2rem;
}
.timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: rgba(255, 255, 255, 0.06);
}
.timeline-item {
    position: relative;
    padding-left: 2rem;
}
.timeline-dot {
    position: absolute;
    left: 0;
    top: 6px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #1e293b;
    border: 3px solid rgba(99, 102, 241, 0.5);
    z-index: 2;
    transition: all 0.3s ease;
}
.timeline-item:hover .timeline-dot {
    border-color: #a5b4fc;
    background: #6366f1;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.6);
}
.timeline-content {
    background: rgba(255, 255, 255, 0.015);
    border: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 12px;
    padding: 1.25rem;
}
.timeline-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.timeline-date {
    font-size: 0.8rem;
    color: #64748b;
}
.timeline-comment {
    font-size: 0.88rem;
    color: #94a3b8;
    line-height: 1.5;
    background: rgba(0, 0, 0, 0.15);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    border-left: 3px solid rgba(99, 102, 241, 0.4);
}
.timeline-empty {
    text-align: center;
    color: #94a3b8;
    margin: 0;
    padding: 1rem 0;
}
.font-tabular {
    font-variant-numeric: tabular-nums;
}
.btn-return-action {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    background: rgba(99, 102, 241, 0.1);
    color: #a5b4fc;
    border: 1px solid rgba(99, 102, 241, 0.25);
    transition: all 0.2s;
}
.btn-return-action:hover {
    background: #6366f1;
    color: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
}

/* ── Responsive ── */
@media (max-width: 640px) {
    .orders-hero { padding: 2rem 1rem 1.5rem; }
    .orders-table th.col-date,
    .orders-table td.col-date { display: none; }
    .orders-table th:nth-child(2),
    .orders-table td:nth-child(2) { display: none; } /* Hide model in details */
    .orders-table th, .orders-table td {
        padding: 0.75rem 1rem;
    }
    .btn-view span { display: none; }
    .btn-view { padding: 0.5rem; }
    .timeline-wrap { padding: 1rem; }
}
```

---

## 🧪 Plano de Verificação

### Verificação Manual
1. Abrir a listagem de pedidos do cliente (`/index.php?route=account/order` ou equivalente).
2. Confirmar se o gradiente do herói, breadcrumbs, barra de estatísticas (`stat-chip`), tabela e paginação estão perfeitamente renderizados e alinhados.
3. Clicar em "Ver Detalhes" para abrir uma página de histórico de pedido específica.
4. Validar se a timeline de status, cartões de endereço de envio/faturamento, e a tabela detalhada de produtos/totais mantêm a identidade visual Premium UI.
5. Testar o comportamento responsivo em resoluções mobile (reduzir tamanho da viewport), verificando a ocultação das colunas de data/modelo e a adaptação do botão de visualização.

# Tarefas — Fase 2 (Consolidação de Estilos de Pedidos)

- `[x]` Migrar e consolidar estilos de Pedidos no [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)
- `[x]` Remover o bloco `<style>` em [orders.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/orders.twig)
- `[x]` Remover o bloco `<style>` em [order-history.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/order-history.twig)
- `[x]` Validar layout das páginas de Pedidos no e-commerce

# Walkthrough — Consolidação de Estilos (Fase 1 e Fase 2)

Concluímos com sucesso a execução das **Fases 1 e 2** da consolidação de estilos do e-commerce.

---

## 🛠️ Fase 1: Área de Endereços

### 1. Centralização e Unificação de CSS
* **[personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)**:
  * Agrupou e unificou estilos duplicados dos elementos das páginas de endereço (`.addr-hero`, `.addr-breadcrumb`, `.addr-wrapper`, `.addr-alert`, `.addr-btn-back`).
  * Manteve as variações de largura máxima e tamanho de ícones aplicando escopo a partir do ID da página-pai (`#address-create-page`, `#address-edit-page`, `#addresses-page`).
  * Consolidou os estilos dos formulários compartilhados de criação e edição (`.addr-form-card`, `.addr-form-section`, `.addr-form-grid`, etc.).
  * Adicionou estilos específicos da listagem (`.addr-grid`, `.addr-card`, `.addr-btn-card`).

### 2. Limpeza dos Templates Twig
* **[create.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/create.twig)**:
  * Removido o bloco `<style>` contendo ~190 linhas.
* **[edit.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/edit.twig)**:
  * Removido o bloco `<style>` contendo ~130 linhas de duplicação.
* **[index.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/index.twig)**:
  * Removido o bloco `<style>` contendo ~190 linhas.

---

## 🛠️ Fase 2: Área de Pedidos

### 1. Centralização e Unificação de CSS
* **[personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)**:
  * Consolidou estilos de herói (`.orders-hero`), breadcrumbs (`.orders-breadcrumb`) e títulos das páginas de listagem e detalhes de pedidos.
  * Unificou a estilização das tabelas de listagem e itens de pedido (`.orders-table-wrap`, `.orders-table`, `.order-row`), incluindo alinhamentos tabulares.
  * Consolidou os estilos dos badges de status (`.status-pill` e `.timeline-status-pill`) e o botão de voltar (`.btn-back`) em seletores combinados.
  * Adicionou os componentes da timeline de histórico (`.timeline-wrap`, `.timeline`, `.timeline-item`, `.timeline-dot`, etc.) e visualização de detalhes (`.detail-grid`, `.detail-card`, `.totals-wrap`, `.total-row`).

### 2. Limpeza dos Templates Twig
* **[orders.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/orders.twig)**:
  * Removido o bloco `<style>` contendo ~290 linhas.
* **[order-history.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/order-history.twig)**:
  * Removido o bloco `<style>` contendo ~380 linhas.

---

## 🔬 Resultados e Verificação
* **Redução Significativa de Código Duplicado**: A consolidação e compartilhamento de regras reduziu cerca de **670 linhas** líquidas de código CSS inline nos templates Twig.
* **Preservação de Layout e Funcionalidade**: Toda a estrutura semântica das páginas, marcação HTML e comportamento responsivo (ocultação de colunas específicas em telas menores e layouts flex/grid inteligentes) foram plenamente preservados.

