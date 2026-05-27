---
### Alpha Engine: Strict Type Casting no DTO de Country
---
**Data:** [Data Atual]
**O que foi feito:**
- Verificação da entidade `Country` e aplicação de *type casting* explícito `(int)` para os booleanos `postcode_required` e `status` no método `toLegacyDTO` do `CountryRepository`.
**Benefícios:** Garante que o array exportado para as *Views* legadas tenha o formato exato `0` ou `1`, prevenindo falhas silenciosas no Twig caso ele tente comparar um booleano nativo com uma string estrita do banco de dados (ex: `'1'`).
