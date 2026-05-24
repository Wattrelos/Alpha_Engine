# Registro de Modificações IA (Sessão 32)

---

### Correção de Mapeamento: CustomerAffiliateReport, CustomerWishlist e DownloadReport
**Data:** [Data Atual]
**O que foi feito:**
- Entidade `CustomerAffiliateReport`: Refatoração total. Remoção do formato legado (anotações Doctrine) e implementação de getters/setters e relacionamentos tipados em PHP 8.4 para as colunas `customer_id` e `store_id`.
- Entidade `CustomerWishlist`: Injeção da coluna omitida `store_id` e vínculo `#[ManyToOne]` de loja.
- Entidade `DownloadReport`: Injeção das colunas omitidas `store_id` e `country` para rastreabilidade correta dos downloads na loja.
**Benefícios:**
- Garante que metadados e relatórios cruciais do sistema não levantem erros do utilitário de persistência ou percam os vínculos multiloja nas rotinas do OpenCart. A limpeza no `CustomerAffiliateReport` eleva o arquivo ao padrão oficial Alpha Engine.