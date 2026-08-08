---
adr: 5
title: Padronização de Colunas Temporais usando DATETIME em Substituição a TIMESTAMP na Alpha Engine
status: Approved
date: 2026-08-05
authors:
  - Antigravity AI
  - Josias
impacted_components:
  - file: docs/database/EERDiagram.puml
  - file: diagramsForStackholders/EERDiagram.puml
  - directory: core/Mappers/
  - directory: core/Model/Domain/Entities/
rules:
  temporal_data_type: "DATETIME"
  forbidden_data_type: "TIMESTAMP"
  timezone_standard: "UTC (Application level via PHP DateTimeImmutable)"
  mapping_php_type: "DateTimeInterface / DateTimeImmutable"
---

# ADR 005: Padronização de Colunas Temporais usando DATETIME em Substituição a TIMESTAMP na Alpha Engine

## Status
Aprovado (2026-08-05)

## Contexto
Durante o planejamento e evolução da modelagem de dados física do banco de dados (MariaDB/MySQL 8.0+) para a Alpha Engine, surgiu a necessidade de definir uma diretriz estrita para o armazenamento de campos de data e hora (como `created_at`, `updated_at`, `date_added`, `date_modified`, `expires_at`).

Historicamente, muitos sistemas legado utilizavam o tipo `TIMESTAMP`. No entanto, a análise de arquitetura de dados identificou 3 riscos técnicos e operacionais críticos no uso do `TIMESTAMP`:

1. **O Limite do Ano 2038 (Problema Y2038 - Unix Epoch 32-bit):**
   No MariaDB e MySQL, o tipo `TIMESTAMP` armazena dados utilizando um número inteiro assinado de 32 bits referente à Era Unix (Unix Epoch). Consequentemente, o `TIMESTAMP` deixará de funcionar e sofrerá estouro (*overflow*) em **19 de janeiro de 2038 às 03:14:07 UTC**. Como a Alpha Engine está sendo construída em 2026, projetar um sistema e-commerce novo com uma limitação que causará colapso de dados em cerca de 11 anos é um risco de arquitetura inaceitável. Por outro lado, o tipo `DATETIME` armazena valores no formato `YYYY-MM-DD HH:MM:SS` e suporta um intervalo de `'1000-01-01 00:00:00'` até `'9999-12-31 23:59:59'`, garantindo a perenidade do sistema por milênios.

2. **Comportamento Mágico Oculto no SGBD:**
   Por padrão, o MariaDB/MySQL aplica regras implícitas e triggers automáticos em colunas do tipo `TIMESTAMP` (como `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`), além de comportamentos variáveis conforme o parâmetro `explicit_defaults_for_timestamp`. Depender dessas mágicas ocultas polui o modelo conceitual/físico, dificulta a reprodução em testes unitários e quebra o princípio de um Diagrama EER limpo e agnóstico.

3. **Incompatibilidade de Fusos Horários com a Aplicação PHP:**
   O tipo `TIMESTAMP` converte automaticamente o valor enviado da timezone da conexão atual para UTC ao salvar, e converte de volta de UTC para a timezone da conexão ao consultar. Se a aplicação PHP e o servidor MariaDB não estiverem com suas configurações de timezone perfeitamente sincronizadas (ou se conexões compartilhadas alterarem a variável `time_zone`), os valores retornados para o PHP sofrerão deslocamentos imprevisíveis. Em contrapartida, o `DATETIME` armazena o valor textual estático exato como foi enviado. Isso garante que a aplicação PHP detenha 100% do controle sobre os fuso horários (utilizando `DateTimeImmutable` parametrizado em UTC).

## Decisão
Decidimos **padronizar o uso exclusivo do tipo `DATETIME`** para todas as colunas de data e hora na Alpha Engine, **proibindo o uso do tipo `TIMESTAMP`** no banco de dados.

### Diretrizes de Implementação:
1. **Estrutura de Banco de Dados:** Todas as tabelas que necessitarem de rastreamento temporal utilizarão colunas do tipo `DATETIME` (ex: `created_at DATETIME NOT NULL`, `updated_at DATETIME NULL`).
2. **Gerenciamento Transparente pela Aplicação:** A criação e atualização dos valores de data/hora serão geradas de forma explícita na camada de aplicação PHP (Data Mappers / Repositories) no formato ISO `Y-m-d H:i:s`, garantindo o armazenamento em UTC.
3. **Mapeamento em Diagramas (EER):** Todos os diagramas de banco de dados (PlantUML EER) devem declarar explicitamente o tipo `DATETIME`, mapeando no PHP 8.4 para a interface `DateTimeInterface` / `DateTimeImmutable`.

## Consequências

### Positivas (Prós)
* **Imunidade Total ao Problema do Ano 2038:** O sistema opera com tranquilidade sem risco de *overflow* de datas em 2038.
* **Previsibilidade e Determinismo:** Elimina desvios de timezone introduzidos pelo SGBD. A aplicação PHP possui controle absoluto sobre as datas em UTC.
* **Modelo Agnóstico e Limpo:** O schema do banco e os diagramas EER refletem tipos de dados universais, compatíveis com PostgreSQL, SQLite, MySQL e MariaDB sem comportamentos ocultos específicos do fornecedor.
* **Facilidade em Testes:** Permite mockar e testar estados temporais na aplicação PHP sem precisar manipular variáveis de ambiente do SGBD.

### Negativas / Mitigações (Contras)
* **Tamanho de Armazenamento:** No MariaDB/MySQL, um `DATETIME` ocupa 5 bytes (sem fração de segundo) em comparação com 4 bytes de um `TIMESTAMP` de 32 bits. Essa diferença de 1 byte por linha é irrelevante frente à integridade e longevidade dos dados.
* **Necessidade de Preenchimento Explícito:** A aplicação PHP precisa passar o valor da data na inserção/atualização (ex: `$now->format('Y-m-d H:i:s')`). Isso se alinha perfeitamente à arquitetura da Alpha Engine, onde os Data Mappers já gerenciam a hidratação e persistência de objetos de valor temporal.
