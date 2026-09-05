# DP-102: Elaboração das Especificações de Casos de Uso & Reorganização de Pastas

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-09-03 18:54:11
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/102

## Descrição

# Plano de Implementação - Elaboração das Especificações de Casos de Uso & Reorganização de Pastas

Este plano descreve a reorganização da estrutura de casos de uso em `docs/business/use-cases` e a elaboração completa de arquivos de especificação individual para cada um dos 53 casos de uso modelados nos diagramas gerais:
1. **Loja Virtual / Cliente** (`general_customer_use.puml` - 29 Casos de Uso)
2. **Ponto de Venda / PDV - Vendedor & Caixa** (`general_seller.puml` - 15 Casos de Uso)
3. **Painel Administrativo / Dashboard** (`general_dashboard.puml` - 9 Casos de Uso)

---

## 🎯 Objetivo & Contexto Acadêmico (Para Estudante)

Na Engenharia de Software (disciplinas como *Laboratório de Engenharia de Software*), o **Diagrama de Casos de Uso** mostra uma visão panorâmica ("*o que o sistema faz e quem interage com ele*"). No entanto, o diagrama sozinho não é suficiente para programadores ou analistas: é necessário um documento textual formal chamado **Especificação do Caso de Uso (CDU)** para cada elipse do diagrama.

Cada especificação acadêmica conterá:
- **Identificador e Nome Oficial** (ex: `UC_CLI_001`, `UC_POS_005`, `UC_ADM_001`)
- **Atores** (Primários, Secundários, Sistemas Externos)
- **Descrição Sumária / Resumo**
- **Rastreabilidade Bidirecional** (Requisitos Funcionais `RFxxx`, Regras de Negócio `RNxxx`, Requisitos Não Funcionais `RNFxxx`)
- **Pré-Condições e Pós-Condições**
- **Gatilho (Trigger)**
- **Fluxo Principal (Caminho Feliz / Happy Path)** passo a passo (Ator -> Sistema)
- **Fluxos Alternativos** (ramificações válidas)
- **Fluxos de Exceção** (tratamento de erros e desvios)
- **Regras de Negócio Aplicadas**
- **Campos de Entrada e Saída / Protótipo de Interface**

---

## 📁 Nova Estrutura de Pastas Proposta

Reorganizaremos `docs/business/use-cases/` em pastas temáticas claras:

```
docs/business/use-cases/
├── README.md                                  # Índice mestre, glossário explicativo e matriz de rastreabilidade
├── general_customer_use.puml                  # Diagrama PlantUML Geral do Cliente
├── general_seller.puml                        # Diagrama PlantUML Geral do PDV (Vendedor/Caixa)
├── general_dashboard.puml                     # Diagrama PlantUML Geral do Painel Administrativo
│
├── customer/                                  # 29 Especificações de Casos de Uso do Cliente
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
├── pos/                                       # 15 Especificações de Casos de Uso do PDV
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
└── dashboard/                                 # 9 Especificações de Casos de Uso do Painel Administrativo
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

## 🛠️ Detalhamento das Alterações

### 1. Limpeza & Estruturação
- Remover pastas vazias obsoletas (`UC001_browse_products`, `UC002_identify_register_customer`, `UC003_process_payment`).
- Criar pastas `customer/`, `pos/` e `dashboard/`.

### 2. Elaboração das Especificações
Cada arquivo será escrito com rigor conceitual de Engenharia de Software e linguagem clara e acessível, contemplando:
- Modelos de dados envolvidos (ex: tabelas `tbkk_product`, `tbkk_order`, sessões Redis).
- Validações de negócio (ex: cálculo de metro quadrado para revestimentos, direito de arrependimento pelo CDC, checagem de estoque com lock otimista, regras de idempotência).
- Telas e protótipos textuais para fácil entendimento visual.

### 3. Atualização do `README.md` de Casos de Uso
- Tabela resumo com índice de todos os 53 casos de uso.
- Guia introdutório para estudante leigo sobre como ler e apresentar casos de uso para o professor.
- Matriz de Rastreabilidade (Caso de Uso x RF x RN).

### 4. Atualização dos Documentos da Faculdade
- Atualizar `docs/documentos_para_a_faculdade/Requisitos_para_a_matéria_de_Laboratório_de_Engenharia_de_Software.md`:
  - Marcar `- [x] Especificação do caso de uso`
  - Corrigir referências e links dos diagramas `.puml`.
- Atualizar `docs/documentos_para_a_faculdade/4. Casos de Uso.doc.md` com os links e referências aos novos arquivos modulares.

---

## 🔍 Plano de Verificação

### Verificação Manual & Estrutural
1. **Consistência de Arquivos:** Validar se todos os 53 casos de uso dos 3 diagramas possuem arquivo `.md` correspondente e que nenhum caso de uso ficou órfão.
2. **Validação de Links e Rastreabilidade:** Garantir que todos os caminhos relativos e marcações de RF/RN/RNF correspondem fielmente aos arquivos de requisitos do projeto (`functional_requirements.yaml`, `business_rules.yaml`, `non_functional_requirements.yaml`).
3. **Checagem de Checklist Acadêmico:** Validar a integridade do arquivo `Requisitos_para_a_matéria_de_Laboratório_de_Engenharia_de_Software.md`.

# Resumo das Especificações de Casos de Uso & Reorganização da Documentação

## 🎯 O que foi realizado

Reorganizamos completamente a estrutura de casos de uso em `docs/business/use-cases/` e elaboramos **53 arquivos de especificação detalhados** (um para cada caso de uso modelado nos 3 diagramas gerais do sistema), além do índice mestre com matriz de rastreabilidade e atualização dos documentos acadêmicos da faculdade.

---

## 📁 Estrutura Final Criada

```
docs/business/use-cases/
├── README.md                                  # Índice Mestre, Guia Didático e Matriz de Rastreabilidade
├── general_customer_use.puml                  # Diagrama PlantUML do Cliente
├── general_seller.puml                        # Diagrama PlantUML do PDV (Vendedor/Caixa)
├── general_dashboard.puml                     # Diagrama PlantUML do Painel Administrativo
│
├── customer/                                  # 29 Especificações - Loja Virtual & Área do Cliente
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
├── pos/                                       # 15 Especificações - Ponto de Venda (Balcão & Caixa)
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
└── dashboard/                                 # 9 Especificações - Painel Administrativo On-Premise
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

## 📊 Matriz Resumo por Módulo

| Módulo / Domínio | Diagrama PlantUML de Origem | Qtd. de Casos de Uso | Atores Mapeados |
| :--- | :--- | :---: | :--- |
| **Loja Virtual / Cliente** | [`general_customer_use.puml`](file:///var/www/html/agsonhos/docs/business/use-cases/general_customer_use.puml) | **29** | Visitante (*Guest*), Cliente Logado (*Customer*), Gateways |
| **Ponto de Venda (PDV)** | [`general_seller.puml`](file:///var/www/html/agsonhos/docs/business/use-cases/general_seller.puml) | **15** | Vendedor de Balcão (*SalesRep*), Caixa (*Cashier*), Cliente (*Customer*) |
| **Painel Administrativo** | [`general_dashboard.puml`](file:///var/www/html/agsonhos/docs/business/use-cases/general_dashboard.puml) | **9** | Operador do Painel (*Operator*), Administrador Geral (*Admin*) |
| **Total do Sistema** | **3 Diagramas** | **53 Casos de Uso** | **7 Perfis de Atores** |

---

## 📋 Padrão Adotado em Cada Especificação

Cada um dos 53 arquivos de caso de uso possui:
1. **Tabela de Cabeçalho:** Identificador, Nome, Pacote, Atores Primários/Secundários, Tipo e Rastreabilidade completa (**RF**, **RN**, **RNF**).
2. **Descrição Sumária:** Resumo claro do objetivo da funcionalidade.
3. **Pré-Condições:** O que deve ser verdadeiro antes da execução.
4. **Pós-Condições:** Garantias de estado do sistema após a conclusão.
5. **Gatilho (Trigger):** Ação que inicia o caso de uso.
6. **Fluxo Principal (Caminho Feliz):** Passo a passo numerado da interação Ator $\leftrightarrow$ Sistema.
7. **Fluxos Alternativos:** Desvios normais do fluxo.
8. **Fluxos de Exceção:** Tratamento de erros, falhas de conexão ou falta de estoque.
9. **Regras de Negócio Aplicadas:** Menção explícita às regras `RN001` a `RN018`.
10. **Interface & Campos de Entrada/Saída:** Dados manipulados pelo caso de uso.

---

## 🎓 Atualização dos Documentos da Faculdade

- **Checklist Atualizado:** [`Requisitos_para_a_matéria_de_Laboratório_de_Engenharia_de_Software.md`](file:///var/www/html/agsonhos/docs/documentos_para_a_faculdade/Requisitos_para_a_matéria_de_Laboratório_de_Engenharia_de_Software.md) marcado com `- [x] Especificação do caso de uso` e links corrigidos para os 3 diagramas.
- **Documento Consolidado:** [`4. Casos de Uso.doc.md`](file:///var/www/html/agsonhos/docs/documentos_para_a_faculdade/4. Casos de Uso.doc.md) ajustado com as referências PlantUML atualizadas.

