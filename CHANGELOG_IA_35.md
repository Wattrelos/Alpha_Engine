# Registro de Modificações IA (Sessão 35)

---

### Correção de Mapeamento: Option, OrderStatus e StockStatus
**Data:** [Data Atual]
**O que foi feito:**
- Entidade `Option`: Injetada a propriedade `validation` faltante na tabela de opções do catálogo.
- Entidades `OrderStatus` e `StockStatus`: Injetados os identificadores primários de dicionário traduzido `orderStatusId` e `stockStatusId` que estavam sendo sinalizados como ausentes pela auditoria.
**Benefícios:**
- Garante que opções sejam salvas com as corretas regras de validação aplicadas a elas no frontend. Normaliza as tabelas de status para que o DataMapper consiga resolver as chaves nas listagens (SELECT) e atualizações, prevenindo dados vazios nos seletores da administração e carrinho.