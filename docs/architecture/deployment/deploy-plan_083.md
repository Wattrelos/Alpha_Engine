# DP-83: Estruturação e Preenchimento do Documento de Requisitos Acadêmico

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-17 11:36:02
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/83

## Descrição

# Plano de Estruturação e Preenchimento do Documento de Requisitos Acadêmico

Consolidação e formalização de todo o levantamento de engenharia de requisitos do projeto **Alpha Engine (E-commerce On-Premise de Materiais de Construção)** no arquivo [`Alpha. Requisitos do Sistema.doc.md`](file:///var/www/html/agsonhos/docs/documentos_para_a_faculdade/Alpha.%20Requisitos%20do%20Sistema.doc.md), alinhando as especificações técnicas, regras de negócio e rastreabilidade com os padrões exigidos no ambiente universitário.

---

## Estrutura Proposta para o Documento

### 1. Atualização do Índice
- Atualização completa do sumário navegável com links âncora para todas as seções (Objetivo, Técnicas, Grupos de RFs, Grupos de RNFs, Regras de Negócio e Matrizes de Rastreabilidade).

### 2. Seção 4: Requisitos Funcionais (RF001 a RF025)
Formatação individual de cada requisito em tabela no padrão acadêmico do template:
- **4.1. Módulo de Produtos & Catálogo**:
  - `RF001`: Cadastro de Produtos com Múltiplas Fotos em Alta Resolução (Alta - [RN003])
  - `RF002`: Exibição de Especificações Técnicas por Categoria (Alta - [RN002, RN003])
  - `RF003`: Categorização e Navegação Hierárquica de Produtos (Média)
  - `RF004`: Venda Fracionada e Múltiplas Unidades de Medida (Média - [RN001])
  - `RF005`: Gestão Administrativa de Produtos e SKUs (Alta)
  - `RF006`: Gestão Automática e Baixa de Inventário em Tempo Real (Alta - [RN005])
- **4.2. Módulo de Compras & Jornada do Cliente**:
  - `RF007`: Criação e Venda de Kits e Combos Promocionais (Baixa - [RN004])
  - `RF008`: Recomendação Inteligente de Produtos Correlatos / Cross-selling (Baixa)
  - `RF009`: Carrinho de Compras Interativo e Persistente (Alta)
  - `RF010`: Cálculo de Frete Dinâmico por Cubagem e CEP (Alta - [RN002, RN007, RN008])
  - `RF011`: Mecanismo de Busca Indexada e Filtros Facetados (Média)
  - `RF012`: Página de Detalhes do Produto (PDP) com Variações (Média)
  - `RF013`: Sistema de Avaliações, Classificação e Comentários (Baixa)
- **4.3. Módulo de Usuários & Clientes**:
  - `RF014`: Autenticação Segura e Cadastro de Clientes (Alta)
  - `RF015`: Recuperação de Acesso e Redefinição de Senha (Baixa)
  - `RF016`: Histórico e Acompanhamento de Pedidos pelo Cliente (Alta)
  - `RF017`: Gestão de Múltiplos Endereços de Entrega (Shiptos) (Baixa)
- **4.4. Módulo de Pagamentos & Faturamento**:
  - `RF018`: Checkout Multi-meios (Cartão de Crédito, Boleto Bancário, PIX) (Alta - [RN016])
  - `RF019`: Integração com Gateway de Pagamento Seguro e Anti-fraude (Baixa)
  - `RF020`: Faturamento e Emissão Automática de Nota Fiscal Eletrônica (NF-e) (Alta - [RN012])
- **4.5. Módulo de Logística & Entrega**:
  - `RF021`: Seleção de Modalidades de Entrega (BOPIS / Retirada e Transportadora) (Média - [RN008])
  - `RF022`: Rastreamento Last-Mile em Tempo Real e Notificação de Status (Média - [RN013])
- **4.6. Módulo de Administração & Governança**:
  - `RF023`: Gestão de Catálogo Global e Precificação Varejo/Atacado (Alta - [RN017, RN018])
  - `RF024`: Alerta Proativo de Ruptura de Estoque (Stockout) (Baixa - [RN006])
  - `RF025`: Painel de Analytics Gerencial e Relatórios de Vendas (Média)

### 3. Seção 5: Requisitos Não Funcionais (RNF001 a RNF008)
Formatação individual de cada RNF em tabela padronizada contendo descrição, métrica de aceitação e estratégia técnica:
- **5.1. Usabilidade e Acessibilidade**: `RNF001` (Interface Intuitiva) e `RNF007` (Design Responsivo Multi-dispositivo).
- **5.2. Desempenho e Escalabilidade**: `RNF002` (Tempo de Carregamento LCP < 2.5s) e `RNF005` (Suporte a Picos de Tráfego / Auto-scaling / CDN).
- **5.3. Confiabilidade e Disponibilidade**: `RNF004` (SLA 99.9% de Disponibilidade).
- **5.4. Segurança e Privacidade**: `RNF003` (Criptografia TLS 1.3 / AES-256 e LGPD) e `RNF008` (Proteção de Diretórios e Prevenção de Directory Listing via Apache/`.htaccess`).
- **5.5. Integração e Interoperabilidade**: `RNF006` (Sincronização com ERP via Webhooks / Mensageria RabbitMQ).

### 4. Seção 6: Regras de Negócio (RN001 a RN018)
Detalhamento aprofundado de todas as 18 regras de negócio já modeladas no projeto:
- **Catálogo e Produtos**: RN001 (Variações e Conversão de Unidades), RN002 (Peso e Cubagem Obrigatórios), RN003 (Specs Técnicas por Categoria), RN004 (Kits e Combos).
- **Inventário e Logística**: RN005 (Baixa de Estoque Realtime), RN006 (Gatilho de Estoque Mínimo), RN007 (Seleção de Frete por Cubagem), RN008 (Frete Grátis e Retirada Presencial).
- **Clientes e CDC**: RN009 (Política de Devolução), RN010 (Critérios de Troca de Materiais Sensíveis), RN011 (Direito de Arrependimento de 7 dias com Exclusão Legal em Retirada Presencial BOPIS), RN012 (Documentação Obrigatória e NF-e de Retorno), RN013 (Comunicação Proativa de Atrasos), RN014 (Suporte Técnico Especializado Pós-Venda).
- **Precificação e Condições**: RN015 (Desconto Progressivo por Volume), RN016 (Desconto por Meio À Vista - PIX/Dinheiro), RN017 (Tabela Diferenciada B2C vs B2B / CPF vs CNPJ), RN018 (Campanhas Promocionais Sazonais).

### 5. Seção 7: Matrizes de Rastreabilidade
- **7.1 Matriz Requisitos Funcionais (RF) x Regras de Negócio (RN)**: Mapeamento bidirecional completo assegurando que nenhuma regra fique órfã (conforme diretriz `TRC_001` e `TRC_002`).
- **7.2 Matriz Requisitos Funcionais (RF) x Requisitos Não Funcionais (RNF)**: Mapeamento de suporte de qualidade e restrições não funcionais.

---

## Verificação
- Revisar a integridade das referências de IDs (`RF001`-`RF025`, `RNF001`-`RNF008`, `RN001`-`RN018`).
- Verificar a clareza e fidelidade das regras com os arquivos de origem (`docs/requirements/` e `docs/kb/`).
- Validar a formatação Markdown e a hierarquia dos títulos.

# Relatório de Conclusão: Documento de Requisitos do Sistema

O documento acadêmico de requisitos da plataforma **Alpha Engine** foi preenchido e estruturado com sucesso no arquivo [`Alpha. Requisitos do Sistema.doc.md`](file:///var/www/html/agsonhos/docs/documentos_para_a_faculdade/Alpha.%20Requisitos%20do%20Sistema.doc.md).

---

## O que foi realizado

### 1. Sumário e Navegação Completa
- Sumário atualizado com links âncora para todas as seções, subseções de módulos, regras de negócio e matrizes de rastreabilidade.

### 2. Seção de Requisitos Funcionais (RF001 a RF025)
Consolidação dos 25 requisitos funcionais no formato de tabela acadêmica estruturada, distribuídos em 6 módulos:
- **4.1. Módulo de Produtos & Catálogo:** RF001 a RF006 (Cadastro de fotos HD, Ficha técnica, Categorização, Venda fracionada m²/cx/kg, CRUD administrativo e baixa síncrona de estoque).
- **4.2. Módulo de Compras & Jornada do Cliente:** RF007 a RF013 (Kits/Combos, Cross-selling, Carrinho dinâmico, Cálculo de frete por cubagem, Busca indexada, PDP e Reviews).
- **4.3. Módulo de Usuários & Clientes:** RF014 a RF017 (Cadastro PF/PJ com validação de CPF/CNPJ, Reset de senha, Histórico de pedidos e Múltiplos endereços Shiptos).
- **4.4. Módulo de Pagamentos & Faturamento:** RF018 a RF020 (Multi-meios com desconto no PIX, Gateway de pagamento PCI-DSS e emissão automática de NF-e).
- **4.5. Módulo de Logística & Entrega:** RF021 e RF022 (Modalidades de envio e BOPIS/Retirada, Rastreamento last-mile com aviso de atrasos).
- **4.6. Módulo de Administração & Governança:** RF023 a RF025 (Tabela de preços Varejo x Atacado, Alerta de stockout e Analytics de vendas).

### 3. Seção de Requisitos Não Funcionais (RNF001 a RNF008)
Formatação individual em tabelas com métricas de aceitação e rastreabilidade:
- **5.1. Usabilidade e Acessibilidade:** `RNF001` (Interface Intuitiva - SUS > 80) e `RNF007` (Design Responsivo 320px a 3840px / WCAG 2.1 AA).
- **5.2. Desempenho e Escalabilidade:** `RNF002` (LCP < 2.5s / Core Web Vitals) e `RNF005` (500 RPS com cache distribuído Redis).
- **5.3. Confiabilidade e Disponibilidade:** `RNF004` (SLA 99.9% com failover).
- **5.4. Segurança e Privacidade:** `RNF003` (TLS 1.3, AES-256, Argon2id e LGPD) e `RNF008` (`Options -Indexes` em `.htaccess`).
- **5.5. Integração e Interoperabilidade:** `RNF006` (Sincronização ERP via Webhooks / RabbitMQ < 3s).

### 4. Seção de Regras de Negócio (RN001 a RN018)
Detalhamento minucioso das 18 regras de negócio de domínio:
- Venda fracionada e conversões (RN001), obrigatoriedade de peso e dimensões (RN002), especificações por categoria (RN003), kits promocionais (RN004).
- Estoque realtime (RN005), alerta de estoque mínimo (RN006), segregação de frete Correios vs Transportadora (RN007), frete grátis condicionado (RN008).
- Políticas de troca/devolução (RN009), restrições em tintas e pisos (RN010), **Direito de Arrependimento de 7 dias e sua Exceção Jurídica em Retirada Presencial BOPIS - Art. 49 CDC** (RN011), validação de DANFE/NF-e (RN012), notificação proativa de atrasos (RN013), suporte técnico especializado (RN014).
- Desconto por volume (RN015), desconto PIX à vista (RN016), precificação B2C vs B2B (RN017) e promoções sazonais (RN018).

### 5. Matrizes de Rastreabilidade
- **Matriz RF x RN:** Mapeamento bidirecional garantindo cobertura total de regras sem nós órfãos.
- **Matriz RNF x RF:** Demonstração de aderência técnica da arquitetura aos requisitos funcionais.

---

## Validação

- Arquivo gerado: [`Alpha. Requisitos do Sistema.doc.md`](file:///var/www/html/agsonhos/docs/documentos_para_a_faculdade/Alpha.%20Requisitos%20do%20Sistema.doc.md) (605 linhas, totalmente formatado em Markdown com tabelas compatíveis).

