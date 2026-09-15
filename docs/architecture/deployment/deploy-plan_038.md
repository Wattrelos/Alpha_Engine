# DP-38: Fase 3 (Consolidação de Estilos de Devoluções e Institucional)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-09 20:53:02
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/38

## Descrição

# Plano de Implementação — Fase 3 (Consolidação de Estilos de Devoluções e Institucional)

Este plano descreve o processo de migração e unificação das folhas de estilos internas contidas nas páginas de devolução e institucionais para o arquivo global `personalizada.css`, reduzindo duplicações e otimizando os arquivos Twig correspondentes.

---

## 🎯 Objetivo

* **Unificar** seletores repetidos em:
  * 📄 [return.twig](/resources/views/pages/users/return.twig)
  * 📄 [return-info.html.twig](/resources/views/pages/users/return-info.html.twig)
  * 📄 [product-returns.html.twig](/resources/views/pages/product/product-returns.html.twig)
  * 📄 [contact.twig](/resources/views/pages/information/contact.twig)
  * 📄 [show.html.twig](/resources/views/pages/information/show.html.twig)
* **Migrar** as definições de estilo resultantes para [personalizada.css](/public_html/css/custom/personalizada.css).
* **Remover** as tags `<style>` internas originais destas páginas.

---

## 🛠️ Alterações Propostas

### 1. Folhas de Estilo (CSS)

#### [MODIFY] [personalizada.css](/public_html/css/custom/personalizada.css)
* Adicionar as regras de estilos unificados de Devoluções e Páginas Institucionais ao final do arquivo. As variações específicas e escopos serão gerenciados usando as IDs parentais `#account-return-form-page`, `#account-return-detail-page`, `#account-returns-page`, `#contact-page` e `#info-page`.

### 2. Templates Twig (Views)

#### [MODIFY] [return.twig](/resources/views/pages/users/return.twig)
* Remover bloco `<style>` interno (linhas 168 a 426).

#### [MODIFY] [return-info.html.twig](/resources/views/pages/users/return-info.html.twig)
* Remover bloco `<style>` interno (linhas 155 a 490).

#### [MODIFY] [product-returns.html.twig](/resources/views/pages/product/product-returns.html.twig)
* Remover bloco `<style>` interno (linhas 100 a 335).

#### [MODIFY] [contact.twig](/resources/views/pages/information/contact.twig)
* Remover bloco `<style>` interno (linhas 201 a 490).

#### [MODIFY] [show.html.twig](/resources/views/pages/information/show.html.twig)
* Remover bloco `<style>` interno (linhas 55 a 176).

---

## 📋 Código CSS Unificado a ser Adicionado a `personalizada.css`

```css
/* ══════════════════════════════════════════════════
   RETURNS & INSTITUTIONAL AREA — Alpha Engine Premium UI (shared)
   ══════════════════════════════════════════════════ */

#account-return-form-page,
#account-return-detail-page,
#account-returns-page,
#contact-page,
#info-page {
    min-height: 100vh;
    background: #0d0f14;
    color: #e2e8f0;
    font-family: 'Inter', 'Outfit', system-ui, sans-serif;
}

/* ── Hero Sections ── */
.return-hero,
.returns-hero,
.contact-hero,
.info-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
    border-bottom: 1px solid rgba(99, 102, 241, 0.2);
    padding: 3rem 2rem 2.5rem;
}
.return-hero-inner,
.returns-hero-inner,
.contact-hero-inner,
.info-hero-inner {
    margin: 0 auto;
}
.return-hero-inner,
.info-hero-inner {
    max-width: 900px;
}
.returns-hero-inner {
    max-width: 1100px;
}
.contact-hero-inner {
    max-width: 1200px;
}

/* ── Breadcrumbs ── */
.return-breadcrumb,
.returns-breadcrumb,
.contact-breadcrumb,
.info-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #94a3b8;
}
.return-breadcrumb,
.returns-breadcrumb {
    margin-bottom: 1.5rem;
}
.contact-breadcrumb,
.info-breadcrumb {
    margin-bottom: 1.75rem;
}
.return-breadcrumb a,
.returns-breadcrumb a,
.contact-breadcrumb a,
.info-breadcrumb a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.2s;
}
.return-breadcrumb a:hover,
.returns-breadcrumb a:hover,
.contact-breadcrumb a:hover,
.info-breadcrumb a:hover {
    color: #a5b4fc;
}
.return-breadcrumb .sep,
.returns-breadcrumb .sep,
.contact-breadcrumb .sep,
.info-breadcrumb .sep {
    color: #475569;
}
.return-breadcrumb .current,
.returns-breadcrumb .current,
.contact-breadcrumb .current,
.info-breadcrumb .current {
    color: #a5b4fc;
    font-weight: 500;
}

/* ── Title / Icons ── */
.return-title,
.returns-title,
.contact-hero-title,
.info-hero-title {
    font-weight: 700;
    color: #f1f5f9;
    letter-spacing: -0.02em;
}
.return-title,
.returns-title {
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    margin: 0 0 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.contact-hero-title {
    font-size: clamp(1.8rem, 3vw, 2.4rem);
    margin: 0 0 0.25rem;
}
.info-hero-title {
    font-size: clamp(1.5rem, 3vw, 2.2rem);
    margin: 0 0 0.25rem;
}
.return-title-icon,
.returns-title-icon {
    font-size: 1.6rem;
}
.return-subtitle,
.returns-subtitle,
.contact-hero-sub,
.info-hero-sub {
    color: #94a3b8;
    font-size: 0.95rem;
    margin: 0;
}
.info-hero-sub {
    font-size: 0.9rem;
}

.contact-hero-content,
.info-hero-content {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}
.contact-icon-hero,
.info-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(139,92,246,0.25));
    border: 1px solid rgba(99, 102, 241, 0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a5b4fc;
    flex-shrink: 0;
}

/* ── Wrappers ── */
.return-wrapper,
.returns-wrapper,
.contact-wrapper,
.info-wrapper {
    margin: 0 auto;
}
.return-wrapper,
.info-wrapper {
    max-width: 900px;
}
.return-wrapper {
    padding: 2.5rem 1.5rem 5rem;
}
.info-wrapper {
    padding: 3rem 1.5rem 6rem;
}
.returns-wrapper {
    max-width: 1100px;
    padding: 2rem 1.5rem 4rem;
}
.contact-wrapper {
    max-width: 1200px;
    padding: 4rem 2rem 6rem;
}

/* ── Cards / Layout containers ── */
.form-card,
.return-card,
.returns-card,
.contact-form-card,
.info-card,
.detail-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    backdrop-filter: blur(8px);
}
.detail-card {
    background: rgba(255, 255, 255, 0.02);
    border-color: rgba(255, 255, 255, 0.06);
    padding: 1.5rem;
}
.contact-form-card {
    border-color: rgba(255, 255, 255, 0.07);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
}
.info-card {
    border-color: rgba(255, 255, 255, 0.07);
    padding: 2.5rem 3rem;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}
.returns-card {
    margin-bottom: 2rem;
}
.form-card,
.return-card,
.returns-card {
    overflow: hidden;
}

.form-card-header,
.return-card-header {
    padding: 1.25rem 1.5rem;
    background: rgba(99, 102, 241, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}
.form-card-header-title,
.return-card-header-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0;
}
.form-card-body,
.return-card-body {
    padding: 1.5rem;
}

/* ── Form Controls & Groups ── */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.form-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #94a3b8;
}
#account-return-form-page .form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 0.5rem;
}
#account-return-form-page .form-label.required::after {
    content: " *";
    color: #ef4444;
}

.form-input {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0.875rem 1.25rem;
    color: #f1f5f9;
    font-size: 0.95rem;
    font-family: inherit;
    transition: all 0.2s ease-in-out;
}
#account-return-form-page .form-input {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 0.65rem 0.9rem;
    font-size: 0.9rem;
}
.form-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    background: rgba(15, 23, 42, 0.8);
}
#account-return-form-page .form-input:focus {
    box-shadow: 0 0 8px rgba(99, 102, 241, 0.25);
    background: rgba(0, 0, 0, 0.3);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}
#account-return-form-page .form-textarea {
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.65rem 0.9rem;
    font-size: 0.9rem;
    color: #f1f5f9;
    transition: all 0.2s;
    font-family: inherit;
}
#account-return-form-page .form-textarea:focus {
    border-color: #6366f1;
    outline: none;
    box-shadow: 0 0 8px rgba(99, 102, 241, 0.25);
    background: rgba(0, 0, 0, 0.3);
}

.form-group.has-error .form-input {
    border-color: #ef4444;
}
.form-group.has-error .form-input:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
}

/* ── Buttons ── */
.btn-submit,
.contact-btn-submit,
.btn-shop {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #ffffff;
    text-decoration: none;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    font-family: inherit;
    font-size: 0.95rem;
}
.btn-submit:hover,
.contact-btn-submit:hover,
.btn-shop:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    color: #ffffff;
}
.contact-btn-submit {
    align-self: flex-start;
    gap: 0.75rem;
}
.contact-btn-submit:active {
    transform: translateY(1px);
}
.contact-btn-submit .btn-arrow {
    transition: transform 0.2s ease-in-out;
}
.contact-btn-submit:hover .btn-arrow {
    transform: translateX(3px);
}

.btn-back,
.btn-cancel {
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
.btn-back:hover,
.btn-cancel:hover {
    color: #e2e8f0;
    background: rgba(255, 255, 255, 0.05);
}
.btn-cancel {
    padding: 0.5rem 1rem;
}

/* ── Specific Elements (return.twig) ── */
.radio-group {
    display: flex;
    gap: 1.5rem;
}
.radio-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}
.radio-item input[type="radio"] {
    accent-color: #6366f1;
    width: 1rem;
    height: 1rem;
}
.radio-label {
    font-size: 0.9rem;
    color: #e2e8f0;
}
.radio-column {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.radio-item-block {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.radio-item-block:hover {
    background: rgba(99, 102, 241, 0.05);
    border-color: rgba(99, 102, 241, 0.2);
}
.radio-item-block input[type="radio"] {
    accent-color: #6366f1;
    width: 1.1rem;
    height: 1.1rem;
}
.radio-label-text {
    font-size: 0.88rem;
    color: #cbd5e1;
}
.error-msg {
    color: #f87171;
    font-size: 0.78rem;
    margin-top: 0.35rem;
    font-weight: 500;
}
.return-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
}
.grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 640px) {
    .grid-2 { grid-template-columns: 1fr 1fr; }
}
.grid-3 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 768px) {
    .grid-3 { grid-template-columns: 2fr 1fr 1fr; }
}
.mt-4 { margin-top: 1.5rem; }
.mb-3 { margin-bottom: 0.75rem; }
.d-block { display: block; }

/* ── Specific Elements (return-info.twig) ── */
.info-row {
    margin-bottom: 0.5rem;
}
.info-row strong {
    color: #94a3b8;
    margin-right: 0.35rem;
}
.product-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.06);
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
}
.product-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.product-info-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.product-info-val {
    font-size: 0.95rem;
    color: #f1f5f9;
}
.model-badge {
    display: inline-block;
    background: rgba(255,255,255,0.04);
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
}
.text-warning { color: #fcd34d !important; }
.text-success { color: #4ade80 !important; }
.fault-comment-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.fault-comment-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.fault-comment-content {
    background: rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 12px;
    padding: 1.25rem;
    font-size: 0.9rem;
    color: #cbd5e1;
    line-height: 1.6;
}
.return-footer { display: flex; align-items: center; }

/* ── Specific Elements (product-returns.twig) ── */
.returns-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.returns-table-wrap {
    overflow-x: auto;
}
.returns-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}
.returns-table thead tr {
    background: rgba(99, 102, 241, 0.08);
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.returns-table th {
    padding: 0.9rem 1.2rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
    white-space: nowrap;
}
.returns-table th.col-id,
.returns-table th.col-order { text-align: right; }
.return-row {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background 0.2s;
    cursor: pointer;
}
.return-row:last-child { border-bottom: none; }
.return-row:hover { background: rgba(99, 102, 241, 0.06); }
.returns-table td {
    padding: 1rem 1.2rem;
    vertical-align: middle;
}
.returns-table td.col-id,
.returns-table td.col-order { text-align: right; }
.returns-empty {
    text-align: center;
    padding: 5rem 2rem;
    background: rgba(255,255,255,0.02);
    border: 1px dashed rgba(255,255,255,0.1);
    border-radius: 16px;
    margin-bottom: 2rem;
}
.returns-footer { display: flex; align-items: center; }

/* ── Specific Elements (contact.twig) ── */
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1.25fr;
    gap: 4rem;
    align-items: start;
}
.contact-section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 0.75rem;
    letter-spacing: -0.01em;
}
.info-card-group {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}
.info-detail-item {
    display: flex;
    gap: 1.25rem;
    margin-bottom: 2rem;
}
.info-detail-item:last-child {
    margin-bottom: 0;
}
.info-detail-item h3 {
    font-size: 1.05rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0 0 0.5rem;
}
.info-detail-item p, .store-address {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #94a3b8;
    margin: 0;
    font-style: normal;
}
.contact-success-alert {
    display: flex;
    gap: 1rem;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 2rem;
    color: #a7f3d0;
}
.contact-success-alert h3 {
    margin: 0 0 0.25rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: #34d399;
}
.contact-success-alert p {
    margin: 0;
    font-size: 0.9rem;
    color: #a7f3d0;
}
.alert-icon {
    color: #34d399;
    flex-shrink: 0;
}
.egen-contact-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.invalid-feedback {
    font-size: 0.85rem;
    color: #ef4444;
    margin-top: 0.25rem;
}

/* ── Specific Elements (show.html.twig) ── */
.info-body-content {
    line-height: 1.8;
    font-size: 1.05rem;
    color: #cbd5e1;
}
.info-body-content p {
    margin-bottom: 1.5rem;
}
.info-body-content h2, .info-body-content h3, .info-body-content h4 {
    color: #f1f5f9;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.info-body-content h2 { font-size: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem; }
.info-body-content h3 { font-size: 1.25rem; }
.info-body-content ul, .info-body-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}
.info-body-content li {
    margin-bottom: 0.5rem;
}
.info-body-content strong {
    color: #f8fafc;
}
.info-body-content a {
    color: #8b5cf6;
    text-decoration: none;
    transition: color 0.2s;
    border-bottom: 1px dotted rgba(139, 92, 246, 0.4);
}
.info-body-content a:hover {
    color: #a5b4fc;
    border-bottom-style: solid;
}

/* ── Responsive Adjustments (Fase 3) ── */
@media (max-width: 968px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
}
@media (max-width: 640px) {
    .return-hero, .returns-hero, .contact-hero, .info-hero { padding: 2.5rem 1rem 2rem; }
    .return-wrapper, .returns-wrapper, .contact-wrapper, .info-wrapper { padding: 2rem 1rem 4rem; }
    .product-info-grid { grid-template-columns: 1fr; gap: 1rem; }
    .timeline-wrap { padding: 1rem; }
    .returns-table th.col-date, .returns-table td.col-date { display: none; }
    .info-card-group, .contact-form-card { padding: 1.5rem; }
    .contact-btn-submit { width: 100%; justify-content: center; }
    .info-card { padding: 1.5rem; }
}
```

---

## 🧪 Plano de Verificação

### Verificação Manual
1. **Página de Solicitação de Devolução**:
   * Abrir o formulário de devolução (`/index.php?route=account/return/add` ou equivalente).
   * Verificar se o layout do formulário, inputs de dados e seleção de motivos estão corretos.
2. **Página de Listagem de Devoluções**:
   * Abrir `/index.php?route=account/return`.
   * Verificar a tabela de solicitações, status e chip de estatísticas.
3. **Página de Detalhes da Devolução**:
   * Abrir os detalhes de uma devolução específica.
   * Validar a visualização dos dados do produto, motivo, ações recomendadas e a linha do tempo (timeline) de histórico de status.
4. **Página de Contato**:
   * Abrir `/index.php?route=information/contact`.
   * Validar o grid de duas colunas (Informações de contato e Formulário de mensagem) e o envio da mensagem.
5. **Página Institucional**:
   * Abrir qualquer página institucional (Ex: "/empresa" ou `/index.php?route=information/information`).
   * Validar se a formatação de textos (parágrafos, títulos, links e listas) no conteúdo `raw` do artigo está perfeitamente legível.
6. **Responsividade**:
   * Simular navegação mobile para verificar se todas as páginas colapsam os grids, colunas de tabelas e botões adequadamente.

# Tarefas — Fase 3 (Consolidação de Estilos de Devoluções e Institucional)

- `[x]` Migrar e consolidar estilos de Devoluções e Institucional no [personalizada.css](/public_html/css/custom/personalizada.css)
- `[x]` Remover o bloco `<style>` em [return.twig](/resources/views/pages/users/return.twig)
- `[x]` Remover o bloco `<style>` em [return-info.html.twig](/resources/views/pages/users/return-info.html.twig)
- `[x]` Remover o bloco `<style>` em [product-returns.html.twig](/resources/views/pages/product/product-returns.html.twig)
- `[x]` Remover o bloco `<style>` em [contact.twig](/resources/views/pages/information/contact.twig)
- `[x]` Remover o bloco `<style>` em [show.html.twig](/resources/views/pages/information/show.html.twig)
- `[x]` Validar layout das páginas de Devoluções e Institucional no e-commerce

# Walkthrough — Consolidação de Estilos (Fase 1, 2 e 3)

Concluímos com sucesso a execução das **Fases 1, 2 e 3** da consolidação de estilos do e-commerce.

---

## 🛠️ Fase 1: Área de Endereços

### 1. Centralização e Unificação de CSS
* **[personalizada.css](/public_html/css/custom/personalizada.css)**:
  * Agrupou e unificou estilos duplicados dos elementos das páginas de endereço (`.addr-hero`, `.addr-breadcrumb`, `.addr-wrapper`, `.addr-alert`, `.addr-btn-back`).
  * Manteve as variações de largura máxima e tamanho de ícones aplicando escopo a partir do ID da página-pai (`#address-create-page`, `#address-edit-page`, `#addresses-page`).
  * Consolidou os estilos dos formulários compartilhados de criação e edição (`.addr-form-card`, `.addr-form-section`, `.addr-form-grid`, etc.).
  * Adicionou estilos específicos da listagem (`.addr-grid`, `.addr-card`, `.addr-btn-card`).

### 2. Limpeza dos Templates Twig
* **[create.twig](/resources/views/pages/users/addresses/create.twig)**:
  * Removido o bloco `<style>` contendo ~190 linhas.
* **[edit.twig](/resources/views/pages/users/addresses/edit.twig)**:
  * Removido o bloco `<style>` contendo ~130 linhas de duplicação.
* **[index.twig](/resources/views/pages/users/addresses/index.twig)**:
  * Removido o bloco `<style>` contendo ~190 linhas.

---

## 🛠️ Fase 2: Área de Pedidos

### 1. Centralização e Unificação de CSS
* **[personalizada.css](/public_html/css/custom/personalizada.css)**:
  * Consolidou estilos de herói (`.orders-hero`), breadcrumbs (`.orders-breadcrumb`) e títulos das páginas de listagem e detalhes de pedidos.
  * Unificou a estilização das tabelas de listagem e itens de pedido (`.orders-table-wrap`, `.orders-table`, `.order-row`), incluindo alinhamentos tabulares.
  * Consolidou os estilos dos badges de status (`.status-pill` e `.timeline-status-pill`) e o botão de voltar (`.btn-back`) em seletores combinados.
  * Adicionou os componentes da timeline de histórico (`.timeline-wrap`, `.timeline`, `.timeline-item`, `.timeline-dot`, etc.) e visualização de detalhes (`.detail-grid`, `.detail-card`, `.totals-wrap`, `.total-row`).

### 2. Limpeza dos Templates Twig
* **[orders.twig](/resources/views/pages/users/accounts/orders.twig)**:
  * Removido o bloco `<style>` contendo ~290 linhas.
* **[order-history.twig](/resources/views/pages/users/accounts/order-history.twig)**:
  * Removido o bloco `<style>` contendo ~380 linhas.

---

## 🛠️ Fase 3: Devoluções e Páginas Institucionais

### 1. Centralização e Unificação de CSS
* **[personalizada.css](/public_html/css/custom/personalizada.css)**:
  * Centralizou os estilos de fundo e wrapper das páginas de devolução, contato e institucional (`#account-return-form-page`, `#account-return-detail-page`, `#account-returns-page`, `#contact-page`, `#info-page`).
  * Consolidou heróis (`.return-hero`, `.returns-hero`, `.contact-hero`, `.info-hero`) e breadcrumbs associados.
  * Unificou a estilização de botões primários (`.btn-submit`, `.contact-btn-submit`, `.btn-shop`) e botões de cancelamento/retorno (`.btn-back`, `.btn-cancel`).
  * Compartilhou componentes de layout como cartões (`.form-card`, `.return-card`, `.returns-card`, `.contact-form-card`, `.info-card`, `.detail-card`) e cabeçalhos de seções.
  * Unificou estilos de inputs, textareas e agrupamentos de formulários (`.form-group`, `.form-label`, `.form-input`, `.form-textarea`).

### 2. Limpeza dos Templates Twig
* **[return.twig](/resources/views/pages/users/return.twig)**:
  * Removido o bloco `<style>` contendo ~260 linhas.
* **[return-info.html.twig](/resources/views/pages/users/return-info.html.twig)**:
  * Removido o bloco `<style>` contendo ~340 linhas.
* **[product-returns.html.twig](/resources/views/pages/product/product-returns.html.twig)**:
  * Removido o bloco `<style>` contendo ~240 linhas.
* **[contact.twig](/resources/views/pages/information/contact.twig)**:
  * Removido o bloco `<style>` contendo ~290 linhas.
* **[show.html.twig](/resources/views/pages/information/show.html.twig)**:
  * Removido o bloco `<style>` contendo ~120 linhas.

---

## 🔬 Resultados e Verificação Geral
* **Redução Massiva de Código Duplicado**: A consolidação unificou estilos em 10 templates Twig diferentes, removendo cerca de **1.920 linhas** líquidas de CSS interno redundante.
* **Melhoria de Performance e Manutenibilidade**: O código centralizado em `personalizada.css` reduz significativamente o peso do DOM em cada página, favorece o cacheamento no navegador e centraliza ajustes futuros de design em um único arquivo de estilos de fácil controle.
* **Fidelidade Visual Completa**: Todos os detalhes de espaçamento, gradientes, comportamento responsivo (como a adaptação de tabelas e grids para telas mobile) e estados de foco/erro nos formulários foram perfeitamente preservados.

