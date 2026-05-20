# Alpha Engine - Vendas, Checkout e Clientes
**Módulo de Transações e Relacionamento**

Este log documenta a migração do motor financeiro e da gestão de dados de clientes para o padrão Alpha.

## 💰 [SALES] Ciclo de Venda e Checkout

1.  **Atomic Order Persistence**: Persistência da árvore completa do pedido (Produtos, Totais, Vouchers e Histórico) em um único ciclo atômico via UnitOfWork.
2.  **Cart Persistence Logic**: Gerenciamento de carrinho com hash JSON para evitar duplicidade e transição transparente de Sessão para Cliente no login.
3.  **Subscription Engine**: Normalização de ciclos de faturamento e períodos de teste com "Snapshot Pattern" para congelar termos contratuais.
4.  **Voucher System**: Motor de cartões presente com tipagem rigorosa para códigos de resgate e valores monetários.
5.  **Order Observer Pattern**: Desacoplamento de efeitos colaterais (envio de e-mails) da lógica de persistência.

## 👤 [CUSTOMER] Gestão de Clientes e Afiliados

1.  **Customer Domain Authority**: Autenticação via `password_verify` e entidade Customer, eliminando dependência de modelos legados.
2.  **Address Formatting**: Resolução de layouts postais regionais integrada à camada de domínio.
3.  **Affiliate Network**: Consolidação de rastreamento de parceiros, comissões e dados bancários de forma tipada.
4.  **Activity Tracking**: Vinculação de ações e buscas do cliente ao ecossistema Alpha para auditoria de comportamento.

## 🔄 [RETURNS] Motor de Devoluções

1.  **Return Lifecycle**: Implementação de `OrderReturn`, `ReturnAction` e `ReturnReason` com suporte multi-idioma.
2.  **Auditoria de Devolução**: Snapshot de dados de contato na devolução para garantir histórico imutável.
3.  **Return History**: Trilha de auditoria robusta para eventos de suporte e triagem.

## 📣 [MARKETING] Campanhas e Cupons

1.  **Coupon Engine**: Gestão rigorosa de limites de uso, validade temporal e restrições por categoria/produto via DAO.
2.  **Traffic Auditing**: Relatórios de cliques e conversão por campanha com mapeamento geográfico (IP/País).

## 🛠️ Vitórias Técnicas de Vendas
1.  **Atomic Order Persistence**: A criação de pedidos agora é transacional via `UnitOfWork`. Se o estoque falhar, o histórico não é criado.
2.  **Customer Domain Authority**: Autenticação migrada para `password_verify` nativo e objetos de domínio, removendo lógica sensível dos models legados.
3.  **Address & Geo-Localisation**: `AddressMapper` resolve layouts postais dinamicamente via `AddressFormat` de cada país.
4.  **Subscription Snapshot**: Congelamento de preços e termos no momento da contratação da assinatura, protegendo a integridade contratual.
5.  **Cart Resilience**: Lógica de adição de produtos com opções complexas unificada via hash JSON, evitando duplicidade de itens.
6.  **Voucher Engine**: Normalização monetária rigorosa para créditos de presente e resgate seguro.

### 💡 Insights de Checkout
*   A transição transparente de `session_id` para `customer_id` no login garante que o visitante não perca o carrinho.
*   O método `processAssociations` permite navegar de um cliente até seus pedidos e moedas sem um único JOIN manual no controlador.
*   Filtros obrigatórios por ID do cliente em buscas de endereço único eliminam riscos de manipulação de URL (IDOR).

---
*Foco total na transacionalidade e integridade financeira do ecossistema.*