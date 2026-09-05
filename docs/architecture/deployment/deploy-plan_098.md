# DP-98: Proposta: Adicionar uma funcionalidade de auditoria e monitoramento de acessos no Dashboard, cruciais para a plataforma.

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-30 14:28:42
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/98

## Descrição

### 🚀 Principais Benefícios

1. **Segurança & Detecção de Ameaças**: Identificação de acessos suspeitos, tentativas de ataque (força bruta, scanning), múltiplos acessos de um mesmo IP e requisições a rotas sensíveis.
2. **Inteligência de Tráfego & Analytics**: Entendimento do perfil dos visitantes (navegador, sistema operacional, dispositivo móvel vs. desktop) e das rotas/páginas mais requisitadas em tempo real.
3. **Rastreabilidade & Conformidade (LGPD)**: Histórico claro de quem (usuário autenticado ou visitante anônimo) acessou ou executou determinadas ações no sistema.

---

### 🧩 O que já temos na arquitetura e como podemos implementar

O backend já conta com a base do [`AuditLoggerService.php`](file:///var/www/html/agsonhos/backend/core/Services/Audit/AuditLoggerService.php), que já possui:
- Persistência na tabela `tbkk_audit_logs` (MySQL) com suporte a mensageria assíncrona (RabbitMQ) e fallback para arquivo local;
- Higienização de dados pessoais sensíveis via [`LgpdSanitizer.php`](file:///var/www/html/agsonhos/backend/core/Support/LgpdSanitizer.php);
- Campos prontos para `ip`, `user_agent`, `username`, `event` e `payload`.

#### Plano de Implementação Sugerido:

1. **Middleware de Auditoria de Requisições (`RequestAuditMiddleware`)**:
   - Intercepta as requisições HTTP de forma transparente;
   - Captura IP (com suporte a proxies/Cloudflare via `X-Forwarded-For`), User-Agent (identificando navegador e SO), Método HTTP (GET, POST), rota acessada e status code de resposta;
   - Filtra e ignora requisições de arquivos estáticos (`.css`, `.js`, imagens) para não poluir o banco de dados.

2. **Visualização no Dashboard Administrativo**:
   - **No Dashboard Principal**: Novo widget com cards de estatísticas (Top Navegadores, Visitas Hoje, IPs Únicos) e uma tabela com os **"Últimos Acessos / Visitantes em Tempo Real"**;
   - **Página de Auditoria Dedicada (`/admin/auditoria`)**: Tabela completa com paginação, filtros por data, IP, usuário ou tipo de requisição, e visualização dos detalhes do payload.

---
# Implementação da Página de Auditoria & Logs de Acesso no Dashboard

Este plano detalha a criação da funcionalidade completa de **Auditoria e Monitoramento de Acessos** no painel administrativo, com middleware de captura de visitantes/requisições (IP, Navegador, SO, Rota, Usuário, Status HTTP), controle de acesso granular baseado em papéis (RBAC com a permissão `system/audit`), atalhos condicionais no Dashboard e na Sidebar, e página dedicada com estatísticas e filtros avançados.

---

## User Review Required

> [!IMPORTANT]
> **Privilégios e Permissões:**
> - A permissão `system/audit` será registrada no sistema.
> - O **Super Administrator (ID = 1)** terá acesso automático total.
> - Outros grupos de usuários só verão o atalho no Dashboard, o item no menu lateral e só conseguirão acessar a rota `/auditoria` se a permissão `system/audit` estiver explicitamente marcada no cadastro do seu grupo/papel.

> [!TIP]
> **Performance e Privacidade (LGPD):**
> - O middleware de auditoria ignorará automaticamente arquivos estáticos (`.css`, `.js`, imagens, fontes) para não sobrecarregar o banco de dados.
> - Todos os dados capturados passarão pela sanitização LGPD (`LgpdSanitizer`) já integrada ao `AuditLoggerService`.

---

## Proposed Changes

### 1. Backend Core & Middleware

#### [NEW] [RequestAuditMiddleware.php](file:///var/www/html/agsonhos/backend/core/Auth/Middleware/RequestAuditMiddleware.php)
- Intercepta requisições HTTP tanto na loja quanto no painel administrativo.
- Extrai IP real (considerando cabeçalhos `CF-Connecting-IP`, `X-Forwarded-For`, `REMOTE_ADDR`).
- Realiza o parsing de User-Agent (identificando Navegador: Chrome, Firefox, Safari, Edge, Opera, Bots; Sistema Operacional e tipo de dispositivo: Desktop, Mobile).
- Identifica usuário autenticado (colaborador admin ou cliente logado) ou visitante anônimo.
- Captura tempo de resposta da requisição, código de status HTTP (200, 302, 404, 500), método HTTP e URI.
- Envia os dados para persistência através do `AuditLoggerService`.

#### [MODIFY] [AuditLoggerService.php](file:///var/www/html/agsonhos/backend/core/Services/Audit/AuditLoggerService.php)
- Adicionar suporte a consultas paginadas com filtros (por IP, usuário, evento, data inicial e data final).
- Adicionar método `getAuditStats(int $storeId = 1): array` para retornar métricas consolidadas (Total de requisições hoje, IPs únicos, distribuição de navegadores e status codes).
- Adicionar método `getAuditLogById(int $id, int $storeId = 1): ?array` para visualização detalhada de uma requisição específica.

---

### 2. Controle de Acesso e Permissões (RBAC)

#### [MODIFY] [AdminSessionMiddleware.php](file:///var/www/html/agsonhos/backend/core/Auth/Middleware/AdminSessionMiddleware.php)
- Mapear as rotas de auditoria no `ROUTE_PERMISSION_MAP`:
  - `'admin.audit.list' => 'system/audit'`
  - `'admin.audit.view' => 'system/audit'`

#### [MODIFY] [CreateUserGroupAction.php](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/User/UserGroup/CreateUserGroupAction.php)
#### [MODIFY] [EditUserGroupAction.php](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/User/UserGroup/EditUserGroupAction.php)
- Adicionar `'system/audit' => 'Auditoria & Logs de Acesso'` na lista de módulos disponíveis para concessão de permissões.

---

### 3. Controllers Administrativos & Rotas

#### [NEW] [ListAuditLogsAction.php](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/Audit/ListAuditLogsAction.php)
- Controller responsável por carregar os logs filtrados, paginação e resumo de estatísticas para a tela de auditoria.

#### [NEW] [ViewAuditLogDetailAction.php](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/Audit/ViewAuditLogDetailAction.php)
- Endpoint para carregar os detalhes completos do payload em formato JSON formatado para visualização em modal/detalhe.

#### [MODIFY] [Routes.php](file:///var/www/html/agsonhos/backend/Config/Routes.php)
- Registrar as rotas administrativas protegidas:
  - `GET /auditoria` -> `ListAuditLogsAction`
  - `GET /auditoria/{id:[0-9]+}` -> `ViewAuditLogDetailAction`

---

### 4. Interface e Visualização (Views & Locales)

#### [NEW] [index.html.twig (Audit View)](file:///var/www/html/agsonhos/backend/resources/views/admin/pages/audit/index.html.twig)
- **Cards de Métricas:** Total de acessos hoje, visitantes únicos (IPs), navegadores predominantes e taxa de status/erros.
- **Barra de Filtros:** Busca textual (IP, usuário, rota), filtro por tipo de evento e filtro de datas.
- **Tabela de Auditoria Moderna:**
  - Badge colorido de método HTTP (`GET`, `POST`, etc.) e status (`200`, `404`, `500`);
  - Ícones dos navegadores (Chrome, Firefox, Safari, Edge);
  - Tag de IP e localização / identificação;
  - Usuário com avatar/identificador ou flag de visitante;
  - Data/hora formatada;
  - Botão "Ver Detalhes" para abrir modal com payload sanitizado (LGPD).
- **Paginação integrada.**

#### [MODIFY] [index.html.twig (Dashboard)](file:///var/www/html/agsonhos/backend/resources/views/admin/pages/dashboard/index.html.twig)
- Adicionar o card de atalho rápido na seção **"Atalhos Rápidos do Sistema"**, exibido condicionalmente para Super Admins ou usuários com permissão `'system/audit'`:
  - Ícone de escudo/segurança (`fas fa-shield-alt`);
  - Título "Auditoria & Logs";
  - Subtítulo "Visitantes & Requisições".

#### [MODIFY] [base.html.twig](file:///var/www/html/agsonhos/backend/resources/views/admin/layouts/base.html.twig)
- Adicionar o item **"Auditoria & Logs"** no menu lateral (Sidebar), exibido condicionalmente para quem possui a permissão de acesso.

#### [MODIFY] [pt-br.admin.common.json](file:///var/www/html/agsonhos/backend/Locales/pt-br/pt-br.admin.common.json)
#### [MODIFY] [en-gb.admin.common.json](file:///var/www/html/agsonhos/backend/Locales/en-gb/en-gb.admin.common.json)
#### [MODIFY] [fr-fr.admin.common.json](file:///var/www/html/agsonhos/backend/Locales/fr-fr/fr-fr.admin.common.json)
- Adicionar traduções para o item de menu "Auditoria & Logs".

---

## Verification Plan

### Automated Tests
1. **Teste de Unidade e Integração (`tests/Validation/AuditLogValidationTest.php`):**
   - Testar o `RequestAuditMiddleware` gerando entradas de auditoria com IP, User-Agent, método e status code.
   - Testar os métodos de filtros e estatísticas do `AuditLoggerService`.
   - Testar o bloqueio RBAC: usuário sem permissão `system/audit` recebe 403 Forbidden ao acessar `/auditoria`, enquanto Super Admin ou usuário autorizado acessa com sucesso (200 OK).
   - Executar os testes via:
     ```bash
     cd backend && ./vendor/bin/phpunit ../tests/Validation/AuditLogValidationTest.php
     ```

2. **Execução de Toda a Suíte de Testes:**
   - Garantir que nenhum teste existente seja quebrado:
     ```bash
     cd backend && ./vendor/bin/phpunit
     ```

### Manual Verification
1. Fazer login como Super Admin no painel administrativo e conferir o novo atalho na grade de atalhos rápidos e no menu lateral.
2. Clicar no atalho e navegar pela página de Auditoria (`/admin/auditoria`).
3. Validar a exibição correta dos logs recentes com IP, Navegador, Método, Rota e Modal de Detalhes.
4. Testar a busca e filtros por IP, evento e data.

# Implementação de Auditoria & Monitoramento de Logs no Dashboard

A funcionalidade de **Auditoria e Logs de Acesso** foi implementada com sucesso na plataforma AgSonhos, permitindo o rastreamento em tempo real de visitantes, endereços IP, navegadores, sistemas operacionais, dispositivos, requisições HTTP e controle de acesso baseado em papéis (RBAC).

---

## 🛠️ Modificações Realizadas

### 1. Middleware de Rastreamento de Requisições & Visitantes
- **[`RequestAuditMiddleware.php`](file:///var/www/html/agsonhos/backend/core/Auth/Middleware/RequestAuditMiddleware.php)**:
  - Captura transparente de IP real (com suporte a Cloudflare, Proxies e Nginx via `CF-Connecting-IP`, `X-Forwarded-For`, `X-Real-IP`);
  - Parser inteligente de User-Agent: identifica navegadores (*Chrome, Edge, Safari, Firefox, Opera, Bots*), versões, sistemas operacionais (*Windows, macOS, Linux, Android, iOS*) e tipos de dispositivos (*Desktop, Mobile, Tablet, Bot*);
  - Identificação de usuários autenticados (`ADMIN: <username>`, `CLIENTE: <email>` ou `VISITANTE`);
  - Medição de tempo de resposta da requisição (em ms) e código de status HTTP (200, 302, 403, 404, 500);
  - Filtro para ignorar requisições de arquivos estáticos (`.css`, `.js`, imagens, fontes).

### 2. Serviço de Auditoria & Persistência (LGPD Compliant)
- **[`AuditLoggerService.php`](file:///var/www/html/agsonhos/backend/core/Services/Audit/AuditLoggerService.php)**:
  - Adicionados métodos de consulta paginada com filtros (`getFilteredAuditLogs`);
  - Adicionado cálculo de contagem com filtros (`getTotalAuditLogsCount`);
  - Adicionado compilador de métricas e estatísticas (`getAuditStats` - total de requisições hoje, histórico geral, visitantes únicos em 24h e divisão por navegadores);
  - Adicionado método para buscar payload detalhado por ID (`getAuditLogById`).

### 3. Controle de Acesso e Permissões (RBAC)
- **[`AdminSessionMiddleware.php`](file:///var/www/html/agsonhos/backend/core/Auth/Middleware/AdminSessionMiddleware.php)**:
  - Mapeadas as rotas `admin.audit.list` e `admin.audit.view` para a permissão `system/audit`.
  - Bloqueio automático com **403 Forbidden** para colaboradores sem a permissão `system/audit`, e acesso liberado (200 OK) para Super Administrators (ID 1) e usuários autorizados.
- **[`CreateUserGroupAction.php`](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/User/UserGroup/CreateUserGroupAction.php)** & **[`EditUserGroupAction.php`](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/User/UserGroup/EditUserGroupAction.php)**:
  - Registrado o módulo `'system/audit' => 'Auditoria & Logs de Acesso'` para concessão de privilégios nos perfis de colaboradores.

### 4. Controllers e Rotas do Painel Administrativo
- **[`ListAuditLogsAction.php`](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/Audit/ListAuditLogsAction.php)**: Controller principal da tela de auditoria.
- **[`ViewAuditLogDetailAction.php`](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/Audit/ViewAuditLogDetailAction.php)**: Endpoint para obtenção dos dados do payload higienizado.
- **[`Routes.php`](file:///var/www/html/agsonhos/backend/Config/Routes.php)**: Rotas `/auditoria` e `/auditoria/{id}` registradas no grupo protegido.

### 5. Interface Visual (UI) & Atalhos Condicionais
- **Página Dedicada de Auditoria:** **[`admin/pages/audit/index.html.twig`](file:///var/www/html/agsonhos/backend/resources/views/admin/pages/audit/index.html.twig)**
  - 4 Cards de Estatísticas com contadores dinâmicos;
  - Formulário com filtros por busca textual (IP, usuário, rota), tipo de evento e intervalo de datas;
  - Tabela moderna com badges visuais de métodos HTTP (GET, POST, PUT, DELETE), status codes (2xx, 4xx, 5xx), ícones dos navegadores e SOs;
  - Modal interativo com visualização formatada do JSON de cada requisição;
  - Paginação completa.
- **Atalho Rápido no Dashboard:** **[`admin/pages/dashboard/index.html.twig`](file:///var/www/html/agsonhos/backend/resources/views/admin/pages/dashboard/index.html.twig)**
  - Adicionado o card *"Auditoria & Logs (Visitantes & Requisições)"* exibido apenas para quem possui permissão.
- **Menu Lateral (Sidebar):** **[`admin/layouts/base.html.twig`](file:///var/www/html/agsonhos/backend/resources/views/admin/layouts/base.html.twig)**
  - Adicionado link direto com ícone de escudo para a página de auditoria.
- **Internacionalização (Locales):** Adicionadas as traduções para o item nos arquivos `pt-br`, `en-gb` e `fr-fr`.

---

## 🧪 Validação & Testes

### 1. Testes Unitários e de Integração
- **[`tests/Validation/AuditLogValidationTest.php`](file:///var/www/html/agsonhos/tests/Validation/AuditLogValidationTest.php)**:
  - ✅ Validação da interceptação do middleware e extração de IP e metadados de User-Agent.
  - ✅ Validação de exclusão de arquivos estáticos.
  - ✅ Validação de filtragem, paginação e estatísticas do serviço.
  - ✅ Validação da regra RBAC (403 Forbidden para não autorizados e 200 OK para Super Admin).

### 2. Execução da Suíte de Testes
```bash
./vendor/bin/phpunit
# OK (105 testes, 392 asserções)

php vendor/bin/phpstan analyse core/Auth/Middleware/RequestAuditMiddleware.php core/Admin/Controllers/Actions/Audit/ core/Services/Audit/AuditLoggerService.php --no-progress
# [OK] No errors
```

