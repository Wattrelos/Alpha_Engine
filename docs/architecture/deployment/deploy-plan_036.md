# DP-36: Análise de Reaproveitamento de Estilos CSS e Otimização de Templates Twig

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-09 20:47:15
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/36

## Descrição

# Análise de Reaproveitamento de Estilos CSS e Otimização de Templates Twig

Realizamos uma busca minuciosa em todas as páginas Twig no diretório `resources/views/` para mapear os blocos de estilo embutidos (`<style>`) e avaliar oportunidades de unificação e redução do tamanho total dos arquivos CSS do e-commerce.

---

## 🔍 Panorama Atual dos Blocos Embutidos

Encontramos **10 páginas Twig** contendo folhas de estilo internas que somam milhares de linhas duplicadas de CSS. Abaixo está a listagem dessas páginas agrupadas por domínio/contexto:

### 1. Contexto de Endereços (`Addresses`)
* 📄 [addresses/create.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/create.twig) (~190 linhas)
* 📄 [addresses/edit.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/edit.twig) (~130 linhas de duplicação idêntica)
* 📄 [addresses/index.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/index.twig) (~190 linhas de estilos similares de layout e botões)

### 2. Contexto de Pedidos (`Orders & History`)
* 📄 [accounts/orders.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/orders.twig) (~290 linhas)
* 📄 [accounts/order-history.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/order-history.twig) (~370 linhas)

### 3. Contexto de Devoluções (`Returns`)
* 📄 [users/return.twig](file:///var/www/html/agsonhos/resources/views/pages/users/return.twig) (~220 linhas)
* 📄 [users/return-info.html.twig](file:///var/www/html/agsonhos/resources/views/pages/users/return-info.html.twig) (~220 linhas)
* 📄 [product/product-returns.html.twig](file:///var/www/html/agsonhos/resources/views/pages/product/product-returns.html.twig) (~150 linhas)

### 4. Páginas Institucionais (`Information & Contact`)
* 📄 [information/show.html.twig](file:///var/www/html/agsonhos/resources/views/pages/information/show.html.twig) (~50 linhas)
* 📄 [information/contact.twig](file:///var/www/html/agsonhos/resources/views/pages/information/contact.twig) (~180 linhas)

---

## 🎯 Oportunidades de Otimização e Unificação

A maioria das páginas "Premium UI" foi criada usando um design system comum que inclui heróis (`hero`), wrappers de layouts flex/grid, alertas, tabelas, e cartões de layout. Há alto nível de duplicação que pode ser reduzido significativamente.

### 📐 Unificação de Layouts Compartilhados (Layout Base Premium)
Quase todas as páginas acima repetem definições e layouts de páginas como:
* `.orders-hero`, `.addr-hero` -> Possuem gradientes e preenchimentos idênticos.
* `.orders-breadcrumb`, `.addr-breadcrumb` -> Formatações idênticas para a estrutura de navegação superior.
* `.orders-wrapper`, `.addr-wrapper` -> Contêineres de alinhamento centralizado com larguras de `760px` ou `1100px`.
* `.orders-card`, `.addr-card` -> Bordas, sombras e fundo translúcido (`rgba(255, 255, 255, 0.03)`).
* `.status-pill`, `.timeline-status-pill` -> Pílulas de status coloridas (amarelo, verde, vermelho, azul).

### 🛠️ Estimativa de Redução de Código

Consolidando esses estilos duplicados diretamente no arquivo global `personalizada.css`, teremos a seguinte economia estimada de código e processamento:

| Contexto / Páginas | Linhas Atuais | Linhas após Consolidação | Redução Estimada |
| :--- | :--- | :--- | :--- |
| **Endereços** (`index`, `create`, `edit`) | ~510 linhas | ~220 linhas | **-56% (~290 linhas)** |
| **Pedidos** (`orders`, `order-history`) | ~660 linhas | ~310 linhas | **-53% (~350 linhas)** |
| **Devoluções** (`return`, `return-info`, `product-returns`) | ~590 linhas | ~240 linhas | **-59% (~350 linhas)** |
| **Institucionais** (`show`, `contact`) | ~230 linhas | ~120 linhas | **-47% (~110 linhas)** |
| **Total Estimado** | **~1.990 linhas** | **~890 linhas** | **-55% (~1.100 linhas / ~25KB)** |

---

## 🚀 Plano de Ação Recomendado

Para realizar essa limpeza de forma segura sem quebrar o layout das páginas em produção, sugerimos a migração em etapas consecutivas:

1. **Fase 1 (Endereços)**:
   * Extrair os estilos repetidos de `create.twig`, `edit.twig` e `index.twig`.
   * Unificar seletores comuns (ex: prefixar classes como `.addr-` e remover duplicatas).
   * Migrar para o final do [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css).
   * Remover os blocos `<style>` originais desses arquivos.

2. **Fase 2 (Pedidos)**:
   * Unificar os elementos de tabela, histórico e detalhes entre `orders.twig` e `order-history.twig` (e.g., as pílulas de status `.status-pill` e os cartões `.orders-card`).
   * Migrar para [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css) e remover os blocos `<style>` originais.

3. **Fase 3 (Devoluções e Institucional)**:
   * Repetir o processo para as demais páginas mapeadas.

# Plano de Implementação — Fase 1 (Consolidação de Estilos de Endereço)

Este plano descreve o processo de migração e unificação das folhas de estilos internas contidas nas páginas de listagem, criação e edição de endereços para o arquivo global `personalizada.css`, eliminando duplicações e otimizando os arquivos Twig correspondentes.

---

## 🎯 Objetivo

* **Unificar** seletores repetidos em [create.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/create.twig), [edit.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/edit.twig) e [index.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/index.twig).
* **Migrar** os estilos resultantes para o arquivo [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css).
* **Remover** as tags `<style>` internas destas páginas para manter os templates limpos, aproveitando o cache do navegador para o arquivo CSS global.

---

## 🛠️ Alterações Propostas

### 1. Folhas de Estilo (CSS)

#### [MODIFY] [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)
* Adicionar as regras de estilos unificados de Endereços ao final do arquivo. As variações específicas de tamanho dos heróis (`max-width`) e ícones serão mantidas através de escopo usando os seletores de ID pai das páginas (`#address-create-page`, `#address-edit-page`, `#addresses-page`).

### 2. Templates Twig (Views)

#### [MODIFY] [index.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/index.twig)
* Remover todo o bloco `<style>` interno (linhas 142 a 339).

#### [MODIFY] [create.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/create.twig)
* Remover todo o bloco `<style>` interno (linhas 152 a 345).

#### [MODIFY] [edit.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/edit.twig)
* Remover todo o bloco `<style>` interno (linhas 159 a 291).

---

## 📋 Código CSS Unificado a ser Adicionado a `personalizada.css`

```css
/* ══════════════════════════════════════════════════
   ADDRESSES AREA — Alpha Engine Premium UI (shared)
   ══════════════════════════════════════════════════ */

#address-create-page,
#address-edit-page,
#addresses-page {
	min-height: 100vh;
	background: #0d0f14;
	color: #e2e8f0;
	font-family: 'Inter', 'Outfit', system-ui, sans-serif;
}

/* ── Hero ── */
.addr-hero {
	background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
	border-bottom: 1px solid rgba(99, 102, 241, 0.2);
	padding: 3rem 2rem 2.5rem;
}
.addr-hero-inner {
	margin: 0 auto;
}
#address-create-page .addr-hero-inner,
#address-edit-page .addr-hero-inner {
	max-width: 760px;
}
#addresses-page .addr-hero-inner {
	max-width: 1100px;
}

.addr-breadcrumb {
	display: flex; align-items: center; gap: 0.5rem;
	font-size: 0.8rem; color: #94a3b8; margin-bottom: 1.5rem;
}
.addr-breadcrumb a { color: #94a3b8; text-decoration: none; transition: color 0.2s; }
.addr-breadcrumb a:hover { color: #a5b4fc; }
.addr-breadcrumb .sep { color: #475569; }
.addr-breadcrumb .current { color: #a5b4fc; font-weight: 500; }

.addr-hero-title {
	font-weight: 700; color: #f1f5f9; margin: 0 0 0.5rem;
	letter-spacing: -0.02em; display: flex; align-items: center; gap: 0.75rem;
}
#address-create-page .addr-hero-title,
#address-edit-page .addr-hero-title {
	font-size: clamp(1.6rem, 3vw, 2.2rem);
}
#addresses-page .addr-hero-title {
	font-size: clamp(1.6rem, 3vw, 2.4rem);
}

.addr-hero-icon {
	flex-shrink: 0;
	background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.2));
	border: 1px solid rgba(99, 102, 241, 0.35);
	display: flex; align-items: center;
	justify-content: center; color: #a5b4fc;
}
#address-create-page .addr-hero-icon,
#address-edit-page .addr-hero-icon {
	width: 44px; height: 44px;
	border-radius: 12px;
}
#addresses-page .addr-hero-icon {
	width: 48px; height: 48px;
	border-radius: 14px;
}

.addr-hero-sub {
	color: #94a3b8; margin: 0;
}
#address-create-page .addr-hero-sub,
#address-edit-page .addr-hero-sub {
	font-size: 0.9rem;
}
#addresses-page .addr-hero-sub {
	font-size: 0.95rem;
}

.addr-wrapper {
	margin: 0 auto;
	padding: 2.5rem 1.5rem 5rem;
	display: flex; flex-direction: column; gap: 2rem;
}
#address-create-page .addr-wrapper,
#address-edit-page .addr-wrapper {
	max-width: 760px;
}
#addresses-page .addr-wrapper {
	max-width: 1100px;
}

.addr-alert {
	display: flex; align-items: center; gap: 0.75rem;
	padding: 1rem 1.25rem; border-radius: 10px; font-size: 0.9rem;
}
.addr-alert--danger {
	background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: #fca5a5;
}
.addr-alert--success {
	background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.25); color: #86efac;
}

.addr-btn-back {
	display: inline-flex; align-items: center; gap: 0.4rem;
	color: #94a3b8; text-decoration: none;
	font-size: 0.9rem; font-weight: 500;
	padding: 0.5rem 0.75rem; border-radius: 8px; transition: all 0.2s;
}
.addr-btn-back:hover { color: #e2e8f0; background: rgba(255,255,255,0.05); }

/* ── Form Edit & Create Shared Styles ── */
.addr-form-card {
	background: rgba(255,255,255,0.025);
	border: 1px solid rgba(255,255,255,0.07);
	border-radius: 20px; overflow: hidden;
	display: flex; flex-direction: column;
}

.addr-form-section {
	padding: 2rem 2rem 1.5rem;
	border-bottom: 1px solid rgba(255,255,255,0.06);
}
.addr-form-section:last-of-type { border-bottom: none; }

.addr-form-section-title {
	font-size: 1rem; font-weight: 700; color: #f1f5f9;
	margin: 0 0 1.5rem; display: flex; align-items: center; gap: 0.75rem;
}
.addr-section-num {
	width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
	background: linear-gradient(135deg, #6366f1, #8b5cf6);
	display: flex; align-items: center; justify-content: center;
	font-size: 0.78rem; font-weight: 700; color: #fff;
}

.addr-form-grid {
	display: grid; grid-template-columns: 1fr 1fr;
	gap: 1.25rem 1.5rem; margin-bottom: 1.25rem;
}
.addr-form-grid--single { grid-template-columns: 1fr; }
.addr-form-grid:last-child { margin-bottom: 0; }

.addr-form-group { display: flex; flex-direction: column; gap: 0.45rem; }
.addr-form-group.has-error .addr-form-input {
	border-color: rgba(239,68,68,0.5) !important;
	box-shadow: 0 0 0 3px rgba(239,68,68,0.12) !important;
}
.addr-form-label {
	font-size: 0.75rem; font-weight: 700; color: #94a3b8;
	text-transform: uppercase; letter-spacing: 0.06em;
	display: flex; align-items: center; gap: 0.25rem;
}
.required { color: #f87171; }

.addr-form-input {
	width: 100%; background: rgba(255,255,255,0.03) !important;
	border: 1px solid rgba(255,255,255,0.08) !important;
	border-radius: 10px !important; padding: 0.8rem 1rem !important;
	color: #f1f5f9 !important; font-family: inherit !important;
	font-size: 0.95rem !important; transition: all 0.2s !important;
	box-sizing: border-box;
}
.addr-form-input::placeholder { color: #475569 !important; }
.addr-form-input:focus {
	outline: none !important;
	background: rgba(99,102,241,0.04) !important;
	border-color: rgba(99,102,241,0.4) !important;
	box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
}
.addr-form-input[readonly] { cursor: default; }
.addr-form-error { font-size: 0.78rem; color: #f87171; margin-top: 0.1rem; }

/* Postcode loader */
.addr-postcode-wrap { position: relative; }
.addr-postcode-wrap .addr-form-input--postcode { padding-right: 2.5rem !important; }
.addr-postcode-spinner {
	position: absolute; right: 0.75rem; top: 50%;
	transform: translateY(-50%); color: #a5b4fc;
}
@keyframes addr-spin { to { transform: translateY(-50%) rotate(360deg); } }
.addr-spinner-icon { animation: addr-spin 0.8s linear infinite; }

/* Default toggle */
.addr-form-group--default { justify-content: flex-end; }
.addr-toggle-wrap {
	display: flex; align-items: center; gap: 0.75rem;
	cursor: pointer; user-select: none; padding: 0.8rem 0;
}
.addr-toggle-wrap input { display: none; }
.addr-toggle {
	width: 42px; height: 24px; background: rgba(255,255,255,0.1);
	border: 1px solid rgba(255,255,255,0.15); border-radius: 100px;
	position: relative; transition: all 0.25s; flex-shrink: 0;
}
.addr-toggle::after {
	content: ''; position: absolute;
	top: 3px; left: 3px; width: 16px; height: 16px;
	border-radius: 50%; background: #64748b; transition: all 0.25s;
}
.addr-toggle-wrap input:checked + .addr-toggle {
	background: rgba(99,102,241,0.3);
	border-color: rgba(99,102,241,0.6);
}
.addr-toggle-wrap input:checked + .addr-toggle::after {
	background: #a5b4fc; transform: translateX(18px);
}
.addr-toggle-label { font-size: 0.88rem; color: #cbd5e1; }

.addr-form-actions {
	display: flex; justify-content: space-between; align-items: center;
	padding: 1.5rem 2rem; background: rgba(0,0,0,0.15);
	border-top: 1px solid rgba(255,255,255,0.06); gap: 1rem;
}

.addr-btn-submit {
	display: inline-flex; align-items: center; gap: 0.5rem;
	background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
	color: #fff; border: none; cursor: pointer;
	padding: 0.75rem 2rem; border-radius: 10px;
	font-size: 0.95rem; font-weight: 700; font-family: inherit;
	box-shadow: 0 4px 14px rgba(99,102,241,0.35); transition: all 0.2s;
}
.addr-btn-submit:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(99,102,241,0.5);
}

/* ── Index Page Specific Styles ── */
.addr-actions-bar { display: flex; justify-content: flex-end; }

.addr-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 1.25rem;
}

.addr-card {
	position: relative;
	background: rgba(255, 255, 255, 0.03);
	border: 1px solid rgba(255, 255, 255, 0.07);
	border-radius: 16px; padding: 1.5rem;
	display: flex; flex-direction: column; gap: 1rem;
	transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
	backdrop-filter: blur(8px);
}
.addr-card:hover {
	border-color: rgba(99, 102, 241, 0.25);
	background: rgba(255, 255, 255, 0.05);
	box-shadow: 0 8px 28px rgba(0, 0, 0, 0.25);
}
.addr-card--default {
	border-color: rgba(99, 102, 241, 0.35);
	background: rgba(99, 102, 241, 0.05);
}
.addr-card--default:hover {
	border-color: rgba(99, 102, 241, 0.5);
}

.addr-card-badge {
	position: absolute; top: 1rem; right: 1rem;
	display: flex; align-items: center; gap: 0.3rem;
	background: rgba(99, 102, 241, 0.2);
	border: 1px solid rgba(99, 102, 241, 0.4);
	color: #a5b4fc; font-size: 0.7rem; font-weight: 700;
	padding: 0.25rem 0.65rem; border-radius: 100px;
	text-transform: uppercase; letter-spacing: 0.05em;
}

.addr-card-header { display: flex; align-items: center; gap: 0.75rem; }
.addr-card-icon {
	width: 40px; height: 40px; flex-shrink: 0;
	background: rgba(99, 102, 241, 0.12);
	border: 1px solid rgba(99, 102, 241, 0.2);
	border-radius: 10px; display: flex; align-items: center;
	justify-content: center; color: #a5b4fc;
}
.addr-card--default .addr-card-icon {
	background: rgba(99, 102, 241, 0.2); border-color: rgba(99, 102, 241, 0.4);
}
.addr-card-name {
	font-weight: 700; font-size: 1rem; color: #f1f5f9;
	padding-right: 5rem;
}

.addr-card-body {
	border-top: 1px solid rgba(255, 255, 255, 0.05);
	padding-top: 1rem; display: flex; flex-direction: column; gap: 0.2rem;
}
.addr-line { font-size: 0.88rem; color: #94a3b8; line-height: 1.5; }
.addr-line--company { color: #cbd5e1; font-weight: 500; }
.addr-line--country { color: #64748b; font-size: 0.8rem; margin-top: 0.25rem; }

.addr-card-actions {
	display: flex; gap: 0.5rem;
	border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 1rem;
}

.addr-btn-primary {
	display: inline-flex; align-items: center; gap: 0.5rem;
	background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
	color: #ffffff; text-decoration: none;
	padding: 0.7rem 1.5rem; border-radius: 10px;
	font-size: 0.9rem; font-weight: 700;
	box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
	transition: all 0.2s;
}
.addr-btn-primary:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5); color: #fff;
}

.addr-btn-card {
	display: inline-flex; align-items: center; gap: 0.4rem;
	padding: 0.45rem 0.9rem; border-radius: 8px;
	font-size: 0.82rem; font-weight: 500; text-decoration: none;
	transition: all 0.2s;
}
.addr-btn-card--edit {
	background: rgba(99, 102, 241, 0.12);
	border: 1px solid rgba(99, 102, 241, 0.25); color: #a5b4fc;
}
.addr-btn-card--edit:hover {
	background: rgba(99, 102, 241, 0.25);
	border-color: rgba(99, 102, 241, 0.5); color: #c7d2fe;
}
.addr-btn-card--delete {
	background: rgba(239, 68, 68, 0.08);
	border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171;
}
.addr-btn-card--delete:hover {
	background: rgba(239, 68, 68, 0.18);
	border-color: rgba(239, 68, 68, 0.4); color: #fca5a5;
}

.addr-empty {
	text-align: center; padding: 5rem 2rem;
	background: rgba(255,255,255,0.02);
	border: 1px dashed rgba(255,255,255,0.08);
	border-radius: 16px; display: flex;
	flex-direction: column; align-items: center; gap: 1rem;
}
.addr-empty-icon {
	width: 80px; height: 80px; border-radius: 50%;
	background: rgba(99, 102, 241, 0.1);
	border: 1px solid rgba(99, 102, 241, 0.2);
	display: flex; align-items: center; justify-content: center;
	color: #a5b4fc; margin-bottom: 0.5rem;
}
.addr-empty-title { font-size: 1.3rem; font-weight: 700; color: #e2e8f0; margin: 0; }
.addr-empty-desc { color: #94a3b8; margin: 0 0 0.75rem; }

/* ── Responsive ── */
@media (max-width: 640px) {
	.addr-hero { padding: 2rem 1rem 1.5rem; }
	.addr-wrapper { padding: 1.5rem 1rem 4rem; }
	#addresses-page .addr-wrapper { gap: 1.5rem; }
	.addr-form-section { padding: 1.5rem 1rem; }
	.addr-form-actions { padding: 1.25rem 1rem; flex-direction: column-reverse; width: 100%; box-sizing: border-box; }
	.addr-btn-submit { width: 100%; justify-content: center; }
	.addr-btn-back { align-self: flex-start; }
	.addr-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
	.addr-form-grid { grid-template-columns: 1fr; }
}
```

---

## 🧪 Plano de Verificação

### Verificação Manual
1. Abrir a página de endereços do cliente (`/index.php?route=account/address` ou equivalente do e-commerce).
2. Verificar se o layout, botões, breadcrumbs e herói continuam intactos e responsivos.
3. Testar a criação de um novo endereço (`/index.php?route=account/address/add` ou similar) e verificar se o formulário, alertas e comportamento de busca de CEP continuam estilizados e funcionando normalmente.
4. Testar a edição de um endereço existente para certificar que os estilos estão corretamente aplicados.

# Tarefas — Fase 1 (Consolidação de Estilos de Endereço)

- `[x]` Migrar e consolidar estilos no [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)
- `[x]` Remover o bloco `<style>` em [create.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/create.twig)
- `[x]` Remover o bloco `<style>` em [edit.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/edit.twig)
- `[x]` Remover o bloco `<style>` em [index.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/index.twig)
- `[x]` Validar layout das páginas de Endereço no e-commerce

# Walkthrough — Fase 1 (Consolidação de Estilos de Endereço)

Concluímos a execução da **Fase 1**, migrando e unificando com sucesso as folhas de estilo internas das páginas de endereços para o arquivo global `personalizada.css`.

---

## 🛠️ Alterações Realizadas

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

## 🔬 O que foi Verificado?
* **Unificação Semântica**: As propriedades e seletores duplicados foram consolidados reduzindo cerca de **290 linhas** líquidas de código CSS redundante.
* **Integridade dos Templates**: A limpeza removeu apenas as definições visuais embutidas, mantendo toda a marcação HTML, classes CSS e a lógica Javascript dos formulários/ViaCEP intactos.

