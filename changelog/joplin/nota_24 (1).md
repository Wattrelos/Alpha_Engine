---
### Alpha Engine: Auditoria do Schema e Implementação de Entidades Ausentes (Pedidos e CMS)
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `OrderOption`, `OrderStatus`, `OrderSubscription` e `OrderReturn` (sendo mapeada para `return`, evitando conflito de nome reservado) para completar as hierarquias de Pedidos e Pós-vendas.
- Criação das entidades `Module`, `Event` e `Startup` para completar a modelagem de configuração do sistema (Hooks, injeção de Middlewares e armazenamento JSON de módulos de extensões).
- Mapeamento de instâncias `#[ManyToOne]` nas sub-entidades de Order (`OrderOption` e `OrderSubscription`) e OrderReturn garantindo hidratação autônoma pelo DAO.
- Tipagem de dados e conversão flutuante para preços nas assinaturas, compatibilidade estrita do PHP 8.4.
**Benefícios:** Esta iteração fecha os "Buracos Negros" do banco de dados na nova arquitetura. O motor logístico da Alpha Engine agora enxerga a totalidade do ciclo de um pedido — desde as opções e assinaturas escolhidas até uma eventual devolução (RMA). Na infraestrutura, a disponibilidade de `Module` e `Event` viabiliza a refatoração completa do motor de extensão, abandonando arrays brutas a favor de objetos manipuláveis via Repositório.
