---
adr: 4
title: Adoção do Stack Tecnológico Core (PHP 8, Slim 4, Twig 3 e MySQL/MariaDB) na Alpha Engine
status: Approved
date: 2026-07-12
authors:
  - Antigravity AI
  - Josias
impacted_components:
  - file: composer.json
  - directory: core/
  - directory: Containers/
  - directory: Config/
rules:
  minimum_php_version: "8.0"
  routing_framework: "Slim 4"
  templating_engine: "Twig 3"
  database_system: "MySQL / MariaDB via PDO"
  compliance_standards:
    - PSR-7 (HTTP Message Interfaces)
    - PSR-11 (Container Interface)
    - PSR-15 (HTTP Server Middleware Drivers)
---

# ADR 004: Adoção do Stack Tecnológico Core (PHP 8, Slim 4, Twig 3 e MySQL/MariaDB) na Alpha Engine

## Status
Aprovado (2026-07-12)

## Contexto
Durante o planejamento inicial da Alpha Engine, surgiu a necessidade de definir o stack de tecnologias estruturais do sistema. O e-commerce anterior sofria de problemas graves de performance, falta de tipagem estrita, acoplamento excessivo e carregamento lento de páginas devido a arquiteturas monolíticas pesadas e desatualizadas. 

Para a nova versão da engine (Alpha), buscávamos uma arquitetura que oferecesse:
1. **Ultra-performance e Baixo Footprint:** Carregamento de páginas extremamente rápido e baixo consumo de memória no servidor.
2. **Desenvolvimento Orientado a Padrões Modernos:** Conformidade com as recomendações de padrões do PHP (PSRs), permitindo interoperabilidade.
3. **Liberdade Arquitetural:** Evitar o acoplamento excessivo de frameworks completos (como Laravel ou Magento), onde a maior parte dos recursos não é utilizada em todas as páginas, mas ainda assim consome tempo de boot.
4. **Controle Total e Simplicidade:** Flexibilidade para implementar padrões de design específicos (como Unit of Work, Data Access Object, Injeção de Dependência nativa e Controllers baseados em Ações de responsabilidade única).

## Decisão
Decidimos adotar e padronizar o seguinte stack tecnológico core para o desenvolvimento da Alpha Engine a partir do zero:

1. **PHP 8 (8.0+ com suporte a tipagens estritas, Constructor Promotion e Attributes):**
   * **Razão:** A modernização do ecossistema PHP 8 trouxe enormes ganhos de performance do motor JIT, tipagem estrita para segurança em tempo de desenvolvimento, e sintaxe limpa (ex: Union Types, Named Arguments, Match Expressions).

2. **Slim Framework 4 (Micro-framework HTTP):**
   * **Razão:** O Slim 4 gerencia exclusivamente o ciclo de vida HTTP (Request, Response, Routing e Middleware pipeline). Ele atua como um coordenador minimalista baseado em PSR-7, PSR-11 e PSR-15, sem forçar estruturas de banco de dados, autenticação ou visualização. Isso garante um tempo de boot na casa dos microssegundos.

3. **Twig 3 (Template Engine):**
   * **Razão:** Separação limpa entre a lógica de apresentação e a lógica de negócios. O Twig oferece sintaxe elegante, sandbox seguro para templates, cache eficiente de compilação em disco e extensibilidade simples (permitindo injeção de helpers de tradução, rotas e asset pipelines).

4. **MySQL / MariaDB via PDO Nativo:**
   * **Razão:** Uso do driver `PDO` nativo do PHP com preparação de queries desabilitada para emulação (`PDO::ATTR_EMULATE_PREPARES => false`) para manter tipos nativos (ex: int, float) e reduzir drasticamente o uso de memória. O banco MariaDB/MySQL oferece performance consolidada para e-commerce relacional e transações ACID robustas via InnoDB.

5. **Container de Injeção de Dependências PSR-11 Customizado (`Containers\AppContainer`):**
   * **Razão:** Em vez de usar containers pesados externos, implementamos um micro-container que resolve dinamicamente as dependências das Actions (Single Action Controllers) via Reflection (autowiring de repositórios e serviços), mantendo a compatibilidade estrita com a interface PSR-11.

## Consequências

### Positivas (Prós)
* **Tempo de Resposta Excepcional (Time to First Byte - TTFB):** Sem o overhead de boot de um framework full-stack clássico, o tempo de inicialização da Alpha Engine permanece extremamente baixo.
* **Flexibilidade Arquitetural:** O projeto possui total liberdade para estruturar as pastas `core/`, `Containers/` e `Config/` da maneira mais adequada para a nossa lógica de negócio, focada em Domain Driven Design (DDD) leve.
* **Curva de Aprendizado e Manutenibilidade:** O código é transparente; o desenvolvedor consegue depurar toda a stack do ciclo de vida HTTP até a persistência no banco de dados sem precisar navegar por dezenas de camadas internas invisíveis do framework.
* **Segurança e Proteção contra FOUC/Injeção:** O Twig 3 por padrão escapa HTML (evitando XSS) e o uso nativo de Prepared Statements via PDO elimina riscos de SQL Injection.

### Negativas (Contras)
* **Desenvolvimento Customizado de Infraestrutura:** A escolha por micro-componentes nos obriga a escrever certas lógicas que frameworks full-stack fornecem de fábrica (como migrations de banco, gerenciadores de filas, validadores e o próprio container de DI básico).
* **Ausência de Ecossistema de Plugins Prontos:** Não há como instalar plugins genéricos de frameworks populares (ex: pacotes Laravel/Symfony integrados sem adaptação). Qualquer biblioteca externa precisa ser acoplada manualmente via adaptadores/providers compatíveis ou PSR.
