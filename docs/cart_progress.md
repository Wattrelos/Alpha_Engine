# Refatoração do Carrinho de Compras - Alpha Engine

Este documento registra o avanço na reestruturação arquitetural do módulo de carrinho de compras, consolidando a inteligência de negócios fora das bibliotecas legadas e reduzindo gargalos de banco de dados.

## O que foi implementado nesta rodada:

1. **A Ponte Legada (`cart.php`)**
   - Transformação da pesada biblioteca original do OpenCart em um *Proxy* leve que atua apenas para manter a retrocompatibilidade com extensões antigas, delegando todo o processamento para o `CartRepository`.

2. **Persistência Isolada (`CartMapper.php`)**
   - Todo o SQL do carrinho foi extraído e movido para métodos unitários no Mapper. Funcionalidades complexas, como limpeza de carrinhos abandonados e mesclagem de itens de visitantes após o login, agora ocorrem de forma explícita e controlada.

3. **Orquestração de Regras (`CartRepository.php`)**
   - Fim dos loops com queries SQL: a hidratação de produtos agora consome o `ProductMapper`.
   - Isolamento das regras de prioridade de descontos (progressivos e promoções limitadas).
   - Processamento iterativo de opções do carrinho (`checkbox`, `select`, etc.), aplicando seus devidos prefixos de valor, peso e pontuação de forma performática.
   - Integração das lógicas físicas e fiscais através da injeção do `WeightClassRepository` (para `getWeight`) e da biblioteca global de impostos (para `getTaxes` e `getTotal`).

4. **Controlador da API (`api/cart.php`)**
   - Completamente desacoplado do carrinho legado, passando a consumir as métricas consolidadas pelo `CartRepository`.
   - Reparo de contexto: A API agora mapeia o Idioma, Loja e Grupo de Clientes do usuário, forçando o `ProductMapper` a processar dados regionalizados e aplicar os preços corretos na hora de formatar o JSON de resposta do Checkout.