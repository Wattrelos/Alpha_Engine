---
### Alpha Engine: Correção de Schema e Refatoração de Dicionário de RMA
---
**Data:** [Data Atual]
**O que foi feito:**
- Correção estrutural na modelagem das entidades `ReturnAction` e `ReturnReason`. Foi detectado que o OpenCart não utiliza o padrão `_description` nestas tabelas (`db_schema.php`), mantendo os atributos `language_id` e `name` enraizados na tabela principal.
- Refatoração do `ReturnDictionaryRepository` para consumir os Mappers primários (Flat Tables) em vez de relacionamentos *OneToMany*.
**Benefícios:** Prevenção de exceções severas no momento em que o DAO fosse montar as queries dinâmicas, garantindo precisão total na extração das traduções de devolução de forma limpa.
