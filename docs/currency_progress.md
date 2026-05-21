# Refatoração da Biblioteca de Moedas (Currency) - Alpha Engine

Este documento registra o progresso na refatoração da biblioteca de Moedas (`currency.php`), dando continuidade à padronização da Alpha Engine (semelhante ao feito em `weight.php`).

## O que foi feito:
1. **Criação de `CurrencyMapper.php`:**
   - Criado no namespace `Alpha\Mappers`.
   - Implementado o método `findAllActive()` para buscar *apenas as moedas com status ativo*, adicionando uma camada de segurança/regra de negócio que faltava na query nativa original.
2. **Atualização de `currency.php`:**
   - Removida a query SQL acoplada (`SELECT * FROM currency`) diretamente no construtor.
   - Integrado o uso do `mapperFactory` para instanciar `CurrencyMapper`. A iteração agora ocorre sobre um array filtrado e confiável gerado pelo Mapper.

*Atualização realizada seguindo as diretrizes do `GEMINI.md`.*