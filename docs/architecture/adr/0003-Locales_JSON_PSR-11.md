---
adr: 3
title: Adoção de Arquitetura Unificada de Locales baseada em JSON Nativo e PSR-11
status: Accepted
date: 2026-07-11
authors:
  - Antigravity AI
  - Josias
impacted_components:
  - namespace: Alpha\Support\Language
    path: /var/www/html/agsonhos/core/Support/Language.php
  - namespace: Containers\AppContainer
    path: /var/www/html/agsonhos/Containers/AppContainer.php
  - namespace: Alpha\Auth\Middleware\LanguageMiddleware
    path: /var/www/html/agsonhos/core/Auth/Middleware/LanguageMiddleware.php
rules:
  format:
    allowed: ["JSON"]
    disallowed: ["PHP Array", "INI"]
  paths:
    locales_directory: "/Locales"
    structure: "/Locales/{locale}/{locale}.{namespace}.json"
  psr11_bindings:
    container_keys:
      - "language"
      - "Alpha\Support\Language"
    class_resolving: "Alpha\Support\Language"
validation:
  must_use_di: true
  forbidden_calls:
    - "include"
    - "require"
    - "parse_ini_file"
---

# ADR 003: Adoção de Arquitetura Unificada de Locales baseada em JSON Nativo e PSR-11

## Status
Aceito

## Contexto
O sistema atual gerencia arquivos de tradução e localização (locales) de forma fragmentada, utilizando múltiplos formatos (como arrays PHP e arquivos INI) espalhados por diferentes módulos. Isso gera os seguintes problemas:

* Falta de padronização: Dificulta a manutenção e a automação de ferramentas de tradução externas.
* Acoplamento: A recuperação de chaves de tradução não segue um padrão de injeção de dependência.
* Performance: Carregar múltiplos formatos consome mais recursos computacionais desnecessariamente.

Precisamos de uma abordagem unificada que centralize essas configurações e que seja interoperável com o ecossistema padrão do projeto.

## Decisão
Adotaremos uma nova arquitetura unificada para o gerenciamento de internacionalização com as seguintes diretrizes:

   1. Centralização: Todos os arquivos de tradução serão movidos para um diretório raiz unificado chamado `/Locales`. Os arquivos seguem a convenção `/Locales/{locale}/{locale}.{namespace}.json` (ex: `/Locales/pt-br/pt-br.account.account.json`).
   2. Formato Nativo: O único formato permitido para os dados de localização será o JSON.
   3. Abstração e PSR-11: O acesso, carregamento e resolução desses arquivos JSON são encapsulados pelo serviço `Alpha\Support\Language` (que usa por debaixo dos panos o Symfony Translator). Esse serviço é registrado no container de injeção de dependência (`Containers\AppContainer`), que implementa a interface PSR-11 (`Psr\Container\ContainerInterface`), sob a chave `'language'` e o FQCN `Alpha\Support\Language::class`. O middleware de idioma (`Alpha\Auth\Middleware\LanguageMiddleware`) detecta o locale da rota e define o idioma ativo no serviço de tradução usando o método `setCode()`.

## Consequências

### Positivas (Prós)
* Padronização: Formato JSON único facilita a integração com plataformas modernas de tradução (SaaS).
* Interoperabilidade: O uso do container PSR-11 garante a injeção limpa do serviço de tradução nos controladores e middlewares.
* Organização: A pasta `/Locales` centraliza o escopo de tradução, limpando a estrutura dos módulos legados.
* Performance: Arquivos JSON nativos possuem解析 (parsing) rápido e o serviço cacheia namespaces carregados.

### Negativas (Contras)
* Curva de migração: Será necessário converter todos os arquivos antigos (arrays PHP/INI) para o novo formato JSON.
* Refatoração de código: O código que chamava as traduções diretamente precisará ser adaptado para buscar os dados através do Container PSR-11.
* Tipagem estrita: Arquivos JSON não aceitam lógica dinâmica (como funções ou concatenações diretas no arquivo), exigindo que qualquer manipulação de string ocorra na camada de código.
