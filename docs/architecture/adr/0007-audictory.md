---
adr: 7
title: Implementação do Subsistema de Auditoria, Monitoramento de Logs e Observabilidade Ativa na Alpha Engine
status: Approved
date: 2026-08-30
authors:
  - Antigravity AI
  - Josias
impacted_components:
  - file: backend/core/Auth/Middleware/RequestAuditMiddleware.php
  - file: backend/core/Services/Audit/AuditLoggerService.php
  - file: backend/core/Support/LgpdSanitizer.php
  - file: backend/core/Admin/Controllers/Actions/Audit/ListAuditLogsAction.php
  - file: backend/core/Admin/Controllers/Actions/Audit/ViewAuditLogDetailAction.php
  - file: backend/resources/views/admin/pages/audit/index.html.twig
  - file: backend/resources/views/admin/pages/dashboard/index.html.twig
  - file: backend/resources/views/admin/layouts/base.html.twig
  - file: tests/Validation/AuditLogValidationTest.php
rules:
  compliance_lgpd: "Mandatory data sanitization via LgpdSanitizer"
  rbac_scope: "system/audit"
  bypass_static_assets: true
  persistence_strategy: "Multi-level resilient fallback (RabbitMQ -> MySQL tbkk_audit_logs -> Local File)"
---

# ADR 007: Implementação do Subsistema de Auditoria, Monitoramento de Logs e Observabilidade Ativa na Alpha Engine

## Status
Aprovado (2026-08-30)

## Contexto
Para mitigar proativamente tentativas de fraude (como adulteração de payloads de pedidos, manipulação indevida de preços, cupons ou dados de pagamento), vazamento de dados e abusos de requisições maliciosas na infraestrutura multi-tenant, a aplicação demandava um mecanismo robusto, imutável e centralizado de rastreabilidade de requisições. 

Anteriormente, o sistema dependia exclusivamente de logs de erro tradicionais orientados a arquivos texto, que são passivos e ineficientes para auditorias forenses, formação de provas jurídicas ou análise em tempo real de comportamento de acessos anômalos (ex: IP, User-Agent, Fingerprint, intenção dolosa).

Ademais, o design precisava atender estritamente aos seguintes requisitos não-funcionais:
1. **Performance e Baixo Footprint**: Não degradar o tempo de resposta das rotas públicas ou administrativas.
2. **Conformidade Legal (LGPD)**: Respeitar a privacidade de PII (*Personally Identifiable Information*) e Dados Pessoais Sensíveis nos termos da Lei Geral de Proteção de Dados (Lei nº 13.709/2018).
3. **Segurança de Acesso e Governança**: Restringir a visualização dos logs apenas a operadores e administradores com permissões explícitas através do modelo de controle de acesso baseado em papéis (RBAC).

---

## Decisão Arquitetural

Decidiu-se pela implementação de um subsistema nativo e ativo de auditoria acoplado à esteira de Middlewares da aplicação, integrado ao ecossistema Slim 4, com persistência multinível resiliente e interface administrativa Twig renderizada sob controle de acesso baseado em papéis (RBAC).

A solução é composta pelos seguintes pilares técnicos:

### 1. Camada de Interceptação (`RequestAuditMiddleware`)
Implementado middleware global (`Alpha\Auth\Middleware\RequestAuditMiddleware`) para capturar metadados detalhados de cada ciclo de Request/Response:
* **Identidade de Conexão**: IP real (resolvido de forma segura com suporte nativo a proxies reversos e cabeçalhos `CF-Connecting-IP`, `X-Forwarded-For`, `X-Real-IP`), User-Agent do navegador, Sistema Operacional, Dispositivo e Método HTTP.
* **Métricas de Performance**: Tempo exato de processamento da requisição em milissegundos (`duration_ms`), Rota solicitada e *Status Code* HTTP retornado (200, 302, 403, 404, 500).
* **Escopo do Ator**: Vinculação automática do evento ao perfil do originador (`ADMIN: <username>`, `CLIENTE: <email>` ou `VISITANTE`).
* **Otimização e Bypass de Assets**: Ignoração automatizada de requisições para arquivos estáticos (`.css`, `.js`, imagens, fontes, `.map`), prevenindo a inflação desnecessária do banco de dados e mantendo a performance da aplicação.
* **Sanitização LGPD**: Passagem obrigatória do payload pela classe utilitária `Alpha\Support\LgpdSanitizer` antes da persistência, garantindo o mascaramento e a anonimização de senhas, CPFs, tokens ou dados pessoais sensíveis.

### 2. Motor de Persistência Resiliente (`AuditLoggerService`)
O serviço `Alpha\Services\Audit\AuditLoggerService` provê estratégia multinível:
* **Nível 1 (Alta Performance)**: Publicação assíncrona na fila RabbitMQ (`audit_events`) quando disponível.
* **Nível 2 (Persistência MySQL)**: Gravação transacional na tabela `tbkk_audit_logs` com auto-provisionamento do schema e suporte a consultas paginadas e filtradas.
* **Nível 3 (Fallback Emergencial)**: Gravação local em arquivo append-only (`storage/logs/audit.log`) em caso de indisponibilidade de banco de dados.

### 3. Painel de Visualização e Telemetria (`/admin/auditoria`)
Criação de uma área administrativa dedicada e interativa (`Alpha\Admin\Controllers\Actions\Audit\ListAuditLogsAction` e `ViewAuditLogDetailAction`):
* **Cards de Métricas**: Contadores consolidados (Requisições no dia atual, Visitantes Únicos nas últimas 24 horas, Histórico Total de Eventos e Distribuição de Navegadores).
* **Mecanismo de Filtros**: Busca textual facetada (por IP, Usuário, URL, Payload), filtro por Tipo de Evento (`visitor.request`, `admin.request`, `http.404_not_found`, `http.500_error`, `http.403_forbidden`) e Intervalo de Datas.
* **Detalhamento**: Tabela interativa baseada em badges visuais para métodos HTTP e status, paginação completa e janela modal com formatação de JSON para inspeção do payload da requisição.

### 4. Governança e Restrição Baseada em Funções (RBAC)
O acesso à interface e aos dados brutos de auditoria foi totalmente encapsulado no sistema de privilégios da Alpha Engine:
* **Permissão Estrita**: Registro do módulo corporativo `system/audit` na matriz global de permissões (`AdminSessionMiddleware`, `CreateUserGroupAction` e `EditUserGroupAction`).
* **Interface Condicional**: O card de atalho rápido no Dashboard primário (*"Auditoria & Logs (Visitantes & Requisições)"*) e o link na barra lateral de navegação (Sidebar, identificado com o ícone `fas fa-shield-alt`) são renderizados apenas para usuários que possuem o privilégio ativo ou Super Administrators (ID 1).
* **Proteção em Kernel**: Tentativas de acesso não autorizado à rota administrativa abortam imediatamente com HTTP 403 Forbidden.

---

## Validação e Qualidade (DoD)

O subsistema foi homologado com cobertura total de testes automatizados e validações estáticas:
* **Testes de Integração (`tests/Validation/AuditLogValidationTest.php`)**: Validam o comportamento isolado do middleware, a eficácia do algoritmo de bypass de assets estáticos, as regras do sanitizador LGPD, o motor de filtragem e contagem de dados do serviço e o bloqueio de rotas RBAC (403 Forbidden vs 200 OK).
* **Resultados PHPUnit**: **105 testes executados** com sucesso, resultando em **100% de aprovação (0 falhas e 0 erros)** sob ambiente de runtime PHP 8.4+.
* **Análise Estática (PHPStan)**: Código auditado com rigor estrito em todas as novas classes do módulo (`RequestAuditMiddleware`, `AuditLoggerService`, `ListAuditLogsAction`, `ViewAuditLogDetailAction`), retornando **zero erros**.

---

## Consequências

### Positivas (Prós)
* **Inteligência Ativa & Rastreabilidade**: Capacidade imediata de responder a incidentes e rastrear anomalias ou tentativas de invasão através da integridade dos metadados gravados.
* **Segurança Jurídica e Conformidade**: Histórico auditável e em total conformidade com os princípios de segurança da LGPD (minimização de dados e higienização de senhas/PII).
* **UX e Produtividade Operacional**: Painel limpo com paginação eficiente e busca instantânea, facilitando a inspeção de eventos sem sobrecarregar a equipe técnica com análise manual de logs brutos em disco.

### Negativas / Mitigações (Contras)
* **Crescimento de Armazenamento**: Como cada requisição dinâmica gera um registro de auditoria, a tabela `tbkk_audit_logs` tende a acumular volume ao longo do tempo.
  * *Mitigação*: Implementação de rotina periódica assíncrona (Cron/Worker CLI) para expurgo ou transferência de logs com mais de 90 dias para armazenamento a frio (*cold storage* compactado).

---

## Artefatos e Componentes Impactados
* **Middleware de Auditoria**: [`backend/core/Auth/Middleware/RequestAuditMiddleware.php`](file:///var/www/html/agsonhos/backend/core/Auth/Middleware/RequestAuditMiddleware.php)
* **Serviço de Auditoria**: [`backend/core/Services/Audit/AuditLoggerService.php`](file:///var/www/html/agsonhos/backend/core/Services/Audit/AuditLoggerService.php)
* **Sanitizador LGPD**: [`backend/core/Support/LgpdSanitizer.php`](file:///var/www/html/agsonhos/backend/core/Support/LgpdSanitizer.php)
* **Middleware RBAC**: [`backend/core/Auth/Middleware/AdminSessionMiddleware.php`](file:///var/www/html/agsonhos/backend/core/Auth/Middleware/AdminSessionMiddleware.php)
* **Controllers**: [`backend/core/Admin/Controllers/Actions/Audit/ListAuditLogsAction.php`](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/Audit/ListAuditLogsAction.php) e [`backend/core/Admin/Controllers/Actions/Audit/ViewAuditLogDetailAction.php`](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/Audit/ViewAuditLogDetailAction.php)
* **Rotas**: [`backend/Config/Routes.php`](file:///var/www/html/agsonhos/backend/Config/Routes.php)
* **Views Twig**: [`backend/resources/views/admin/pages/audit/index.html.twig`](file:///var/www/html/agsonhos/backend/resources/views/admin/pages/audit/index.html.twig), [`backend/resources/views/admin/pages/dashboard/index.html.twig`](file:///var/www/html/agsonhos/backend/resources/views/admin/pages/dashboard/index.html.twig) e [`backend/resources/views/admin/layouts/base.html.twig`](file:///var/www/html/agsonhos/backend/resources/views/admin/layouts/base.html.twig)
* **Testes Automatizados**: [`tests/Validation/AuditLogValidationTest.php`](file:///var/www/html/agsonhos/tests/Validation/AuditLogValidationTest.php)
