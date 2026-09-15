# DP-30: Adaptar Diagrama de Sequência do PDV (POS) para a Arquitetura do Projeto

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-28 00:07:19
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/30

## Descrição

# Adaptar Diagrama de Sequência do PDV (POS) para a Arquitetura do Projeto

Este plano descreve as adaptações necessárias no diagrama de sequência de vendas e finalização do PDV (`fluxo_venda_pos.puml`) para refletir a arquitetura da Alpha Engine (Slim 4, Actions invocáveis, Repositórios, Mappers, DAO, Unit of Work e controle de concorrência/idempotência via Redis).

## User Review Required

> [!IMPORTANT]
> O diagrama original utilizava uma nomenclatura genérica de controlador (`OrderController.php`) e interações diretas com o banco de dados. A nova versão alinhará o fluxo aos padrões do projeto:
> - Substituição do controlador monolítico por **Single Action Controllers** (invocáveis).
> - Inclusão do **AdminSessionMiddleware** para garantir que o fluxo de funcionários seja autenticado.
> - Exposição dos componentes de persistência/domínio: **ProductRepository**, **OrderRepository**, **UnitOfWork**, **OrderMapper** / **ProductMapper** e **DAO**.
> - Integração do **Redis** para verificação de idempotência no fechamento do pré-pedido (evitando cliques duplos).

## Proposed Changes

### Documentação e Arquitetura

#### [MODIFY] [fluxo_venda_pos.puml](/docs/architecture/fluxo_venda_pos.puml)
- Atualizar a definição dos participantes (`actor`, `boundary`, `control`, `database`) para representar as camadas reais.
- Mapear a rota passando por `AdminSessionMiddleware`.
- Mapear a resolução de dependências e a chamada de Actions correspondentes:
  - `SearchProductAction`
  - `CreatePreOrderAction`
  - `GetPreOrderAction`
  - `PayOrderAction`
- Introduzir a camada de persistência com `ProductRepository`, `OrderRepository`, `UnitOfWork`, `Mapper` e `DAO`.
- Atualizar o fluxo de chamadas em ambas as fases (Phase 1 e Phase 2).

## Verification Plan

### Manual Verification
- Visualizar o diagrama gerado e validar se a semântica está correta e de acordo com o padrão PlantUML.

# Tarefas - Adaptar Diagrama do PDV (POS)

- `[x]` Analisar e desenhar as interações detalhadas entre os componentes do fluxo
- `[x]` Modificar o arquivo `/var/www/html/agsonhos/docs/architecture/fluxo_venda_pos.puml` com a nova estrutura arquitetural
- `[x]` Validar a sintaxe do diagrama PlantUML

# Walkthrough - Adaptar Diagrama do PDV (POS)

Foi adaptado o diagrama de sequência do PDV (Point of Sale / Ponto de Venda) para alinhar-se perfeitamente com a arquitetura definida na Alpha Engine do projeto.

## Alterações Realizadas

### Documentação de Arquitetura
#### [fluxo_venda_pos.puml](/docs/architecture/fluxo_venda_pos.puml)
- **Atores e Fronteiras:** Preservados os atores (`Customer`, `Sales Rep`, `Cashier`) e telas (`SalesScreen`, `CashierScreen`).
- **Rotas e Middlewares:** Introduzido o `Router` do Slim 4 e o middleware `AdminSessionMiddleware` para proteção e autenticação da sessão (verificando os papéis no cache Redis).
- **Single Action Controllers:** Removido o controlador monolítico genérico (`OrderController.php`) e introduzidos os Single Action Controllers (Actions invocações de classe única) apropriados:
  - `SearchProductAction`
  - `CreatePreOrderAction`
  - `GetPreOrderAction`
  - `PayOrderAction`
- **Padrão de Repositório e Mapeamento:** Adicionados `ProductRepository` e `OrderRepository` como intermediários de domínio, em conjunto com `ProductMapper` e `OrderMapper` para conversão (hidratação/desidratação) dos dados antes do envio para o `DataAccessObject (DAO)` e o banco MySQL.
- **Transações Atômicas:** Introduzido o uso de `UnitOfWork (UoW)` para gerenciar e consolidar transações de escrita de forma atômica no banco de dados.
- **Idempotência:** Adicionada a validação de idempotência usando chaves no cache `Redis` (padrão `SETNX`) no fechamento do pré-pedido, evitando a criação de pedidos duplicados em cliques rápidos no frontend.

## Validação de Sintaxe
A sintaxe do arquivo PlantUML foi inspecionada manualmente para assegurar conformidade total com os padrões e regras do motor PlantUML (incluindo as diretivas modernas de estilo CSS).

