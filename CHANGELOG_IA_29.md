# Registro de Modificações IA (Sessão 29)

---

### Sincronização do Modelo de Domínio: Product
**Data:** [Data Atual]
**O que foi feito:**
- Sincronização da entidade raiz `Product` com todas as 21 colunas omitidas em relação ao banco de dados (ex: `upc`, `ean`, `jan`, `isbn`, `mpn`, identificadores dimensionais/peso como `weight`, `height`, controles de logística como `shipping` e metas de regras de negócio como `minimum` e `subtract`).
**Benefícios:**
- Garante integridade absoluta de transações. Operações do ORM e hidratações do catálogo como `ProductMapper` agora manipulam todos os dados fiscais e logísticos de um produto, essenciais para cálculo de frete e integrações de nota fiscal (ERP) que consomem informações como EAN e NCM sem perda de dados na injeção ou atualização.