---
### 🚀 [CATALOG] Otimização e Normalização
---
1.  **Batch Loading em ProductMapper:**
-  Recuperação massiva de produtos e descrições em uma única operação SQL, eliminando o problema de *N+1 queries*.
2.  **Taxonomia Completa:**
-  Árvore lógica conectando Categorias, Produtos, Fabricantes, Opções e Atributos de forma tipada (PHP 8.4).
3.  **Pricing Logic Consolidation:**
-  Unificação de cálculos dinâmicos de descontos (Percentual vs Fixo) diretamente no Mapper.
4.  **Traffic Auditing:**
-  Migração do registro de visualizações para a entidade `ProductReport` via DAO.
5.  **Advertising Engine:**
-  Implementação de `Banner` e `BannerImage` com suporte multi-idioma e cache de identidade.
6.  **Stock Status:**
-  Exibição localizada de estados de inventário sincronizada com o fluxo de pedidos.
