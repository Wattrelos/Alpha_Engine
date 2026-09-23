# ***Alpha Engine: Plataforma E-commerce On-Premise & Ponto de Venda para Materiais de Construção***

***Matriz de Rastreabilidade e Relacionamento: Regras de Negócio (RN) x Requisitos Funcionais (RF)***  
**Projeto:** Alpha Engine (Plataforma E-commerce On-Premise para Varejo de Materiais de Construção)  
**Disciplina:** Laboratório de Engenharia de Software  

---

## 1. 📑 Índice

- [1. Índice](#1--índice)
- [2. Introdução e Fundamentação Teórica](#2--introdução-e-fundamentação-teórica)
  - [2.1. O que é uma Matriz de Rastreabilidade?](#21-o-que-é-uma-matriz-de-rastreabilidade)
  - [2.2. Importância para a Engenharia de Software](#22-importância-para-a-engenharia-de-software)
- [3. Grade Cruzada de Rastreabilidade (Grid RN x RF)](#3--grade-cruzada-de-rastreabilidade-grid-rn-x-rf)
- [4. Detalhamento Direto: Regras de Negócio (RN) $\rightarrow$ Requisitos Funcionais (RF)](#4--detalhamento-direto-regras-de-negócio-rn-rightarrow-requisitos-funcionais-rf)
  - [4.1. Domínio: Produtos e Catálogo (RN001 a RN004)](#41-domínio-produtos-e-catálogo-rn001-a-rn004)
  - [4.2. Domínio: Inventário e Logística (RN005 a RN008)](#42-domínio-inventário-e-logística-rn005-a-rn008)
  - [4.3. Domínio: Clientes, Atendimento e CDC (RN009 a RN014)](#43-domínio-clientes-atendimento-e-cdc-rn009-a-rn014)
  - [4.4. Domínio: Precificação e Condições Comerciais (RN015 a RN018)](#44-domínio-precificação-e-condições-comerciais-rn015-a-rn018)
- [5. Detalhamento Reverso: Requisitos Funcionais (RF) $\rightarrow$ Regras de Negócio (RN)](#5--detalhamento-reverso-requisitos-funcionais-rf-rightarrow-regras-de-negócio-rn)
- [6. Auditoria de Integridade e Regras de Governança (Check de Nós Órfãos)](#6--auditoria-de-integridade-e-regras-de-governança-check-de-nós-órfãos)
- [7. Análise de Impacto de Mudanças (Impact Analysis)](#7--análise-de-impacto-de-mudanças-impact-analysis)

---

## 2. 📚 Introdução e Fundamentação Teórica

### 2.1. O que é uma Matriz de Rastreabilidade?
Na Engenharia de Software e na Governança de Requisitos, a **Matriz de Rastreabilidade** é uma ferramenta formal que mapeia as relações bidirecionais entre diferentes artefatos de software. Ela estabelece uma ligação explícita entre:
- **Regras de Negócio (RN):** Políticas, restrições operacionais, diretrizes fiscais e exigências legais da empresa (o *porquê* o sistema funciona de determinada forma).
- **Requisitos Funcionais (RF):** Comportamentos, serviços, telas e fluxos automatizados que o software disponibiliza (o *que* o sistema faz).
- **Casos de Uso (UC):** As interações práticas entre atores humanos/sistêmicos e as funções do software.

```
+-----------------------------------------------------------------------------------+
|                        FLUXO DE RASTREABILIDADE BIDIRECIONAL                      |
+-----------------------------------------------------------------------------------+
|   [ Regras de Negócio (RN) ]  <========>  [ Requisitos Funcionais (RF) ]          |
|                 \                                   /                             |
|                  \======> [ Casos de Uso (UC) ] <===/                             |
+-----------------------------------------------------------------------------------+
```

### 2.2. Importância para a Engenharia de Software
1. **Garantia de Não-Orfandade:** Garante que nenhuma regra de negócio definida pelos *stakeholders* seja esquecida no desenvolvimento, e que nenhum requisito funcional seja construído sem um propósito claro de negócio (*Gold Plating*).
2. **Análise de Impacto Rápida:** Se uma lei (ex: CDC) ou política comercial mudar, a equipe sabe exatamente quais telas, controllers e testes precisam ser refatorados.
3. **Facilidade de Auditoria Acadêmica e Profissional:** Demonstra rigor metodológico para avaliadores, clientes e órgãos reguladores.

---

## 3. 📊 Grade Cruzada de Rastreabilidade (Grid RN x RF)

A tabela abaixo cruza todas as **18 Regras de Negócio** (`RN001` a `RN018`) com todos os **25 Requisitos Funcionais** (`RF001` a `RF025`).  
O símbolo `[X]` indica que o Requisito Funcional implementa ou valida a respectiva Regra de Negócio.

| Regra de Negócio (RN) | RF001 | RF002 | RF003 | RF004 | RF005 | RF006 | RF007 | RF008 | RF009 | RF010 | RF011 | RF012 | RF013 | RF014 | RF015 | RF016 | RF017 | RF018 | RF019 | RF020 | RF021 | RF022 | RF023 | RF024 | RF025 | Total RFs |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **RN001** (Variações m²/cx) | | | | **X** | **X** | | | | **X** | | | **X** | | | | | | | | | | | | | | **4** |
| **RN002** (Cubagem e Peso) | | **X** | | | **X** | | | | | **X** | | **X** | | | | | **X** | | | | | | | | | **5** |
| **RN003** (Specs Técnicas) | **X** | **X** | **X** | | **X** | | | **X** | | | **X** | **X** | | | | | | | | | | | | | | **7** |
| **RN004** (Kits e Combos) | | | | | | | **X** | | | | | | | | | | | | | | | | | | | **1** |
| **RN005** (Estoque Realtime) | | | | | | **X** | | | **X** | | | | | | | | | | | | | | | | | **2** |
| **RN006** (Alerta Baixo Estoque) | | | | | | **X** | | | | | | | | | | | | | | | | | | **X** | | **2** |
| **RN007** (Opções de Frete) | | | | | | | | | | **X** | | | | | | | | | | | | | | | | **1** |
| **RN008** (Frete Grátis/BOPIS) | | | | | | | | | | **X** | | | | | | | | | | | **X** | | | | | **2** |
| **RN009** (Política Devolução) | | | | | | | | | | | | | | | | **X** | | | | **X** | | | **X** | | | **3** |
| **RN010** (Troca Mat. Sensíveis) | | | | | | | | | | | | | | | | **X** | | | | **X** | | | | | | **2** |
| **RN011** (CDC 7 Dias / BOPIS) | | | | | | | | | | | | | | | | **X** | | | | **X** | **X** | | | | | **3** |
| **RN012** (Doc. Fiscal / NF-e) | | | | | | | | | | | | | | | | **X** | | | | **X** | | | | | | **2** |
| **RN013** (Rastreio / Atrasos) | | | | | | | | | | | | | | | | **X** | | | | | | **X** | | | | **2** |
| **RN014** (Suporte Pós-Venda) | | | | | | | | | | | | | **X** | | | | | | | | | | | | | **1** |
| **RN015** (Desconto Volume) | | | | | | | | | **X** | | | | | | | | | | | | | | **X** | | **X** | **3** |
| **RN016** (Desconto PIX/Vista) | | | | | | | | | | | | | | | | | | **X** | **X** | | | | | | | **2** |
| **RN017** (Preço Varejo/Atacado) | | | | | | | | | | | | | | **X** | | | | | | | | | **X** | | | **2** |
| **RN018** (Campanhas Categoria) | | | | | | | | | | | | | | | | | | | | | | | **X** | | | **1** |
| **Total de RNs por RF** | **1** | **2** | **1** | **1** | **3** | **2** | **1** | **1** | **3** | **3** | **1** | **3** | **1** | **1** | **0** | **5** | **1** | **1** | **1** | **3** | **1** | **1** | **3** | **1** | **1** | **41** |

---

## 4. 🔍 Detalhamento Direto: Regras de Negócio (RN) $\rightarrow$ Requisitos Funcionais (RF)

---

### 4.1. Domínio: Produtos e Catálogo (RN001 a RN004)

#### 🔹 RN001 - Variações de Unidades de Medida (Venda Fracionada)
- **Descrição da Regra:** Produtos da categoria de pisos, porcelanatos e azulejos devem permitir entrada pelo cliente em metros quadrados ($m^2$), convertendo compulsoriamente para embalagens comerciais fechadas (caixas) com arredondamento para cima e cálculo automático do valor total.
- **Requisitos Funcionais Associados:**
  - `RF004` (Venda fracionada e múltiplas unidades de medida)
  - `RF005` (CRUD administrativo para cadastro de fator de conversão de caixa)
  - `RF009` (Carrinho de compras calculando quantidade exata de caixas)
  - `RF012` (PDP com calculadora interativa de área e caixas)
- **Casos de Uso Vinculados:** `UC_CLI_004`, `UC_CLI_006`, `UC_CLI_025`, `UC_CLI_029`, `UC_POS_002`, `UC_POS_006`, `UC_ADM_001`.

#### 🔹 RN002 - Peso e Dimensões Obrigatórios para Cubagem
- **Descrição da Regra:** Todo produto cadastrado deve conter obrigatoriamente peso bruto (kg), altura (cm), largura (cm) e comprimento (cm), permitindo o cálculo do frete cúbico para transporte rodoviário.
- **Requisitos Funcionais Associados:**
  - `RF002` (Exibição de dimensões e peso na ficha técnica)
  - `RF005` (Validação obrigatória de preenchimento no cadastro de produto)
  - `RF010` (Motor dinâmico de frete por cubagem - `ShippingStrategyManager`)
  - `RF012` (Simulador de frete na PDP baseado no peso cúbico)
  - `RF017` (Roteamento de entrega para múltiplos endereços de obras)
- **Casos de Uso Vinculados:** `UC_CLI_003`, `UC_CLI_007`, `UC_CLI_019`, `UC_POS_001`, `UC_ADM_001`, `UC_ADM_009`.

#### 🔹 RN003 - Informações Técnicas Obrigatórias por Categoria
- **Descrição da Regra:** Cada categoria de material de construção exige atributos técnicos indispensáveis para evitar compra errônea (ex: Voltagem 110V/220V em ferramentas elétricas; Rendimento e Tempo de Secagem em tintas; Resistência e Classe de Cura em cimentos).
- **Requisitos Funcionais Associados:**
  - `RF001` (Fotos em alta definição evidenciando detalhes técnicos)
  - `RF002` (Exibição estruturada das especificações técnicas por categoria)
  - `RF003` (Taxonomia e filtros vinculados a atributos específicos)
  - `RF005` (Formulário dinâmico de cadastro de atributos técnicos no Admin)
  - `RF008` (Recomendações de produtos complementares baseadas em compatibilidade)
  - `RF011` (Mecanismo de busca indexada filtrando por voltagem, marca e acabamento)
  - `RF012` (Ficha técnica completa na Página de Detalhes do Produto)
- **Casos de Uso Vinculados:** `UC_CLI_001`, `UC_CLI_002`, `UC_CLI_003`, `UC_CLI_004`, `UC_POS_001`, `UC_POS_002`, `UC_ADM_001`.

#### 🔹 RN004 - Kits e Combos de Produtos
- **Descrição da Regra:** Possibilidade de comercializar grupos de mercadorias correlatas com precificação promocional conjunta (ex: "Combo Alvenaria: Tijolos + Areia + Cimento"), vinculando a disponibilidade do kit ao estoque de cada item individual.
- **Requisitos Funcionais Associados:**
  - `RF007` (Criação e comercialização de kits/combos promocionais)
- **Casos de Uso Vinculados:** `UC_CLI_001`, `UC_ADM_001`.

---

### 4.2. Domínio: Inventário e Logística (RN005 a RN008)

#### 🔹 RN005 - Controle Rigoroso de Estoque em Tempo Real
- **Descrição da Regra:** Baixa atômica e síncrona do saldo físico de estoque no momento da confirmação do pagamento, com bloqueio concorrente (lock otimista) para impedir venda simultânea do mesmo saldo em canais físicos e digitais.
- **Requisitos Funcionais Associados:**
  - `RF006` (Gestão automática e baixa de inventário em tempo real)
  - `RF009` (Validação de saldo disponível ao adicionar e finalizar carrinho)
- **Casos de Uso Vinculados:** `UC_CLI_006`, `UC_CLI_009`, `UC_CLI_011`, `UC_POS_003`, `UC_POS_006`, `UC_POS_007`, `UC_POS_014`, `UC_ADM_003`.

#### 🔹 RN006 - Alerta Proativo de Baixo Estoque (Stockout Threshold)
- **Descrição da Regra:** Disparo de alerta visual e notificação automática para a equipe de compras assim que o saldo de um SKU atinge ou ultrapassa o ponto de pedido mínimo de segurança configurado (`quantity <= min_stock_threshold`).
- **Requisitos Funcionais Associados:**
  - `RF006` (Monitoramento contínuo de saldo de produtos críticos)
  - `RF024` (Dashboard e envio de alertas proativos de ruptura para o gestor)
- **Casos de Uso Vinculados:** `UC_POS_003`, `UC_ADM_005`.

#### 🔹 RN007 - Múltiplas Opções de Frete por Cubagem
- **Descrição da Regra:** A plataforma deve segregar os métodos de transporte conforme a natureza da carga: envio padrão (Correios PAC/SEDEX) para pacotes pequenos e leves; transportadoras rodoviárias especializadas com caminhão munck para mercadorias pesadas e volumosas.
- **Requisitos Funcionais Associados:**
  - `RF010` (Cálculo dinâmico de frete por peso, cubagem e CEP)
- **Casos de Uso Vinculados:** `UC_CLI_007`, `UC_CLI_009`, `UC_CLI_027`.

#### 🔹 RN008 - Frete Grátis Condicionado e Retirada na Loja (BOPIS)
- **Descrição da Regra:** Concessão de frete gratuito condicionada ao valor mínimo de compra por região e oferta universal da modalidade de Retirada na Loja Física (*Buy Online, Pick Up in Store* - BOPIS) sem custo adicional com separação expressa.
- **Requisitos Funcionais Associados:**
  - `RF010` (Regras de isenção de frete no checkout)
  - `RF021` (Seleção de modalidades de entrega e retirada presencial)
- **Casos de Uso Vinculados:** `UC_CLI_007`, `UC_CLI_009`, `UC_POS_005`, `UC_POS_008`.

---

### 4.3. Domínio: Clientes, Atendimento e CDC (RN009 a RN014)

#### 🔹 RN009 - Política de Devolução Padronizada
- **Descrição da Regra:** Definição clara dos procedimentos e prazos para devolução e troca de mercadorias no e-commerce, com abertura de chamado formal de RMA no painel do cliente.
- **Requisitos Funcionais Associados:**
  - `RF016` (Histórico de pedidos com atalho para solicitação de devolução)
  - `RF020` (Emissão de Nota Fiscal de Entrada de devolução)
  - `RF023` (Gestão administrativa de pós-venda no painel)
- **Casos de Uso Vinculados:** `UC_CLI_021`, `UC_CLI_023`, `UC_ADM_004`.

#### 🔹 RN010 - Clareza e Critérios em Trocas de Materiais Sensíveis
- **Descrição da Regra:** Regras restritivas para materiais sensíveis que não admitem devolução após abertura ou violação da embalagem lacrada (ex: tintas preparadas em máquina tintométrica sob medida, argamassas empedradas por exposição ao tempo ou pisos assentados).
- **Requisitos Funcionais Associados:**
  - `RF016` (Termo de ciência no momento da solicitação de RMA)
  - `RF020` (Laudo de recusa de devolução)
- **Casos de Uso Vinculados:** `UC_CLI_023`, `UC_ADM_004`.

#### 🔹 RN011 - Direito de Arrependimento e Exceção BOPIS (Art. 49 CDC)
- **Descrição da Regra:** Concessão incondicional do prazo de reflexão de 7 dias corridos para compras entregues em domicílio, com aplicação da exceção legal para compras com retirada presencial na loja física (BOPIS), onde o cliente inspecionou o produto no balcão.
- **Requisitos Funcionais Associados:**
  - `RF016` (Validação de data de entrega para abertura de arrependimento)
  - `RF020` (Geração de estorno e NF-e de retorno)
  - `RF021` (Bloqueio automático de arrependimento para pedidos retirados em loja)
- **Casos de Uso Vinculados:** `UC_CLI_023`, `UC_ADM_004`.

#### 🔹 RN012 - Documentação Obrigatória para Retorno e NF-e
- **Descrição da Regra:** Toda devolução, troca ou transporte de mercadoria requer a apresentação da Nota Fiscal original (DANFE) e etiqueta de identificação do produto, com emissão mandatória da NF-e de devolução (Entrada).
- **Requisitos Funcionais Associados:**
  - `RF016` (Download do DANFE/XML pelo cliente em PDF)
  - `RF020` (Emissão automatizada de NF-e e NFC-e integrada à SEFAZ)
- **Casos de Uso Vinculados:** `UC_CLI_021`, `UC_CLI_023`, `UC_POS_014`, `UC_POS_015`, `UC_ADM_003`, `UC_ADM_004`, `UC_ADM_009`.

#### 🔹 RN013 - Comunicação Proativa de Atrasos na Entrega
- **Descrição da Regra:** Disparo automatizado de alertas via e-mail e SMS/WhatsApp para o cliente sempre que houver intercorrência na rota ou alteração na data de previsão de entrega informada pela transportadora.
- **Requisitos Funcionais Associados:**
  - `RF016` (Atualização da linha do tempo no painel do cliente)
  - `RF022` (Rastreamento logístico *last-mile* integrado)
- **Casos de Uso Vinculados:** `UC_CLI_021`, `UC_ADM_003`.

#### 🔹 RN014 - Suporte Pós-Venda Técnico Especializado
- **Descrição da Regra:** Manutenção de canais de atendimento (chat, WhatsApp e central técnica) com roteamento de dúvidas sobre instalação, cálculo de dosagem e rendimento para especialistas da categoria.
- **Requisitos Funcionais Associados:**
  - `RF013` (Sistema de dúvidas, avaliações e comentários em produtos)
- **Casos de Uso Vinculados:** `UC_CLI_003`.

---

### 4.4. Domínio: Precificação e Condições Comerciais (RN015 a RN018)

#### 🔹 RN015 - Descontos Progressivos por Volume
- **Descrição da Regra:** Aplicação de descontos percentuais automáticos por escala em materiais de construção pesados (cimento, areia, brita, blocos, tijolos) para compras em grande volume.
- **Requisitos Funcionais Associados:**
  - `RF009` (Recálculo dinâmico da tabela de preços na comanda/carrinho)
  - `RF023` (Configuração de faixas de desconto por quantidade no Admin)
  - `RF025` (Relatórios de vendas segregados por faixa de volume)
- **Casos de Uso Vinculados:** `UC_CLI_006`, `UC_CLI_025`, `UC_CLI_027`, `UC_POS_006`, `UC_ADM_005`.

#### 🔹 RN016 - Descontos por Modalidade de Pagamento à Vista
- **Descrição da Regra:** Concessão de abatimento percentual configurável (ex: 5% a 10%) para pagamentos com liquidação imediata em dinheiro ou PIX, tanto na loja virtual quanto no terminal físico do caixa.
- **Requisitos Funcionais Associados:**
  - `RF018` (Checkout multi-meios com desconto aplicado no PIX)
  - `RF019` (Integração com gateway para geração de QR Code com valor líquido)
- **Casos de Uso Vinculados:** `UC_CLI_009`, `UC_CLI_011`, `UC_POS_008`, `UC_POS_010`, `UC_POS_011`, `UC_POS_013`.

#### 🔹 RN017 - Diferenciação de Preço Varejo (B2C) vs. Atacado (B2B)
- **Descrição da Regra:** Operação em dupla tabela de preços baseada no perfil cadastral: preços padrão para consumidores finais (CPF) e preços atacadistas com desconto corporativo para empresas de construção civil (CNPJ com Inscrição Estadual ativa).
- **Requisitos Funcionais Associados:**
  - `RF014` (Cadastro com validação de CNPJ e enquadramento em grupo de clientes)
  - `RF023` (Gestão de múltiplas listas de preços no catálogo administrativo)
- **Casos de Uso Vinculados:** `UC_CLI_001`, `UC_CLI_012`, `UC_CLI_015`, `UC_POS_004`, `UC_POS_006`, `UC_ADM_001`, `UC_ADM_002`, `UC_ADM_008`.

#### 🔹 RN018 - Campanhas Promocionais Sazonais e Segmentadas
- **Descrição da Regra:** Criação de promoções com cronograma de validade temporal delimitado aplicadas a departamentos específicos (ex: "Semana do Piso", "Mês das Ferramentas").
- **Requisitos Funcionais Associados:**
  - `RF023` (Painel administrativo para programação de cupons e promoções sazonais)
- **Casos de Uso Vinculados:** `UC_CLI_008`, `UC_CLI_024`, `UC_ADM_001`.

---

## 5. 🔄 Detalhamento Reverso: Requisitos Funcionais (RF) $\rightarrow$ Regras de Negócio (RN)

| Requisito Funcional (RF) | Nome do Requisito | Regras de Negócio Implementadas | Justificativa de Engenharia |
| :--- | :--- | :--- | :--- |
| **RF001** | Cadastro de produtos + fotos | `RN003` | Garante fotos HD que destaquem as características técnicas exigidas por categoria. |
| **RF002** | Exibição de especificações técnicas | `RN002`, `RN003` | Apresenta peso/dimensões para cubagem e campos técnicos obrigatórios. |
| **RF003** | Categorização produtos | `RN003` | Organiza taxonomias para direcionar os atributos técnicos corretos. |
| **RF004** | Venda múltiplas unidades | `RN001` | Executa a lógica de conversão de m² para caixas completas de revestimento. |
| **RF005** | CRUD Admin produtos | `RN001`, `RN002`, `RN003` | Valida obrigatoriedade de cubagem, especificações técnicas e tabelas de unidades. |
| **RF006** | Gestão automática inventário | `RN005`, `RN006` | Executa baixa em tempo real e monitora limiar de ruptura de estoque. |
| **RF007** | Criação kits/combos | `RN004` | Gerencia a composição de combos promocionais e controle de estoque de insumos. |
| **RF008** | Sugestão correlatos | `RN003` | Sugere insumos compatíveis com as especificações do produto principal (*Cross-selling*). |
| **RF009** | Cartão/Carrinho compras | `RN001`, `RN005`, `RN015` | Aplica cálculo de caixas m², valida saldo físico e concede descontos progressivos por volume. |
| **RF010** | Cálculo frete dinâmico | `RN002`, `RN007`, `RN008` | Modela frete por cubagem de carga pesada, segrega modalidades e concede frete grátis/BOPIS. |
| **RF011** | Busca + filtros eficientes | `RN003` | Indexa atributos técnicos (voltagem, marca, acabamento) como filtros facetados. |
| **RF012** | PDP (Product Detail Page) | `RN001`, `RN002`, `RN003` | Centraliza calculadora de m², simulador de frete cúbico e ficha técnica por categoria. |
| **RF013** | Avaliações/Reviews | `RN014` | Permite interação pós-venda, dúvidas de aplicação e avaliações de desempenho do produto. |
| **RF014** | Auth/Sign-up | `RN017` | Valida dados de CPF (Varejo) e CNPJ/IE (Atacado B2B) para enquadramento tarifário. |
| **RF015** | Reset senha | *(Requisito de Segurança / RNF003)* | Fluxo de segurança criptográfica com tokens temporários (Argon2id). |
| **RF016** | Histórico pedidos | `RN012`, `RN013` | Disponibiliza DANFE/XML em PDF e exibe linha do tempo com alertas proativos de entrega. |
| **RF017** | Múltiplos SHIPTOS | `RN002` | Permite múltiplos endereços de obra com CEPs individuais para cálculo de frete de caminhão. |
| **RF018** | Multi-meios pagamento | `RN016` | Aplica o desconto comercial de pagamento à vista para pagamentos em PIX ou dinheiro. |
| **RF019** | INTEG Gateway | `RN016` | Comunicação com gateway gerando payload de cobrança com abatimento à vista. |
| **RF020** | Faturamento / NF-e | `RN012` | Emissão automática de NF-e (Modelo 55) e NFC-e (Modelo 65) autorizada na SEFAZ. |
| **RF021** | Modalidades entrega | `RN008`, `RN011` | Suporte a BOPIS (retirada presencial) e validação da exceção legal dos 7 dias do CDC. |
| **RF022** | Last-mile tracking | `RN013` | Rastreamento logístico em tempo real com disparo de notificações de intercorrência. |
| **RF023** | Gestão catálogo/preços | `RN017`, `RN018` | Gestão de preços diferenciados B2C/B2B e agendamento de campanhas promocionais. |
| **RF024** | Alerta Stockout | `RN006` | Notificação e painel de controle para reposição quando o estoque atinge o nível mínimo. |
| **RF025** | Analytics / Relatórios | `RN015` | Relatórios de desempenho de vendas por faixa de quantidade, canal e categoria. |

---

## 6. 🛡️ Auditoria de Integridade e Governança (Check de Nós Órfãos)

Em total conformidade com o motor de rastreabilidade do projeto (`docs/business/traceability-rules.yaml`), foram executadas as seguintes validações formais:

```
+-----------------------------------------------------------------------------------+
|                        AUDITORIA FORMAL DE INTEGRIDADE                            |
+-----------------------------------------------------------------------------------+
| 1. Regra TRC_001 (Mapeamento Obrigatório):                                        |
|    - 100% dos Requisitos Funcionais com lógica possuem rastreio explícito ('trace')|
|    - STATUS: APROVADO [CONFORME]                                                  |
+-----------------------------------------------------------------------------------+
| 2. Regra TRC_002 (Verificação Reversa de Regras Órfãs):                           |
|    - Total de Regras de Negócio Declaradas: 18 (RN001 a RN018)                     |
|    - Total de Regras Mapeadas em RFs: 18 (100% de Cobertura)                       |
|    - Regras Órfãs Detectadas: 0                                                   |
|    - STATUS: APROVADO [CONFORME]                                                  |
+-----------------------------------------------------------------------------------+
| 3. Regra TRC_003 (Análise de Impacto Técnica):                                    |
|    - Diagramas de Sequência e Casos de Uso sincronizados com as RNs               |
|    - STATUS: APROVADO [CONFORME]                                                  |
+-----------------------------------------------------------------------------------+
```

---

## 7. 🎯 Análise de Impacto de Mudanças (Impact Analysis)

Caso qualquer Regra de Negócio precise ser alterada no futuro, a matriz indica os módulos de software, testes e diagramas de sequência impactados:

| Regra Modificada | Componentes de Código / Controllers Impactados | Diagrama de Sequência PUML a Atualizar | Testes Automatizados a Reexecutar |
| :--- | :--- | :--- | :--- |
| **Alteração em RN001** (Unidades/m²) | `ProductOptionController`, `CartService`, `AreaCalculator` | `area_calculation_for_ceramic_flooring_and_wall_tiles.puml` | `tests/Validation/Product/AreaCalculationTest.php` |
| **Alteração em RN002 / RN007** (Frete) | `ShippingStrategyManager`, `CorreiosAdapter`, `FreteTransportadora` | `calculo_frete_strategy.puml` | `tests/Validation/Shipping/ShippingStrategyTest.php` |
| **Alteração em RN005** (Estoque) | `StockDeductionObserver`, `InventoryService`, `PosOrderService` | `fluxo_pedido.puml`, `fluxo_venda_pos.puml`, `lock_otimista_falha.puml` | `tests/Validation/Inventory/StockDeductionTest.php` |
| **Alteração em RN011** (CDC / BOPIS) | `ReturnService`, `AccountReturnController`, `OrderService` | `productReturnSequenceDiagram.puml` | `tests/Validation/Order/ReturnPolicyCdcTest.php` |
| **Alteração em RN016** (Desconto PIX) | `PixGatewayAdapter`, `CheckoutService`, `PosCashierController` | `webhook_pagamento_adapter.puml`, `fluxo_venda_pos.puml` | `tests/Validation/Payment/PixDiscountTest.php` |
| **Alteração em RN017** (Preço B2B) | `CustomerGroupService`, `ProductPriceHydrator`, `AuthService` | `fusao_carrinho_login.puml` | `tests/Validation/Customer/CustomerB2BPriceTest.php` |
