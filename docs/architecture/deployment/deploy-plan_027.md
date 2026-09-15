# DP-27: Plano de Implementação - Refatoração e Internacionalização do Módulo de Devoluções

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-25 21:40:02
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/27

## Descrição

# Plano de Implementação - Refatoração e Internacionalização do Módulo de Devoluções

Este plano detalha as alterações de estilo e de idioma (i18n) a serem aplicadas nas views do módulo de Devoluções: `index.html.twig` e `show.html.twig`.

## Alterações Propostas

### 1. Atualizações de Estilo em `components.css`

Adicionaremos as seguintes classes utilitárias para substituir os estilos inline nas views do módulo de devoluções:
- `.justify-between`: Alinhamento flex space-between (`justify-content: space-between !important;`).
- `.col-id-80` e `.col-order-100` e `.col-actions-100`: Classes de largura de colunas para tabelas.
- `.card-section-title`: Título de seção estilizado para os RMA cards.
- `.table-info`, `.tr-info` e `.tr-info-last`: Tabela e linhas informativas compactas.
- `.grid-details` e `.detail-label` e `.detail-value-pill`: Grades responsivas para detalhes de produto com pílulas.
- `.text-warning`: Cor de aviso amarelada (`color: #d97706 !important;`).
- `.border-top-dashed`: Borda superior tracejada com espaçamento.
- `.comment-quote-box`: Caixa estilizada em itálico para comentários de clientes.
- `.grid-timeline-layout`: Grid de duas colunas assimétrico para a linha do tempo (`grid-template-columns: 1.5fr 1fr; gap: 1.5rem;`).
- `.activity-list-timeline`: Contêiner com padding para histórico.
- `.activity-item-border`: Item do histórico com borda inferior e espaçamento.
- `.activity-time-info`: Data estilizada ao lado do status na linha do tempo.
- `.status-notify`: Pílula específica de cliente notificado (`background: #e0f2fe !important; color: #0369a1 !important;`).
- `.activity-comment-box`: Comentários internos do histórico com estilo compacto.
- `.form-checkbox-label-sm`: Rótulo de checkbox menor.
- `.resize-vertical`: Textarea com redimensionamento apenas vertical.
- `.stats-bar-flex` e `.stat-card-clean` e `.stat-icon-wrapper` e `.stat-title-sm` e `.stat-value-sm`: Contêineres e cartões para a barra de estatísticas de devoluções.

### 2. Mapeamento no Middleware de Idiomas

Mapearemos as rotas de devoluções administrativas em [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar o namespace `admin/returns`:
```php
'admin.returns.index'         => 'admin/returns',
'admin.returns.show'          => 'admin/returns',
'admin.returns.update_status' => 'admin/returns',
```

### 3. Criação de Arquivos de Idiomas (JSON)

Criaremos os arquivos JSON correspondentes ao namespace `admin/returns`:
- **Português**: [pt-br.admin.returns.json](/Locales/pt-br/pt-br.admin.returns.json)
- **Inglês**: [en-gb.admin.returns.json](/Locales/en-gb/en-gb.admin.returns.json)
- **Francês**: [fr-fr.admin.returns.json](/Locales/fr-fr/fr-fr.admin.returns.json)

As chaves cobrirão a lista de RMA, filtros, colunas de tabela, estatísticas, ficha do RMA detalhado, resumo do RMA, dados do cliente, produto, linha do tempo histórica, e formulário de atualização de status.

### 4. Substituição nas Views Twig

Refatoraremos as views para utilizar classes CSS globais e traduzir todas as strings estáticas:
- [index.html.twig](/resources/views/admin/sales/return/index.html.twig)
- [show.html.twig](/resources/views/admin/sales/return/show.html.twig)

---

## Detalhes das Alterações por Arquivo

### [MODIFY] [components.css](/public_html/css/admin/components.css)
Adicionar as novas classes utilitárias e regras para Devoluções.

### [MODIFY] [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)
Mapear as rotas de devolução para o namespace `admin/returns`.

### [NEW] JSON Locales de Devoluções
Criar os arquivos de tradução do módulo de devoluções nos três idiomas.

### [MODIFY] [index.html.twig](/resources/views/admin/sales/return/index.html.twig)
Substituir estilos inline por classes utilitárias e textos estáticos por `AdminLang` com fallbacks.

### [MODIFY] [show.html.twig](/resources/views/admin/sales/return/show.html.twig)
Substituir estilos inline por classes utilitárias e textos estáticos por `AdminLang` com fallbacks.

---

## Plano de Verificação

### Verificação Manual
1. Acessar a listagem de devoluções no painel e conferir a tabela e barra de filtros.
2. Acessar os detalhes de uma devolução (`show.html.twig`) e validar a linha do tempo, RMA e formulário de atualização.
3. Testar a tradução alternando o idioma entre pt-br, en-gb e fr-fr.

- `[x]` Adicionar classes de estilo e utilitários em components.css
- `[x]` Mapear rotas de clientes no AdminLanguageMiddleware.php
- `[x]` Criar pt-br.admin.customer.json
- `[x]` Criar en-gb.admin.customer.json
- `[x]` Criar fr-fr.admin.customer.json
- `[x]` Refatorar index.html.twig (CSS + i18n)
- `[x]` Refatorar _form.html.twig (CSS + i18n)
- `[x]` Refatorar create.html.twig (CSS + i18n)
- `[x]` Refatorar edit.html.twig (CSS + i18n)
- `[x]` Refatorar show.html.twig (CSS + i18n)
- `[x]` Validar as modificações
- `[x]` Adicionar classes adicionais em components.css (checkbox/w-50)
- `[x]` Mapear rotas de endereços de clientes no AdminLanguageMiddleware.php
- `[x]` Estender traduções do cliente com chaves de endereço (pt-br, en-gb, fr-fr)
- `[x]` Refatorar Address/create.html.twig (CSS + i18n)
- `[x]` Refatorar Address/edit.html.twig (CSS + i18n)
- `[x]` Validar as novas modificações de endereços
- `[x]` Atualizar base_auth.html.twig (container_class block)
- `[x]` Adicionar classes de autenticação no components.css
- `[x]` Mapear rotas de autenticação no AdminLanguageMiddleware.php
- `[x]` Criar pt-br.admin.auth.json
- `[x]` Criar en-gb.admin.auth.json
- `[x]` Criar fr-fr.admin.auth.json
- `[x]` Refatorar setup.html.twig (CSS + i18n)
- `[x]` Refatorar error.html.twig (CSS + i18n)
- `[x]` Refatorar login.html.twig (CSS + i18n)
- `[x]` Validar as modificações de autenticação
- `[x]` Adicionar classes de devoluções no components.css
- `[x]` Mapear rotas de devoluções no AdminLanguageMiddleware.php
- `[x]` Criar pt-br.admin.returns.json
- `[x]` Criar en-gb.admin.returns.json
- `[x]` Criar fr-fr.admin.returns.json
- `[x]` Refatorar Return/index.html.twig (CSS + i18n)
- `[x]` Refatorar Return/show.html.twig (CSS + i18n)
- `[x]` Validar as modificações de devoluções

# Walkthrough - Refatoração e Internacionalização dos Módulos de Clientes, Endereços, Autenticação e Devoluções

Concluímos com sucesso a remoção de todos os estilos CSS inline e a internacionalização (i18n) completa das views do módulo de Clientes (`index.html.twig`, `_form.html.twig`, `create.html.twig`, `edit.html.twig`, `show.html.twig`), do submódulo de Endereços (`Address/create.html.twig` e `Address/edit.html.twig`), do módulo de Autenticação Admin (`login.html.twig`, `setup.html.twig` e `error.html.twig`), e do módulo de Devoluções (`sales/return/index.html.twig` e `sales/return/show.html.twig`).

## Alterações Realizadas

### 1. Refatoração de Estilos (CSS)
- **Folha de Estilos**: Integramos e reutilizamos novas classes no arquivo global [components.css](/public_html/css/admin/components.css) para remover os estilos inline:
  - `.is-invalid`: Borda de erro nos inputs.
  - `.align-start`: Alinhamento flex-start.
  - `.flex-column` e `.gap-3`: Layouts flex organizados.
  - `.font-medium` e `.text-success`: Cores e pesos de fontes padronizados.
  - `.address-box` e `.address-box.address-default`: Layouts específicos para o Livro de Endereços do cliente.
  - `.badge-success` e `.badge-primary`: Emblemas indicando endereço padrão.
  - `.empty-box`: Layout tracejado para estados vazios.
  - `.status-info`: Pílula com o status de pedidos no histórico do cliente.
  - `.max-w-50`: Largura máxima de 50% para campos no formulário de endereço.
  - `.form-checkbox` e `.form-checkbox-label`: Estilização padronizada para checkbox de endereço padrão e seus rótulos.
  - `.auth-container-large`: Largura máxima de 580px para o container de autenticação da instalação.
  - `.auth-card-large`: Card estendido com padding de `2.5rem` e sombras profundas.
  - `.auth-logo-emoji`: Estilo com animação `@keyframes bounce` para o emoji de instalação.
  - `.auth-logo-title` e `.auth-logo-subtitle`: Tipografia padronizada em conformidade com as variáveis do tema.
  - `.form-group-flex`: Direção flex em coluna para organizar rótulos e campos dos formulários.
  - `.auth-btn`: Botão em bloco com padding e fonte aumentados.
  - `.auth-error-icon`: Alinhamento para o ícone de aviso de segurança.
  - `.text-danger`: Vermelho padrão do tema (`#ef4444`).
  - `.justify-between`: Classe utilitária flex space-between.
  - `.col-id-80`, `.col-order-100`, `.col-actions-100`: Larguras padrão de colunas para as tabelas.
  - `.card-section-title`: Rótulo de título de cartões do RMA.
  - `.table-info`, `.tr-info`, `.tr-info-last`: Estilos de tabelas e linhas informativas de RMA.
  - `.grid-details`, `.detail-label`, `.detail-value-pill`: Exibição de detalhes do produto devolvido.
  - `.text-warning`: Cor de status pendente/alerta do tema (`#d97706`).
  - `.border-top-dashed`: Linha tracejada de separação de comentários do cliente.
  - `.comment-quote-box`: Caixa com citação em itálico de observações do comprador.
  - `.grid-timeline-layout`: Grid de duas colunas assimétrico para a linha do tempo e formulário de ação.
  - `.activity-list-timeline`, `.activity-item-border`, `.activity-time-info`: Organização do histórico e linha do tempo de RMA.
  - `.status-notify`: Pílula de status de notificação enviada ao cliente.
  - `.activity-comment-box`: Exibição compacta das mensagens internas do histórico.
  - `.form-checkbox-label-sm` e `.resize-vertical`: Rótulos menores de checkbox e textarea redimensionável verticalmente.
  - `.stats-bar-flex`, `.stat-card-clean`, `.stat-icon-wrapper`, `.stat-title-sm`, `.stat-value-sm`: Elementos do painel superior de estatísticas de RMA.
  - `.grid-filters-returns`: Filtros de RMA com alinhamento na base.
  - `.text-decoration-none`: Atalho utilitário para links sem sublinhado.
- **Views Twig**: Todos os atributos inline `style="..."` foram removidos e substituídos pelas classes CSS correspondentes em todas as views. No arquivo de layout [base_auth.html.twig](/resources/views/admin/layouts/base_auth.html.twig), adicionamos o bloco `container_class` para permitir o redimensionamento dinâmico sem estilos inline.

### 2. Internacionalização (i18n)
- **Middleware**: Mapeamos todas as rotas do cliente, endereços, rotas de autenticação (login/setup) e as rotas de devoluções em [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar os namespaces de tradução `admin/customer`, `admin/auth` e `admin/returns`.
- **Arquivos JSON de Tradução**: Criamos e populamos arquivos JSON estruturados nos três idiomas oficiais da loja:
  - **Clientes / Endereços**: [pt-br.admin.customer.json](/Locales/pt-br/pt-br.admin.customer.json), [en-gb.admin.customer.json](/Locales/en-gb/en-gb.admin.customer.json) e [fr-fr.admin.customer.json](/Locales/fr-fr/fr-fr.admin.customer.json).
  - **Autenticação Admin**: [pt-br.admin.auth.json](/Locales/pt-br/pt-br.admin.auth.json), [en-gb.admin.auth.json](/Locales/en-gb/en-gb.admin.auth.json) e [fr-fr.admin.auth.json](/Locales/fr-fr/fr-fr.admin.auth.json).
  - **Devoluções (RMA)**: [pt-br.admin.returns.json](/Locales/pt-br/pt-br.admin.returns.json), [en-gb.admin.returns.json](/Locales/en-gb/en-gb.admin.returns.json) e [fr-fr.admin.returns.json](/Locales/fr-fr/fr-fr.admin.returns.json).
- **Views Twig**: Substituímos os textos hardcoded de todas as páginas por referências ao objeto dinâmico `AdminLang` com fallbacks amigáveis. Nos avisos dinâmicos da página de erro de login e na linha do tempo de devoluções, utilizamos os filtros `|format` e `|raw` do Twig para injetar os valores traduzidos mantendo a estilização.

---

## Verificação e Testes

1. **Validação de Sintaxe PHP**: O middleware de idiomas e arquivos PHP associados foram validados sem erros.
2. **Testes de Integração de Idiomas**: Executamos com sucesso a suíte de testes de linguagem do admin.
3. **Teste de Renderização de Views**: Desenvolvemos e executamos scripts de testes temporários para as views do Cliente, Endereços, Autenticação (Login, Setup, Error) e Devoluções (Lista, Detalhes) simulando requisições em todos os idiomas. Todos os testes de compilação, carregamento de chaves de tradução e renderização passaram com sucesso.

