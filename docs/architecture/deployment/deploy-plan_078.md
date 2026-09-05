# DP-78: Fallback de Auditoria em Banco de Dados (MySQL)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-13 21:59:43
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/78

## Descrição

# Plano de Implementação - Fallback de Auditoria em Banco de Dados (MySQL)

Este plano especifica a implementação de uma estratégia de **Fallback em Banco de Dados (MySQL)** para o sistema de auditoria da **Alpha Engine**, permitindo funcionamento nativo e completo em ambientes de hospedagem compartilhada como a **Hostinger (Plano Premium Web Hosting)**, onde servidores de mensageria (RabbitMQ) não estão disponíveis.

---

## 🎯 Objetivos

1. **Garantir Resiliência Total de Auditoria sem Dependência de RabbitMQ**:
   - Quando o RabbitMQ não estiver instalado ou falhar, os eventos de auditoria serão persistidos automaticamente na tabela `tbkk_audit_logs` do MySQL.
2. **Higienização LGPD Automática**:
   - Todos os payloads de auditoria gravados no banco de dados passarão pelo utilitário `LgpdSanitizer` antes da inserção.
3. **Desempenho & Compatibilidade**:
   - Garantir que a inserção direta via PDO (`ConnectionDB`) seja ultrarrápida e não impacte o tempo de resposta da aplicação.
   - Fornecer criação automática de tabela (*Auto-Healing/Auto-Provisioning*) no primeiro uso.

---

## ⚠️ User Review Required

> [!IMPORTANT]
> **Estratégia de Fallback em Camadas:**
> 1. **Nível 1 (Mensageria Asíncrona):** RabbitMQ (ativo se configurado no `.env` com servidor funcional).
> 2. **Nível 2 (Banco de Dados MySQL - Hostinger):** Inserção na tabela `tbkk_audit_logs` via `ConnectionDB` sanitizada por `LgpdSanitizer`.
> 3. **Nível 3 (Arquivo de Log Local):** Arquivo `storage/logs/audit.log` caso o MySQL esteja temporariamente indisponível.

---

## 🛠️ Modificações Propostas

### 1. Camada de Persistência & Serviços de Auditoria

#### [NEW] [`AuditLoggerService.php`](file:///var/www/html/agsonhos/backend/core/Services/Audit/AuditLoggerService.php)
- Serviço central de auditoria que encapsula o fluxo de gravação:
  - Tenta publicar no RabbitMQ via `QueueService`.
  - Se RabbitMQ falhar ou estiver desativado, faz INSERT na tabela `tbkk_audit_logs`.
  - Se a tabela `tbkk_audit_logs` não existir, cria-a dinamicamente (`CREATE TABLE IF NOT EXISTS`).
  - Aplica `LgpdSanitizer::sanitizeArray()` em todos os dados e payloads JSON.

#### [MODIFY] [`QueueService.php`](file:///var/www/html/agsonhos/backend/core/Events/QueueService.php)
- Adicionar verificação de conectividade e integração graciosa com o mecanismo de fallback em banco de dados caso a conexão com RabbitMQ falhe ou esteja desativada no `.env`.

---

### 2. Documentação de Arquitetura

#### [MODIFY] [`security-and-audit-architecture.md`](file:///var/www/html/agsonhos/backend/docs/architecture/security-and-audit-architecture.md)
- Atualizar a seção 3 (Camada de Auditoria) para documentar a estratégia de **Fallback Multinível** (RabbitMQ -> MySQL `tbkk_audit_logs` -> `storage/logs/audit.log`) adaptada para a Hostinger e ambientes sem broker de mensagens.

---

## 🧪 Plano de Verificação

### Testes Automatizados (PHPUnit)

#### [NEW] [`AuditDatabaseFallbackTest.php`](file:///var/www/html/agsonhos/backend/tests/Validation/AuditDatabaseFallbackTest.php)
- Teste unitário para validar:
  1. Inserção com sucesso de um evento de auditoria na tabela `tbkk_audit_logs` do MySQL quando o RabbitMQ está ausente.
  2. Higienização LGPD no banco de dados (redação de senhas, mascaramento de CPF/E-mail em JSON payload).
  3. Recuperação de eventos de auditoria por `store_id` e `event`.

Executar a suíte via terminal:
```bash
vendor/bin/phpunit tests/Validation/AuditDatabaseFallbackTest.php
vendor/bin/phpunit
```

# Tarefas de Execução — Fallback de Auditoria em Banco de Dados (Hostinger / Shared Hosting)

- [x] Criar o serviço `AuditLoggerService.php` com suporte a fallback MySQL (`tbkk_audit_logs`), sanitização LGPD e provisionamento automático de tabela
- [x] Atualizar `QueueService.php` para integrar o fallback gracioso em banco de dados quando RabbitMQ estiver ausente
- [x] Criar teste de validação automatizado `AuditDatabaseFallbackTest.php` e executar suíte PHPUnit
- [x] Atualizar a documentação `security-and-audit-architecture.md` refletindo o fallback multinível
- [x] Gerar walkthrough de conclusão

# Walkthrough — Fallback de Auditoria em Banco de Dados (Hostinger / MySQL)

Concluímos com sucesso a implementação da estratégia de **Fallback Multinível de Auditoria** para a **Alpha Engine**, permitindo funcionamento nativo em hospedagens compartilhadas como a **Hostinger (Plano Premium Web Hosting)** sem necessidade do RabbitMQ.

---

## 🎯 Componentes Desenvolvidos e Atualizados

### 1. Novo Serviço de Auditoria [`AuditLoggerService.php`](file:///var/www/html/agsonhos/backend/core/Services/Audit/AuditLoggerService.php)
- Implementa o fluxo de resiliência multinível:
  - **Nível 1:** Publicação assíncrona no RabbitMQ (`RABBITMQ_ENABLED=true`).
  - **Nível 2 (Hostinger Fallback):** Gravação direta na tabela `tbkk_audit_logs` no MySQL via PDO (`ConnectionDB`).
  - **Nível 3 (Emergencial):** Gravação em arquivo local `storage/logs/audit.log`.
- **Auto-Healing de BD:** Cria automaticamente a tabela `tbkk_audit_logs` no primeiro uso caso ela ainda não exista.
- **Higienização LGPD Integrada:** Passa obrigatoriamente todos os payloads pelo [`LgpdSanitizer`](file:///var/www/html/agsonhos/backend/core/Support/LgpdSanitizer.php) (redigindo senhas, tokens, mascarando CPFs, CNPJs e e-mails).

### 2. Integração no [`QueueService.php`](file:///var/www/html/agsonhos/backend/core/Events/QueueService.php)
- Atualizado para tratar graciosamente exceções de falha de conexão com o RabbitMQ, permitindo o chaveamento transparente para a camada de fallback em banco de dados.

### 3. Teste de Validação Automatizada [`AuditDatabaseFallbackTest.php`](file:///var/www/html/agsonhos/backend/tests/Validation/AuditDatabaseFallbackTest.php)
- Teste dedicado para simular a ausência de RabbitMQ e validar:
  1. O salvamento bem-sucedido do evento na tabela `tbkk_audit_logs` do MySQL.
  2. O mascaramento e redação LGPD dos dados gravados no banco de dados.

### 4. Documentação de Arquitetura [`security-and-audit-architecture.md`](file:///var/www/html/agsonhos/backend/docs/architecture/security-and-audit-architecture.md)
- Atualizada a Seção 3 para documentar a estratégia multinível adaptada para ambientes de hospedagem compartilhada.

---

## 🧪 Resultados dos Testes Automatizados (PHPUnit)

Executamos a suíte de testes com a adição do novo teste de fallback em MySQL:

```bash
vendor/bin/phpunit
```

**Resultado:**
```text
PHPUnit 13.3.0 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.22
Configuration: /var/www/html/agsonhos/backend/phpunit.xml

.............................................................. 62 / 62 (100%)

Time: 00:01.486, Memory: 34.50 MB
OK (62 tests, 177 assertions)
```

Todos os **62 testes e 177 asserções** foram executados com **100% de aprovação** e zero erros!

