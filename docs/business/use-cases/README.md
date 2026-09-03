# 🧩 Casos de Uso do Sistema (Alpha Engine)

> **Documentação de Engenharia de Software - Laboratório de Engenharia de Software**  
> **Projeto:** Alpha Engine (Plataforma E-commerce & Ponto de Venda On-Premise para Materiais de Construção)

---

## 📚 1. Guia Didático: Entendendo Casos de Uso (Para Estudantes)

Se você é um estudante e está aprendendo Engenharia de Software, aqui está uma explicação direta e clara sobre o que é esta pasta e por que ela é estruturada desta forma:

### O que é um Caso de Uso (*Use Case*)?
Um **Caso de Uso** descreve uma sequência de interações entre um **Ator** (alguém ou algo que interage com o sistema, como um *Cliente*, *Vendedor*, *Caixa* ou *Gateway de Pagamento*) e o **Sistema de Software** para atingir um objetivo de negócio específico (por exemplo: *Adicionar ao Carrinho*, *Calcular Frete*, *Emitir Ticket de Pré-Venda*).

### Qual a diferença entre o Diagrama e a Especificação?
1. **Diagrama de Casos de Uso (PlantUML / Visual):** É a foto panorâmica. Mostra os bonecos (*atores*), as elipses (*casos de uso*) e os relacionamentos (`<<include>>`, `<<extend>>`, `herança`).
2. **Especificação de Caso de Uso (Documento `.md` / Textual):** É o manual detalhado passo a passo de cada elipse. Ele descreve o **Caminho Feliz** (*o que acontece quando tudo dá certo*), os **Fluxos Alternativos** (*caminhos secundários válidos*) e os **Fluxos de Exceção** (*como o sistema reage a erros, falta de estoque ou dados inválidos*).

### O que é Rastreabilidade?
A **Rastreabilidade** é a ponte que conecta cada funcionalidade aos seus requisitos e regras:
- **RF (Requisito Funcional):** O que o sistema deve fazer (ex: `RF001` - Cadastro de produtos com fotos HD).
- **RN (Regra de Negócio):** As leis e políticas da empresa (ex: `RN001` - Pisos só podem ser vendidos em caixas fechadas calculadas por m²; `RN011` - Arrependimento de 7 dias pelo CDC).
- **RNF (Requisito Não Funcional):** Como o sistema deve se comportar em termos de qualidade, segurança e velocidade (ex: `RNF002` - Resposta em menos de 500ms; `RNF003` - Criptografia de senhas com Argon2id).

---

## 📁 2. Estrutura de Pastas e Módulos

A documentação está modularizada em **três grandes domínios funcionais**, correspondentes aos diagramas gerais do sistema:

```
docs/business/use-cases/
├── README.md                                  # Este Índice Mestre e Guia de Rastreabilidade
├── general_customer_use.puml                  # Diagrama PlantUML Geral da Loja Virtual (Cliente)
├── general_seller.puml                        # Diagrama PlantUML Geral do Ponto de Venda (PDV)
├── general_dashboard.puml                     # Diagrama PlantUML Geral do Painel Administrativo
│
├── customer/                                  # 🛒 Loja Virtual & Área do Cliente (29 Casos de Uso)
│   ├── UC_CLI_001_navegar_catalogo.md
│   ├── UC_CLI_002_buscar_produtos_filtros.md
│   ├── UC_CLI_003_visualizar_detalhes_produto.md
│   ├── UC_CLI_004_selecionar_variantes_opcoes.md
│   ├── UC_CLI_005_carregar_midia_cache.md
│   ├── UC_CLI_006_adicionar_ao_carrinho.md
│   ├── UC_CLI_007_calcular_frete_cep.md
│   ├── UC_CLI_008_aplicar_cupom_desconto.md
│   ├── UC_CLI_009_realizar_checkout.md
│   ├── UC_CLI_010_comprar_como_visitante.md
│   ├── UC_CLI_011_processar_pagamento.md
│   ├── UC_CLI_012_cadastrar_nova_conta.md
│   ├── UC_CLI_013_autenticar_login_logout.md
│   ├── UC_CLI_014_recuperar_senha_email.md
│   ├── UC_CLI_015_sincronizar_sessao_redis_carrinho.md
│   ├── UC_CLI_016_acessar_painel_cliente.md
│   ├── UC_CLI_017_gerenciar_dados_cadastrais.md
│   ├── UC_CLI_018_alterar_senha_logado.md
│   ├── UC_CLI_019_gerenciar_livro_enderecos.md
│   ├── UC_CLI_020_gerenciar_lista_desejos.md
│   ├── UC_CLI_021_consultar_pedidos_historico.md
│   ├── UC_CLI_022_consultar_extrato_transacoes.md
│   ├── UC_CLI_023_solicitar_devolucao_rma.md
│   ├── UC_CLI_024_gerenciar_inscricao_newsletter.md
│   ├── UC_CLI_025_criar_solicitacao_orcamento_rfq.md
│   ├── UC_CLI_026_acompanhar_meus_projetos.md
│   ├── UC_CLI_027_comparar_propostas_recebidas.md
│   ├── UC_CLI_028_aceitar_proposta_prestador.md
│   └── UC_CLI_029_aprovar_boq_enviar_carrinho.md
│
├── pos/                                       # 🏪 Ponto de Venda - Balcão & Caixa (15 Casos de Uso)
│   ├── UC_POS_001_navegar_catalogo_pdv.md
│   ├── UC_POS_002_selecionar_variacoes_pdv.md
│   ├── UC_POS_003_verificar_estoque_pdv.md
│   ├── UC_POS_004_identificar_cadastrar_cliente_pdv.md
│   ├── UC_POS_005_criar_pre_venda_pdv.md
│   ├── UC_POS_006_adicionar_itens_carrinho_pdv.md
│   ├── UC_POS_007_salvar_pre_venda_pendente.md
│   ├── UC_POS_008_gerar_ticket_pre_venda.md
│   ├── UC_POS_009_localizar_pre_venda_ticket.md
│   ├── UC_POS_010_processar_pagamento_caixa.md
│   ├── UC_POS_011_pagar_com_pix_qrcode.md
│   ├── UC_POS_012_pagar_com_cartao_tef.md
│   ├── UC_POS_013_pagar_com_dinheiro_troco.md
│   ├── UC_POS_014_finalizar_recebimento_venda.md
│   └── UC_POS_015_imprimir_recibo_nfce.md
│
└── dashboard/                                 # ⚙️ Painel Administrativo On-Premise (9 Casos de Uso)
    ├── UC_ADM_001_gerenciar_catalogo_produtos.md
    ├── UC_ADM_002_gerenciar_clientes_aprovacoes.md
    ├── UC_ADM_003_gerenciar_pedidos_faturamento.md
    ├── UC_ADM_004_gerenciar_devolucoes_trocas.md
    ├── UC_ADM_005_visualizar_relatorios_estatisticas.md
    ├── UC_ADM_006_configurar_lojas_parametros.md
    ├── UC_ADM_007_gerenciar_usuarios_permissoes_rbac.md
    ├── UC_ADM_008_gerenciar_planos_assinatura.md
    └── UC_ADM_009_gerenciar_localizacao_moedas_impostos.md
```

---

## 📋 3. Índice Geral dos 53 Casos de Uso

### 🛒 Módulo 1: Loja Virtual & Portal do Cliente (`customer/`)
*Baseado no diagrama:* [`general_customer_use.puml`](file:///var/www/html/agsonhos/docs/business/use-cases/general_customer_use.puml)  
*Atores:* **Visitante (Guest)**, **Cliente Logado (Customer)**, **Sistema / Gateways**

| ID | Caso de Uso | Pacote / Subdomínio | Relacionamento | Especificação |
| :--- | :--- | :--- | :--- | :--- |
| `UC_CLI_001` | Navegar no Catálogo & Categorias | Catálogo, Busca & Mídia | Direto (Guest/Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_001_navegar_catalogo.md) |
| `UC_CLI_002` | Buscar Produtos com Filtros | Catálogo, Busca & Mídia | Direto (Guest/Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_002_buscar_produtos_filtros.md) |
| `UC_CLI_003` | Visualizar Detalhes do Produto (PDP) | Catálogo, Busca & Mídia | Direto (Guest/Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_003_visualizar_detalhes_produto.md) |
| `UC_CLI_004` | Selecionar Variantes & Opções | Catálogo, Busca & Mídia | `<<extend>>` UC_CLI_003 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_004_selecionar_variantes_opcoes.md) |
| `UC_CLI_005` | Carregar Mídia & Cache On-Demand | Catálogo, Busca & Mídia | `<<include>>` UC_CLI_003 / Sistema | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_005_carregar_midia_cache.md) |
| `UC_CLI_006` | Adicionar ao Carrinho & Gerenciar Itens | Carrinho & Compras | Direto (Guest/Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_006_adicionar_ao_carrinho.md) |
| `UC_CLI_007` | Calcular Frete por CEP | Carrinho & Compras | `<<extend>>` UC_CLI_006 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_007_calcular_frete_cep.md) |
| `UC_CLI_008` | Aplicar Cupom de Desconto | Carrinho & Compras | `<<extend>>` UC_CLI_009 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_008_aplicar_cupom_desconto.md) |
| `UC_CLI_009` | Realizar Checkout | Carrinho & Compras | Direto (Guest/Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_009_realizar_checkout.md) |
| `UC_CLI_010` | Comprar como Visitante (*Guest Checkout*) | Carrinho & Compras | `<<extend>>` UC_CLI_009 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_010_comprar_como_visitante.md) |
| `UC_CLI_011` | Processar Pagamento Online | Carrinho & Compras | `<<include>>` UC_CLI_009 / Sistema | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_011_processar_pagamento.md) |
| `UC_CLI_012` | Cadastrar Nova Conta (*Sign-up* PF/PJ) | Autenticação & Sessão | Direto (Guest) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_012_cadastrar_nova_conta.md) |
| `UC_CLI_013` | Autenticar-se (Login / Logout) | Autenticação & Sessão | Direto (Guest/Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_013_autenticar_login_logout.md) |
| `UC_CLI_014` | Recuperar Senha por E-mail | Autenticação & Sessão | Direto (Guest) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_014_recuperar_senha_email.md) |
| `UC_CLI_015` | Sincronizar Sessão Redis & Carrinho | Autenticação & Sessão | `<<include>>` UC_CLI_013 / Sistema | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_015_sincronizar_sessao_redis_carrinho.md) |
| `UC_CLI_016` | Acessar Painel do Cliente | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_016_acessar_painel_cliente.md) |
| `UC_CLI_017` | Gerenciar Dados Cadastrais (`/account/edit`) | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_017_gerenciar_dados_cadastrais.md) |
| `UC_CLI_018` | Alterar Senha Logado (`/account/password`) | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_018_alterar_senha_logado.md) |
| `UC_CLI_019` | Gerenciar Livro de Endereços (`/account/addresses`) | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_019_gerenciar_livro_enderecos.md) |
| `UC_CLI_020` | Gerenciar Lista de Desejos (`/account/wishlist`) | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_020_gerenciar_lista_desejos.md) |
| `UC_CLI_021` | Consultar Pedidos & Histórico (`/account/orders`) | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_021_consultar_pedidos_historico.md) |
| `UC_CLI_022` | Consultar Extrato & Transações (`/account/transaction`) | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_022_consultar_extrato_transacoes.md) |
| `UC_CLI_023` | Solicitar Devolução / RMA (`/account/return`) | Minha Conta | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_023_solicitar_devolucao_rma.md) |
| `UC_CLI_024` | Gerenciar Inscrição na Newsletter | Minha Conta | Direto (Customer/Guest) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_024_gerenciar_inscricao_newsletter.md) |
| `UC_CLI_025` | Criar Solicitação de Orçamento (RFQ) (`/projetos/novo`) | Cotações & BoQ | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_025_criar_solicitacao_orcamento_rfq.md) |
| `UC_CLI_026` | Acompanhar Meus Projetos (`/account/projetos`) | Cotações & BoQ | Direto (Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_026_acompanhar_meus_projetos.md) |
| `UC_CLI_027` | Comparar Propostas Recebidas (`/propostas`) | Cotações & BoQ | `<<extend>>` UC_CLI_026 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_027_comparar_propostas_recebidas.md) |
| `UC_CLI_028` | Aceitar Proposta de Prestador (`/aceitar`) | Cotações & BoQ | `<<extend>>` UC_CLI_027 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_028_aceitar_proposta_prestador.md) |
| `UC_CLI_029` | Aprovar BoQ & Enviar ao Carrinho (`/boq`) | Cotações & BoQ | `<<extend>>` UC_CLI_026 + `<<include>>` UC_CLI_006 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/customer/UC_CLI_029_aprovar_boq_enviar_carrinho.md) |

---

### 🏪 Módulo 2: Ponto de Venda / PDV (`pos/`)
*Baseado no diagrama:* [`general_seller.puml`](file:///var/www/html/agsonhos/docs/business/use-cases/general_seller.puml)  
*Atores:* **Vendedor de Balcão (Sales Representative)**, **Operador de Caixa (Cashier)**, **Cliente Presencial (Customer)**

| ID | Caso de Uso | Módulo / Perfil | Relacionamento | Especificação |
| :--- | :--- | :--- | :--- | :--- |
| `UC_POS_001` | Navegar no Catálogo de Produtos | Vendedor de Balcão | Direto (SalesRep) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_001_navegar_catalogo_pdv.md) |
| `UC_POS_002` | Selecionar Variações do Produto | Vendedor de Balcão | `<<extend>>` UC_POS_001 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_002_selecionar_variacoes_pdv.md) |
| `UC_POS_003` | Verificar Disponibilidade de Estoque | Vendedor de Balcão | `<<include>>` UC_POS_001 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_003_verificar_estoque_pdv.md) |
| `UC_POS_004` | Identificar / Cadastrar Cliente no Balcão | Vendedor de Balcão | Direto (SalesRep & Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_004_identificar_cadastrar_cliente_pdv.md) |
| `UC_POS_005` | Criar Pré-Venda no Balcão | Vendedor de Balcão | Direto (SalesRep) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_005_criar_pre_venda_pdv.md) |
| `UC_POS_006` | Adicionar Itens ao Carrinho do Balcão | Vendedor de Balcão | `<<include>>` UC_POS_005 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_006_adicionar_itens_carrinho_pdv.md) |
| `UC_POS_007` | Salvar Pré-Venda Pendente | Vendedor de Balcão | `<<include>>` UC_POS_005 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_007_salvar_pre_venda_pendente.md) |
| `UC_POS_008` | Gerar e Imprimir Ticket de Pré-Venda | Vendedor de Balcão | `<<include>>` UC_POS_007 / Customer | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_008_gerar_ticket_pre_venda.md) |
| `UC_POS_009` | Localizar Pré-Venda por Ticket | Operador de Caixa | Direto (Cashier & Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_009_localizar_pre_venda_ticket.md) |
| `UC_POS_010` | Processar Pagamento no Caixa | Operador de Caixa | Direto (Cashier & Customer) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_010_processar_pagamento_caixa.md) |
| `UC_POS_011` | Pagar com Pix via QR Code Dinâmico | Operador de Caixa | Generalização de UC_POS_010 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_011_pagar_com_pix_qrcode.md) |
| `UC_POS_012` | Pagar com Cartão via TEF | Operador de Caixa | Generalização de UC_POS_010 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_012_pagar_com_cartao_tef.md) |
| `UC_POS_013` | Pagar com Dinheiro com Cálculo de Troco | Operador de Caixa | Generalização de UC_POS_010 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_013_pagar_com_dinheiro_troco.md) |
| `UC_POS_014` | Finalizar Pagamento e Venda | Operador de Caixa | Direto (Cashier) | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_014_finalizar_recebimento_venda.md) |
| `UC_POS_015` | Imprimir Recibo e Cupom Fiscal NFC-e | Operador de Caixa | `<<include>>` UC_POS_014 | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/pos/UC_POS_015_imprimir_recibo_nfce.md) |

---

### ⚙️ Módulo 3: Painel Administrativo / Dashboard (`dashboard/`)
*Baseado no diagrama:* [`general_dashboard.puml`](file:///var/www/html/agsonhos/docs/business/use-cases/general_dashboard.puml)  
*Atores:* **Operador do Painel (Operator)**, **Administrador Geral (Admin)**

| ID | Caso de Uso | Domínio de Operação | Nível de Permissão | Especificação |
| :--- | :--- | :--- | :--- | :--- |
| `UC_ADM_001` | Gerenciar Catálogo (Produtos, Categorias, Opções) | Negócio & Catálogo | Operador & Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_001_gerenciar_catalogo_produtos.md) |
| `UC_ADM_002` | Gerenciar Clientes & Grupos de Clientes B2B | Negócio & Comercial | Operador & Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_002_gerenciar_clientes_aprovacoes.md) |
| `UC_ADM_003` | Gerenciar Pedidos, Expedição & Faturamento | Negócio & Vendas | Operador & Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_003_gerenciar_pedidos_faturamento.md) |
| `UC_ADM_004` | Gerenciar Devoluções, Trocas & Logística Reversa | Negócio & SAC (RMA) | Operador & Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_004_gerenciar_devolucoes_trocas.md) |
| `UC_ADM_005` | Visualizar Relatórios & Estatísticas (Analytics) | Negócio & BI | Operador & Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_005_visualizar_relatorios_estatisticas.md) |
| `UC_ADM_006` | Configurar Lojas & Parâmetros do Motor | Sistema & Multi-Loja | Exclusivo Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_006_configurar_lojas_parametros.md) |
| `UC_ADM_007` | Gerenciar Usuários & Grupos de Permissões (RBAC) | Sistema & Segurança | Exclusivo Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_007_gerenciar_usuarios_permissoes_rbac.md) |
| `UC_ADM_008` | Gerenciar Planos de Assinatura & Recorrência | Sistema & Monetização | Exclusivo Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_008_gerenciar_planos_assinatura.md) |
| `UC_ADM_009` | Gerenciar Localização, Moedas & Impostos | Sistema & Fiscal | Exclusivo Admin | [Visualizar](file:///var/www/html/agsonhos/docs/business/use-cases/dashboard/UC_ADM_009_gerenciar_localizacao_moedas_impostos.md) |

---

## 🔗 4. Matriz de Rastreabilidade Bidirecional (Requisitos x Casos de Uso)

| Requisito Funcional (RF) | Regra de Negócio (RN) | Casos de Uso Associados |
| :--- | :--- | :--- |
| **RF001** (Cadastro produtos/fotos HD) | `RN003` | `UC_CLI_001`, `UC_CLI_003`, `UC_CLI_005`, `UC_POS_001`, `UC_ADM_001` |
| **RF002** (Specs técnicas obrigatórias) | `RN002`, `RN003` | `UC_CLI_003`, `UC_POS_001`, `UC_ADM_001` |
| **RF003** (Categorização e taxonomia) | `RN003` | `UC_CLI_001`, `UC_ADM_001` |
| **RF004** (Venda de múltiplas unidades m²/cx) | `RN001` | `UC_CLI_004`, `UC_CLI_006`, `UC_CLI_025`, `UC_POS_002`, `UC_POS_006`, `UC_ADM_001` |
| **RF005** (CRUD Admin de produtos/SKUs) | `RN001`, `RN002`, `RN003` | `UC_ADM_001` |
| **RF006** (Gestão automática de inventário) | `RN005`, `RN006` | `UC_POS_003`, `UC_POS_006`, `UC_POS_007`, `UC_POS_014`, `UC_ADM_005` |
| **RF007** (Criação de kits/combos) | `RN004` | `UC_CLI_001`, `UC_ADM_001` |
| **RF008** (Sugestão de correlatos / Cross-selling) | `RN003` | `UC_CLI_003` |
| **RF009** (Carrinho de compras persistente) | `RN001`, `RN005` | `UC_CLI_006`, `UC_CLI_008`, `UC_CLI_015`, `UC_CLI_020`, `UC_CLI_029`, `UC_POS_005` |
| **RF010** (Cálculo de frete dinâmico/cubagem) | `RN002`, `RN007`, `RN008` | `UC_CLI_007`, `UC_CLI_009`, `UC_CLI_027` |
| **RF011** (Busca Full-Text e filtros facetados) | `RN003` | `UC_CLI_001`, `UC_CLI_002` |
| **RF012** (Página de detalhes PDP dedicada) | `RN001`, `RN002`, `RN003` | `UC_CLI_003`, `UC_CLI_004`, `UC_POS_002` |
| **RF013** (Avaliações e reviews de clientes) | `RN014` | `UC_CLI_003` |
| **RF014** (Autenticação e cadastro PF/PJ) | `RN017` | `UC_CLI_010`, `UC_CLI_012`, `UC_CLI_013`, `UC_CLI_016`, `UC_CLI_017`, `UC_POS_004`, `UC_ADM_002` |
| **RF015** (Recuperação de credenciais/senha) | - | `UC_CLI_014`, `UC_CLI_018` |
| **RF016** (Histórico de pedidos e compras) | `RN012`, `RN013` | `UC_CLI_016`, `UC_CLI_021`, `UC_CLI_022`, `UC_CLI_023`, `UC_ADM_003` |
| **RF017** (Múltiplos Shiptos / Endereços de obra) | `RN002` | `UC_CLI_009`, `UC_CLI_012`, `UC_CLI_019`, `UC_ADM_002` |
| **RF018** (Multi-meios de pagamento PIX/Cartão/Boleto)| `RN016` | `UC_CLI_009`, `UC_CLI_010`, `UC_CLI_011`, `UC_CLI_028`, `UC_POS_009`, `UC_POS_010`, `UC_POS_011`, `UC_POS_012`, `UC_POS_013`, `UC_ADM_008` |
| **RF019** (Integração Gateway seguro / TEF) | `RN016` | `UC_CLI_011`, `UC_POS_010`, `UC_POS_011`, `UC_POS_012` |
| **RF020** (Faturamento e emissão NF-e / NFC-e) | `RN012` | `UC_CLI_009`, `UC_CLI_011`, `UC_CLI_021`, `UC_CLI_023`, `UC_POS_014`, `UC_POS_015`, `UC_ADM_003`, `UC_ADM_004`, `UC_ADM_009` |
| **RF021** (Modalidades de entrega e retirada BOPIS)| `RN008` | `UC_CLI_007`, `UC_POS_005`, `UC_POS_008` |
| **RF022** (Rastreamento logístico Last-mile) | `RN013` | `UC_CLI_021`, `UC_ADM_003` |
| **RF023** (Gestão administrativa global e preços) | `RN017`, `RN018` | `UC_ADM_001`, `UC_ADM_002`, `UC_ADM_003`, `UC_ADM_004`, `UC_ADM_006`, `UC_ADM_007`, `UC_ADM_008`, `UC_ADM_009` |
| **RF024** (Alerta proativo de ruptura / Stockout) | `RN006` | `UC_ADM_005` |
| **RF025** (Painel analítico e relatórios gerenciais)| `RN015` | `UC_ADM_005` |

---

## 🎓 5. Dicas para a Apresentação na Faculdade

1. **Mostre a coerência:** Explique que o sistema possui 3 diagramas panorâmicos (Loja Virtual, Ponto de Venda e Painel Administrativo) e que **cada elipse do diagrama possui seu arquivo de especificação individual**, totalizando 53 casos de uso.
2. **Destaque os diferenciais do nicho:** Enfatize para o professor as particularidades de materiais de construção documentadas nos casos de uso:
   - Cálculo automático de metros quadrados para caixas de porcelanatos (`RN001`);
   - Cálculo de frete pesado por cubagem de caminhão vs. correios leves (`RN002` e `RN007`);
   - Diferenciação de preços no balcão e e-commerce para construtoras PJ (`RN017`);
   - Regras do CDC com a exceção de retirada presencial em loja BOPIS (`RN011`);
   - Operação de balcão físico com comanda de pré-venda e quitação no caixa com NFC-e (`UC_POS_005` a `UC_POS_015`).
