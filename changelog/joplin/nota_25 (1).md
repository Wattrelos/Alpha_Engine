---
### Alpha Engine: Repositórios e Mappers da Malha de Devolução (RMA)
---
**Data:** [Data Atual]
**O que foi feito:**
- Foram implementados os Mappers para abstração do DAO: `OrderReturnMapper`, `ReturnActionMapper`, `ReturnHistoryMapper`, `ReturnReasonMapper` e `ReturnStatusMapper`.
- Criação dos repositórios correspondentes com foco em buscar as devoluções por Cliente e por Pedido (`OrderReturnRepository`).
- Criação do `ReturnDictionaryRepository` estruturado como *Facade* (Fachada) para puxar facilmente listas de status, motivos e ações com base no idioma (`languageId`) do cliente ativo, eliminando as dezenas de Models isolados do OpenCart legado.
**Benefícios:** A gestão de logística reversa e SAC (Devoluções) agora estão totalmente independentes da arquitetura defasada e dos *queries* complexos. Os *Controllers* da interface não precisam mais mesclar bancos de dados de idiomas, bastando chamar os métodos concisos como `getReasonsByLanguage()`.
