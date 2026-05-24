# Registro de Modificações IA (Sessão 20)

---

### Correção Estrutural de Entidades Core (Sync com DB) - Pedidos
**Data:** [Data Atual]
**O que foi feito:**
- Foram adicionados todas as propriedades em falta nas classes de domínio `Order`, `OrderProduct` e `OrderTotal` detectadas a partir da auditoria no arquivo `db_schema.php`. Adicionados diversos atributos vitais como configurações de pagamentos, dados de endereço, moedas, tracking e IDs faltantes (`masterId` em `OrderProduct`, `extension` em `OrderTotal`, dentre muitos outros relacionados ao controle da Order completa no OpenCart 4).
**Benefícios:**
- **Zero Error Hydration em Transações:** Essas atualizações previnem que dados fundamentais sejam omitidos durante os preenchimentos do DataAccessObject. Como a transação de compra ("Order") é o pilar de uma plataforma de e-commerce, qualquer dado não mapeado resultaria em valores perdidos no banco. Com essa estabilização, o script `DetectarZumbis.php` não indicará mais os longos registros de atributos órfãos nas classes de negócio relativas a Pedidos.