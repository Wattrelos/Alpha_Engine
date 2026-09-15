# DP-92: Atualização do Diagrama de Casos de Uso do Cliente

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-25 00:18:19
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/92

## Descrição

# Relatório de Implementações e Documentação

## 1. Atualização do Diagrama de Casos de Uso do Cliente ([`UseCaseDiagramCustomer.puml`](/docs/business/use-cases/UseCaseDiagramCustomer.puml))

O diagrama de casos de uso foi totalmente atualizado e estruturado em pacotes temáticos modernos, refletindo todas as funcionalidades implementadas no sistema:

### Pacotes e Casos de Uso Mapeados:
1. **Catálogo, Busca & Mídia**:
   - Navegar no Catálogo & Categorias (`UC_Nav`)
   - Buscar Produtos com Filtros (`UC_Search`)
   - Visualizar Detalhes do Produto (`UC_ProductDetail`)
   - Selecionar Variantes & Opções (`UC_Variants` - extend)
   - Carregar Mídia & Cache On-Demand (`UC_MediaCache` - include do detalhe e sistema)
2. **Carrinho & Compras**:
   - Adicionar ao Carrinho (`UC_Cart`)
   - Calcular Frete por CEP (`UC_Shipping` - extend)
   - Aplicar Cupom de Desconto (`UC_Coupon` - extend)
   - Realizar Checkout (`UC_Checkout` com include de `UC_Payment`)
   - Comprar como Visitante (`UC_GuestCheckout` - extend)
   - Processar Pagamento (`UC_Payment` integrado ao ator Gateway/Sistema)
3. **Autenticação & Sessão**:
   - Cadastrar Nova Conta (`UC_Register`)
   - Autenticar-se Login / Logout (`UC_Login` com include de sincronização de sessão Redis e mesclagem de carrinho `UC_SyncSession`)
   - Recuperar Senha por E-mail (`UC_ForgotPass`)
4. **Área "Minha Conta" (Portal do Cliente)**:
   - Acessar Painel do Cliente (`UC_AccountDash`)
   - Gerenciar Dados Cadastrais (`/account/edit`)
   - Alterar Senha Logado (`/account/resetar-senha`)
   - Gerenciar Livro de Endereços (`/account/addresses`)
   - Gerenciar Lista de Desejos (`/account/wishlist`)
   - Consultar Pedidos & Histórico (`/account/orders`)
   - Consultar Extrato & Transações Financeiras (`/account/transaction`)
   - Solicitar Devolução / RMA (`/account/return`)
   - Gerenciar Inscrição na Newsletter (`/account/newsletter`)
5. **Cotações & Projetos (RFQ / BoQ)**:
   - Criar Solicitação de Orçamento (`/projetos/novo`)
   - Acompanhar Meus Projetos (`/account/projetos`)
   - Comparar Propostas Recebidas (`UC_CompareBids` - extend)
   - Aceitar Proposta de Prestador (`UC_AcceptBid` - extend)
   - Aprovar BoQ & Enviar Materiais ao Carrinho (`UC_ApproveBoq` com include em `UC_Cart`)

---

## 2. Compilação e Renderização dos Diagramas
- Diagrama compilado e validado com PlantUML em formatos vetoriais e rasterizados:
  - [`Diagrama_Casos_De_Uso_Cliente.svg`](/docs/business/use-cases/Diagrama_Casos_De_Uso_Cliente.svg)
  - [`Diagrama_Casos_De_Uso_Cliente.png`](/docs/business/use-cases/Diagrama_Casos_De_Uso_Cliente.png)

