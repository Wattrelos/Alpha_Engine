---
### Alpha Engine: Auditoria de Segurança da API e Whitelist
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `ApiIp` e `ApiHistory` com mapeamento reverso `#[ManyToOne]` para a entidade primária `Api`.
- Criação dos repositórios correspondentes, incorporando métodos limpos de negócio: `isIpAllowed(int $apiId, string $ip)` para atuar como middleware de checagem da Whitelist, e `getRecentHistory(int $apiId)` para auditoria.
- Criação dos Mappers para abstrair as queries destas tabelas da camada do ORM.
**Benefícios:** A arquitetura de segurança da API da loja não depende mais de strings SQL vulneráveis dentro do core (`startup/api.php` ou `model/setting/api.php`). Os middlewares agora apenas invocam repositórios, resultando num fluxo de autenticação limpo, OOD (Object-Oriented Design) e preparado para checagem em cache O(1) de permissões.
