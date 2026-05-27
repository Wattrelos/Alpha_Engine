---
### Alpha Engine: Limpeza de Entidades Zumbis (Sem Tabela no OpenCart 4)
---
**Data:** [Data Atual]
**O que foi feito:**
- Identificação e remoção de classes de Domínio (`Entities`) que não possuem tabelas correspondentes no banco de dados do OpenCart 4 (verificado via `db_schema.php`).
- Foram deletadas as entidades: `ReturnReasonDescription.php`, `SubscriptionTransaction.php`, `VoucherHistory.php`, `VoucherTheme.php` e `VoucherThemeDescription.php`.
- Identificado e sugerida a deleção manual de: `CustomerPayment.php`, `Voucher.php` e `ReturnActionDescription.php`.
**Benefícios:** Redução do débito técnico e prevenção de erros fatais no mapeamento ORM. Funcionalidades como "Vouchers" não fazem mais parte do core estrutural do OpenCart 4, e tabelas de descrição como `return_reason` e `return_action` tiveram suas colunas mescladas, deixando de usar o formato paralelo relacional de antes.# Registro de Modificações IA (Sessão 8)
