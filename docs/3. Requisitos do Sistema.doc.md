# ***Alpha Engine: Plataforma E-commerce On-Premise & Ponto de Venda para Materiais de Construção***

***Documento de Engenharia de Requisitos e Regras de Negócio***  
**Projeto:** Alpha Engine (Plataforma E-commerce On-Premise para Varejo de Materiais de Construção)

---

## 1. **Índice**

- [1. Índice](#1-índice)
- [2. Objetivo](#2-objetivo)
- [3. Técnicas Utilizadas na Elucidação de Requisitos](#3-técnicas-utilizadas-na-elucidação-de-requisitos)
- [4. Requisitos Funcionais](#4-requisitos-funcionais)
  - [4.1. Módulo de Produtos & Catálogo](#41-módulo-de-produtos--catálogo)
    - [RF001 - Cadastro de produtos com fotos em alta resolução](#rf001---cadastro-de-produtos-com-fotos-em-alta-resolução)
    - [RF002 - Exibição de especificações técnicas por categoria](#rf002---exibição-de-especificações-técnicas-por-categoria)
    - [RF003 - Categorização e taxonomia hierárquica de produtos](#rf003---categorização-e-taxonomia-hierárquica-de-produtos)
    - [RF004 - Venda fracionada e múltiplas unidades de medida](#rf004---venda-fracionada-e-múltiplas-unidades-de-medida)
    - [RF005 - Gestão administrativa (CRUD) de produtos e SKUs](#rf005---gestão-administrativa-crud-de-produtos-e-skus)
    - [RF006 - Gestão automática e baixa de inventário em tempo real](#rf006---gestão-automática-e-baixa-de-inventário-em-tempo-real)
  - [4.2. Módulo de Compras & Jornada do Cliente](#42-módulo-de-compras--jornada-do-cliente)
    - [RF007 - Criação e comercialização de kits/combos promocionais](#rf007---criação-e-comercialização-de-kitscombos-promocionais)
    - [RF008 - Sugestão de produtos correlatos (Cross-selling)](#rf008---sugestão-de-produtos-correlatos-cross-selling)
    - [RF009 - Carrinho de compras interativo e persistente](#rf009---carrinho-de-compras-interativo-e-persistente)
    - [RF010 - Cálculo de frete dinâmico por peso, cubagem e CEP](#rf010---cálculo-de-frete-dinâmico-por-peso-cubagem-e-cep)
    - [RF011 - Mecanismo de busca indexada e filtros avançados](#rf011---mecanismo-de-busca-indexada-e-filtros-avançados)
    - [RF012 - Página de detalhes do produto (PDP) dedicada](#rf012---página-de-detalhes-do-produto-pdp-dedicada)
    - [RF013 - Sistema de avaliações, classificação e comentários](#rf013---sistema-de-avaliações-classificação-e-comentários)
  - [4.3. Módulo de Usuários & Clientes](#43-módulo-de-usuários--clientes)
    - [RF014 - Autenticação segura e cadastro de clientes (PF/PJ)](#rf014---autenticação-segura-e-cadastro-de-clientes-pfpj)
    - [RF015 - Recuperação de credenciais e redefinição de senha](#rf015---recuperação-de-credenciais-e-redefinição-de-senha)
    - [RF016 - Histórico e rastreamento de compras pelo cliente](#rf016---histórico-e-rastreamento-de-compras-pelo-cliente)
    - [RF017 - Gestão de múltiplos endereços de entrega (Shiptos)](#rf017---gestão-de-múltiplos-endereços-de-entrega-shiptos)
  - [4.4. Módulo de Pagamentos & Faturamento](#44-módulo-de-pagamentos--faturamento)
    - [RF018 - Checkout multi-meios (PIX, Cartão, Boleto)](#rf018---checkout-multi-meios-pix-cartão-boleto)
    - [RF019 - Integração com gateway de pagamento seguro](#rf019---integração-com-gateway-de-pagamento-seguro)
    - [RF020 - Faturamento e emissão de Nota Fiscal Eletrônica (NF-e)](#rf020---faturamento-e-emissão-de-nota-fiscal-eletrônica-nf-e)
  - [4.5. Módulo de Logística & Entrega](#45-módulo-de-logística--entrega)
    - [RF021 - Modalidades de entrega e retirada na loja (BOPIS)](#rf021---modalidades-de-entrega-e-retirada-na-loja-bopis)
    - [RF022 - Rastreamento last-mile e atualização de status](#rf022---rastreamento-last-mile-e-atualização-de-status)
  - [4.6. Módulo de Administração & Governança](#46-módulo-de-administração--governança)
    - [RF023 - Gestão de catálogo global e precificação segmentada](#rf023---gestão-de-catálogo-global-e-precificação-segmentada)
    - [RF024 - Alerta proativo de ruptura de estoque (Stockout)](#rf024---alerta-proativo-de-ruptura-de-estoque-stockout)
    - [RF025 - Painel analítico de vendas e relatórios gerenciais](#rf025---painel-analítico-de-vendas-e-relatórios-gerenciais)
- [5. Requisitos Não Funcionais](#5-requisitos-não-funcionais)
  - [5.1. Usabilidade e Acessibilidade](#51-usabilidade-e-acessibilidade)
  - [5.2. Desempenho e Escalabilidade](#52-desempenho-e-escalabilidade)
  - [5.3. Confiabilidade e Disponibilidade](#53-confiabilidade-e-disponibilidade)
  - [5.4. Segurança e Privacidade](#54-segurança-e-privacidade)
  - [5.5. Integração e Interoperabilidade](#55-integração-e-interoperabilidade)
- [6. Regras de Negócio](#6-regras-de-negócio)
  - [6.1. Produtos e Catálogo](#61-produtos-e-catálogo)
  - [6.2. Inventário e Logística](#62-inventário-e-logística)
  - [6.3. Clientes, Atendimento e CDC](#63-clientes-atendimento-e-cdc)
  - [6.4. Precificação e Condições Comerciais](#64-precificação-e-condições-comerciais)
- [7. Matrizes de Relacionamento (Rastreabilidade)](#7-matrizes-de-relacionamento-rastreabilidade)
  - [7.1. Matriz Requisitos Funcionais (RF) x Regras de Negócio (RN)](#71-matriz-requisitos-funcionais-rf-x-regras-de-negócio-rn)
  - [7.2. Matriz Requisitos Não Funcionais (RNF) x Requisitos Funcionais (RF)](#72-matriz-requisitos-não-funcionais-rnf-x-requisitos-funcionais-rf)

---

## 2. **Objetivo**

2.1. Este documento define formalmente os requisitos funcionais, requisitos não funcionais, regras de negócio e a matriz de rastreabilidade do projeto de um **site de e-commerce On-Premise adaptado à realidade brasileira** (**Alpha Engine**). 

2.2. O objetivo primordial é estruturar uma plataforma modular, escalável e de alto desempenho que atenda às complexidades tributárias, fiscais, logísticas e de consumo do mercado brasileiro. Em seu escopo inicial, o projeto é customizado para suprir as dores operacionais e comerciais do setor de **varejo de materiais para construção** caracterizado pela venda fracionada por unidade/m²/peso, produtos com alta variação de cubagem e restrições legais específicas do Código de Defesa do Consumidor (CDC), mantendo uma arquitetura extensível para expansão a outros nichos do comércio eletrônico.

---

## 3. **Técnicas Utilizadas na Elucidação de Requisitos**

3.1. **Entrevistas Estruturadas com Lojistas do Varejo:** Realização de entrevistas com comerciantes e gestores do varejo de materiais de construção, compreendendo as rotinas de balcão, expedição, processos de cotação para obras civis, garantia de fabricantes, controle de avarias e fluxos de devolução.

3.2. **Análise de Domínio e Benchmarking On-Premise:** Levantamento comparativo de recursos em plataformas líderes de e-commerce e On-Premise de comércio eletrônico, mapeando funcionalidades críticas para checkout transparente, carrinho com cálculo de cubagem, múltiplos centros de distribuição e gestão de múltiplos canais de venda (omnichannel).

3.3. **Prospecção Tecnológica e Padrões Arquiteturais:** Avaliação de arquitetura orientada a serviços leves utilizando PHP 8 / Slim Framework 4 (Single Action Controllers), Twig View Engine, persistência relacional MySQL/MariaDB com Doctrine DBAL/ORM, camada de cache distribuído em Redis e filas assíncronas para desacoplamento de eventos.

3.4. **Engenharia de Conformidade Legal e Normativa:** Mapeamento minucioso da legislação brasileira aplicável ao e-commerce, incluindo a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018), o Código de Defesa do Consumidor (CDC - Lei nº 8.078/1990 com ênfase no Art. 49 sobre arrependimento), e os padrões SEFAZ para faturamento eletrônico (NF-e modelo 55 / NFC-e modelo 65).

3.5. **Engenharia Reversa e Integração com ERPs Especializados:** Avaliação dos sistemas legados de gestão comercial comumente empregados no setor de construção para desenhar pontos de integração via webhooks, APIs REST e filas de sincronização de saldo de estoque em tempo real.

---

## 4. **Requisitos Funcionais**

Os requisitos funcionais estão organizados em **6 módulos temáticos**, refletindo a arquitetura do sistema e a jornada do usuário e dos administradores.

```
+-----------------------------------------------------------------------------------+
|                        MÓDULOS DE REQUISITOS FUNCIONAIS                          |
+---------------------+---------------------+--------------------+------------------+
| 4.1 Produtos/Catál. | 4.2 Compras/Jornada | 4.3 Usuários/Cli.  | 4.4 Pagamentos   |
| (RF001 a RF006)     | (RF007 a RF013)     | (RF014 a RF017)    | (RF018 a RF020)  |
+---------------------+---------------------+--------------------+------------------+
| 4.5 Logística/Frete (RF021 a RF022)       | 4.6 Administração (RF023 a RF025)     |
+-------------------------------------------+---------------------------------------+
```

---

### 4.1. Módulo de Produtos & Catálogo

#### RF001 - Cadastro de produtos com fotos em alta resolução
| RF001 - Cadastro de produtos + fotos | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O sistema deve permitir o upload e a renderização de múltiplas imagens em alta resolução por produto. As imagens devem passar por processamento automatizado de compressão (WebP/JPEG otimizado) e geração de miniaturas (*thumbnails*) para otimização de banda. Requisito associado à obrigatoriedade de especificações visuais claras para evitar compras equivocadas ([RN003](#rn003---especificações-técnicas-obrigatórias-por-categoria)). |
| **Detalhes da implementação prevista:** | Formulário de upload com suporte a *drag-and-drop*, validação de formatos (JPEG, PNG, WebP) e limites de tamanho (máximo 5MB). Armazenamento em CDN/Storage com persistência de URLs relativas na tabela `product_images`. Geração assíncrona de resoluções (200x200, 600x600, 1200x1200). |

#### RF002 - Exibição de especificações técnicas por categoria
| RF002 - Exibição de especificações técnicas | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O sistema deve exigir e exibir atributos técnicos específicos conforme a categoria do item: dimensões físicas, peso líquido/bruto ([RN002](#rn002---peso-e-dimensões-obrigatórios-para-cubagem)), voltagem para ferramentas elétricas (110V, 220V, Bivolt), instruções de diluição e rendimento para tintas, e resistência/secagem para cimentos e argamassas ([RN003](#rn003---especificações-técnicas-obrigatórias-por-categoria)). |
| **Detalhes da implementação prevista:** | Tabela dinâmica de atributos em formato EAV (*Entity-Attribute-Value*) ou coluna JSON estruturada com schema validation. Exibição na página do produto em aba dedicada "Ficha Técnica". |

#### RF003 - Categorização e taxonomia hierárquica de produtos
| RF003 - Categorização de produtos | Prioridade: Média |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Permitir a organização do catálogo em estrutura de árvore hierárquica (Departamento -> Categoria -> Subcategoria), permitindo que um item pertença a uma categoria principal e múltiplas secundárias para facilitar a descoberta de itens na busca. |
| **Detalhes da implementação prevista:** | Estrutura de tabela auto-relacionada `categories` com `parent_id` e caminho indexado (*materialized path* / *slugs* aninhados). Menu de navegação dinâmico com suporte a *mega menu* no cabeçalho do site. |

#### RF004 - Venda fracionada e múltiplas unidades de medida
| RF004 - Venda em múltiplas unidades de medida | Prioridade: Média |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Permitir que materiais de construção (pisos, azulejos, cabos, areia, brita) sejam vendidos por peça unitária, metro quadrado (m²), caixa fechada, metro linear, quilograma (kg) ou litro (L). O sistema deve realizar a conversão automática da cubagem para cálculo de preço total e arredondamento para a menor embalagem comercializável ([RN001](#rn001---variações-de-unidades-de-medida-venda-fracionada)). |
| **Detalhes da implementação prevista:** | Calculadora em JavaScript no frontend com reatividade instantânea. Backend valida a conversão a partir de campos `unit_type`, `box_coverage_m2` e `conversion_factor` ao submeter o item ao carrinho. |

#### RF005 - Gestão administrativa (CRUD) de produtos e SKUs
| RF005 - CRUD Administrativo de produtos | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O painel administrativo deve oferecer controle total de criação, leitura, atualização e inativação de produtos, controlando dados cadastrais, múltiplos SKUs com variações (ex: cor, acabamento, amperagem), tabela de preços e metadados de SEO. |
| **Detalhes da implementação prevista:** | Telas no painel administrativo com controle de acesso baseado em papéis (RBAC). Ações RESTful com validação via DTOs no Slim Framework e persistência transacional no banco de dados. |

#### RF006 - Gestão automática e baixa de inventário em tempo real
| RF006 - Gestão automática de inventário | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O sistema deve garantir integridade e consistência estrita no saldo de estoque. A reserva é realizada durante o checkout e a baixa definitiva ocorre de forma síncrona/realtime logo após a confirmação do pagamento, bloqueando imediatamente a venda de itens esgotados ([RN005](#rn005---controle-rigoroso-de-estoque-em-tempo-real)). |
| **Detalhes da implementação prevista:** | Controle de concorrência com bloqueio pessimista (*SELECT FOR UPDATE*) ou transação atômica em Redis/MySQL durante a finalização do pedido. Disparo de eventos de inventário para a fila de sincronização com ERP ([RNF006](#rnf006---sincronização-com-erp-externo)). |

---

### 4.2. Módulo de Compras & Jornada do Cliente

#### RF007 - Criação e comercialização de kits/combos promocionais
| RF007 - Criação de kits/combos | Prioridade: Baixa |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Possibilidade de agrupar produtos correlatos em pacotes fechados (ex: Combo Pintura: Balde de Tinta + 2 Trinchas + Rolo + Lixas) com aplicação de desconto comercial atrativo ([RN004](#rn004---kits-e-combos-de-produtos)). A disponibilidade do kit é condicionada à existência de saldo de estoque individual de todos os seus componentes ([RN005](#rn005---controle-rigoroso-de-estoque-em-tempo-real)). |
| **Detalhes da implementação prevista:** | Entidade `bundles` associada a múltiplos `product_skus` com multiplicadores de quantidade e desconto percentual configurado no painel de administração. |

#### RF008 - Sugestão de produtos correlatos (Cross-selling)
| RF008 - Sugestão de correlatos | Prioridade: Baixa |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O sistema deve exibir recomendações dinâmicas de itens complementares durante a navegação na página do produto e no carrinho (ex: sugerir espaçadores e argamassa colante na compra de pisos). |
| **Detalhes da implementação prevista:** | Mecanismo de correlação por regras de categoria e histórico de compras conjuntas persistido em tabela de relacionamentos e cacheado em Redis para acesso em sub-milissegundos. |

#### RF009 - Carrinho de compras interativo e persistente
| RF009 - Carrinho de compras | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O cliente pode adicionar, remover e modificar quantidades de produtos, com atualização instantânea dos totais, cálculo de impostos, aplicação de cupons e estimativa de frete. O carrinho deve persistir tanto para usuários não autenticados (armazenamento temporário em sessão/cookies) quanto autenticados (sincronização no banco). |
| **Detalhes da implementação prevista:** | Gestão de estado com Session Handler integrado ao Redis. Ao autenticar, os itens do carrinho anônimo sofrem *merge* com o carrinho do usuário logado no banco de dados. |

#### RF010 - Cálculo de frete dinâmico por peso, cubagem e CEP
| RF010 - Cálculo de frete dinâmico | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O sistema deve calcular automaticamente o valor e prazo do frete com base no CEP do destinatário e nas dimensões/peso de todos os itens do carrinho ([RN002](#rn002---peso-e-dimensões-obrigatórios-para-cubagem)), direcionando pacotes convencionais aos Correios e cargas pesadas/volumosas a transportadoras especializadas ([RN007](#rn007---múltiplas-opções-de-frete-por-cubagem)), além de aplicar regras de frete grátis por região ([RN008](#rn008---frete-grátis-condicionado-e-retirada-na-loja)). |
| **Detalhes da implementação prevista:** | Integração via API REST com gateways de frete (Melhor Envio / Frenet / Tabela Própria). Algoritmo de empacotamento 3D (*Bin Packing Problem*) para consolidar cubagem total dos volumes. |

#### RF011 - Mecanismo de busca indexada e filtros avançados
| RF011 - Busca + filtros eficientes | Prioridade: Média |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Permitir busca por texto livre (nome, marca, código de barras, SKU) com tolerância a erros tipográficos, além de filtros facetados por faixa de preço, voltagem, fabricante, cor e disponibilidade em estoque ([RNF001](#rnf001---interface-intuitiva)). |
| **Detalhes da implementação prevista:** | Índices Full-Text no MySQL com consultas otimizadas ou indexação em Elasticsearch/Meilisearch. Filtros no frontend via AJAX sem recarregar a página inteira. |

#### RF012 - Página de detalhes do produto (PDP) dedicada
| RF012 - Página de Detalhes do Produto (PDP) | Prioridade: Média |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Exibição de todas as informações relevantes do produto: fotos em alta definição, seletor de voltagem/cor, calculadora de frete rápida, tabela de parcelamento, ficha técnica e selos de garantia. |
| **Detalhes da implementação prevista:** | Template Twig modularizado (`product/detail.twig`), galeria com biblioteca de zoom em JavaScript puro, microdados estruturados Schema.org (*Product*, *Offer*) para SEO. |

#### RF013 - Sistema de avaliações, classificação e comentários
| RF013 - Avaliações e Reviews | Prioridade: Baixa |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Permitir que compradores verificados avaliem os itens adquiridos através de pontuação por estrelas (1 a 5), comentários textuais e envio de fotos reais do material aplicado. |
| **Detalhes da implementação prevista:** | Tabela `product_reviews` vinculada a pedidos com status "Entregue". Moderação administrativa prévia para mitigação de spam e ofensas. |

---

### 4.3. Módulo de Usuários & Clientes

#### RF014 - Autenticação segura e cadastro de clientes (PF/PJ)
| RF014 - Autenticação e Cadastro (Sign-up) | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Permitir o cadastro de Pessoa Física (CPF) e Pessoa Jurídica (CNPJ e Inscrição Estadual), validando os documentos na base da Receita/Sintegra. Suporte a autenticação padrão (e-mail e senha com hash forte) e login social OAuth2 (Google/Facebook), garantindo conformidade com a LGPD ([RNF003](#rnf003---proteção-de-dados-financeiros-e-pessoais-lgpd-e-criptografia)). |
| **Detalhes da implementação prevista:** | Rotas de cadastro com validação de algoritmo de CPF/CNPJ. Hash de senhas usando Argon2id / bcrypt. Gerenciamento de sessão com JWT/Cookies HTTP-only. |

#### RF015 - Recuperação de credenciais e redefinição de senha
| RF015 - Recuperação de Acesso / Reset de senha | Prioridade: Baixa |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Disponibilizar fluxo seguro de redefinição de credenciais via envio de token criptográfico temporário por e-mail com validade de 30 minutos. |
| **Detalhes da implementação prevista:** | Geração de token único (*sha256* com salt aleatório) persistido com timestamp de expiração na tabela `password_resets`. Envio de e-mail transacional via SMTP autenticado. |

#### RF016 - Histórico e rastreamento de compras pelo cliente
| RF016 - Histórico de pedidos | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Área do cliente ("Meus Pedidos") para visualização detalhada de compras anteriores, download de DANFE/XML de NF-e ([RN012](#rn012---documentação-obrigatória-para-retorno-e-nf-e)), segunda via de boletos, código PIX e rastreamento da entrega ([RF022](#rf022---rastreamento-last-mile-e-atualização-de-status)). |
| **Detalhes da implementação prevista:** | Painel centralizado do cliente com listagem paginada de pedidos, busca por número de pedido e consumo de rotas autenticadas da API. |

#### RF017 - Gestão de múltiplos endereços de entrega (Shiptos)
| RF017 - Múltiplos Endereços (Shiptos) | Prioridade: Baixa |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | O usuário deve poder cadastrar múltiplos locais de entrega (ex: "Casa", "Obra Residencial 1", "Escritório"), selecionando o endereço de destino no momento do checkout. |
| **Detalhes da implementação prevista:** | Tabela `customer_addresses` com relacionamento 1:N para `customers`, incluindo campo identificador (`is_default`) e preenchimento automático de logradouro via consulta de CEP (API ViaCEP). |

---

### 4.4. Módulo de Pagamentos & Faturamento

#### RF018 - Checkout multi-meios (PIX, Cartão, Boleto)
| RF018 - Multi-meios de pagamento | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Oferecer pagamento seguro via PIX (com geração de QR Code dinâmico e código Copia e Cola com expiração de 15 minutos), Cartão de Crédito (com parcelamento em até 12x e aplicação de regras de juros) e Boleto Bancário. O sistema deve aplicar automaticamente desconto percentual configurável para pagamentos à vista via PIX ou dinheiro ([RN016](#rn016---descontos-por-modalidade-de-pagamento-à-vista)). |
| **Detalhes da implementação prevista:** | Checkout transparente com integração direta via SDK/API do gateway parceiro (Mercado Pago / AOn-Premise / Pagar.me / PagBank). Webhook para captura assíncrona de notificações de confirmação de pagamento. |

#### RF019 - Integração com gateway de pagamento seguro
| RF019 - Gateway de Pagamento e Antifraude | Prioridade: Baixa |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Garantir a transmissão segura de dados de cartão de crédito tokenizados, em conformidade com as diretrizes PCI-DSS, realizando análise automatizada de risco antifraude antes da aprovação do pedido. |
| **Detalhes da implementação prevista:** | Utilização de *tokenization* no frontend com JavaScript do gateway, garantindo que números de cartão de crédito jamais passem de forma plana pelos servidores da aplicação ([RNF003](#rnf003---proteção-de-dados-financeiros-e-pessoais-lgpd-e-criptografia)). |

#### RF020 - Faturamento e emissão de Nota Fiscal Eletrônica (NF-e)
| RF020 - Faturamento e NF-e | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Emissão automatizada de NF-e (modelo 55) após a confirmação financeira da compra, transmitindo os dados fiscais à SEFAZ e anexando o arquivo XML e DANFE (PDF) ao pedido do cliente e no histórico ([RN012](#rn012---documentação-obrigatória-para-retorno-e-nf-e)). |
| **Detalhes da implementação prevista:** | Integração via API de mensageria fiscal (Focus NFe / PlugNotas / e-Notas) ou serviço de background worker acionado pelo evento de pagamento aprovado. |

---

### 4.5. Módulo de Logística & Entrega

#### RF021 - Modalidades de entrega e retirada na loja (BOPIS)
| RF021 - Modalidades de entrega | Prioridade: Média |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Oferecer a escolha entre envio via transportadora terceirizada/frota própria e a opção de retirada física presencial na loja (*Buy Online, Pick Up In Store* - BOPIS) sem custos adicionais de frete ([RN008](#rn008---frete-grátis-condicionado-e-retirada-na-loja)). Em caso de retirada presencial, aplica-se a exceção jurídica onde o cliente não usufrui do direito de arrependimento de 7 dias previsto para compras entregues em domicílio ([RN011](#rn011---direito-de-arrependimento-e-exceção-bopis-art-49-cdc)). |
| **Detalhes da implementação prevista:** | Seletor de modalidade de entrega na etapa de transporte do checkout. A seleção de BOPIS marca a flag `is_bopis = true` na ordem e omite cobrança de frete. |

#### RF022 - Rastreamento last-mile e atualização de status
| RF022 - Last-mile tracking | Prioridade: Média |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Rastreamento do ciclo de vida da entrega (Separação -> Faturado -> Em Trânsito -> Entregue). O sistema deve disparar notificações proativas ao cliente por e-mail/WhatsApp em caso de atrasos na rota ([RN013](#rn013---comunicação-proativa-de-atrasos-na-entrega)). |
| **Detalhes da implementação prevista:** | Tabela de eventos de tracking `order_tracking_events` atualizada via webhook de transportadoras ou atualização manual da expedição. Disparo de templates de e-mail transacional. |

---

### 4.6. Módulo de Administração & Governança

#### RF023 - Gestão de catálogo global e precificação segmentada
| RF023 - Gestão de catálogo e precificação | Prioridade: Alta |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Gerenciamento centralizado de preços com suporte a diferenciação entre clientes Varejo/Consumidor Final (CPF) e Atacado/Construtoras (CNPJ) ([RN017](#rn017---diferenciação-de-preço-varejo-b2c-vs-atacado-b2b)), além de aplicação de regras de desconto progressivo por quantidade comprada ([RN015](#rn015---desconto-progressivo-por-volume)) e campanhas sazonais ([RN018](#rn018---campanhas-promocionais-sazonais-e-segmentadas)). |
| **Detalhes da implementação prevista:** | Motor de precificação (*Pricing Engine*) baseado no padrão GoF Strategy, calculando o preço líquido a partir das políticas ativas para o perfil do cliente e volume. |

#### RF024 - Alerta proativo de ruptura de estoque (Stockout)
| RF024 - Alerta Stockout | Prioridade: Baixa |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Monitoramento contínuo dos níveis de estoque. Quando a quantidade de um SKU atinge ou ultrapassa o ponto de pedido/limite mínimo configurado, o sistema emite alerta automático no painel e por e-mail para a equipe de compras ([RN006](#rn006---alerta-proativo-de-baixo-estoque-stockout)). |
| **Detalhes da implementação prevista:** | Trigger de banco de dados ou interceptor no serviço de inventário que insere notificação na tabela `admin_alerts` e despacha evento para worker de notificação. |

#### RF025 - Painel analítico de vendas e relatórios gerenciais
| RF025 - Analytics e Relatórios | Prioridade: Média |
| :--- | :--- |
| **Especificação da Regra de Negócio:** | Dashboard executivo com indicadores de performance (KPIs): faturamento diário/mensal, ticket médio, produtos mais vendidos, margem por categoria, taxa de conversão do checkout e exportação de dados para planilhas (CSV/XLSX). |
| **Detalhes da implementação prevista:** | Views agregadas e consultas analíticas no MySQL com cache de consultas pesadas em Redis, renderizando gráficos interativos via biblioteca Chart.js. |

---

## 5. **Requisitos Não Funcionais**

Os requisitos não funcionais foram agrupados por categorias essenciais da engenharia de software:

```
+-----------------------------------------------------------------------------------+
|                     CATEGORIAS DE REQUISITOS NÃO FUNCIONAIS                       |
+------------------------------------+----------------------------------------------+
| 5.1 Usabilidade e Acessibilidade   | 5.2 Desempenho e Escalabilidade              |
| (RNF001, RNF007)                   | (RNF002, RNF005)                             |
+------------------------------------+----------------------------------------------+
| 5.3 Confiabilidade e Disponibilid. | 5.4 Segurança e Privacidade (LGPD/Apache)    |
| (RNF004)                           | (RNF003, RNF008)                             |
+------------------------------------+----------------------------------------------+
| 5.5 Integração e Interoperabilidade (RNF006)                                      |
+-----------------------------------------------------------------------------------+
```

---

### 5.1. Usabilidade e Acessibilidade

#### RNF001 - Interface intuitiva e ergonomia visual
| RNF001 - Interface intuitiva | Prioridade: Alta |
| :--- | :--- |
| **Descrição:** | O sistema deve proporcionar uma experiência de navegação fluida, intuitiva e ergonômica, facilitando a localização de produtos complexos por meio de hierarquia visual clara, termos padronizados e rotas de compra simplificadas. |
| **Métrica de Aceitação:** | Taxa de sucesso superior a 90% na localização de itens e conclusão do fluxo de compra em testes de usabilidade, com pontuação SUS (*System Usability Scale*) superior a 80 pontos. |
| **Rastreabilidade RF:** | [RF011](#rf011---mecanismo-de-busca-indexada-e-filtros-avançados), [RF012](#rf012---página-de-detalhes-do-produto-pdp-dedicada) |

#### RNF007 - Design responsivo multi-dispositivo
| RNF007 - Design responsivo multi-dispositivo | Prioridade: Alta |
| :--- | :--- |
| **Descrição:** | A interface do usuário deve se adaptar de forma responsiva e consistente em diferentes dispositivos e resoluções de tela (smartphones, tablets, notebooks e desktops), sem perda de funcionalidade ou quebra de componentes visuais. |
| **Métrica de Aceitação:** | Compatibilidade visual e funcional testada em viewports de 320px a 3840px (4K), aderindo às diretrizes de acessibilidade WCAG 2.1 nível AA. |
| **Rastreabilidade RF:** | [RF009](#rf009---carrinho-de-compras-interativo-e-persistente), [RF012](#rf012---página-de-detalhes-do-produto-pdp-dedicada), [RF018](#rf018---checkout-multi-meios-pix-cartão-boleto) |

---

### 5.2. Desempenho e Escalabilidade

#### RNF002 - Velocidade de carregamento de páginas
| RNF002 - Velocidade de carregamento | Prioridade: Alta |
| :--- | :--- |
| **Descrição:** | O e-commerce deve carregar rapidamente todas as páginas públicas (Home, Catálogo, PDP), mesmo com grande densidade de imagens e fichas técnicas detalhadas. |
| **Métrica de Aceitação:** | *Largest Contentful Paint* (LCP) < 2,5 segundos e *First Input Delay* (FID) < 100ms em conexões 4G convencionais (avaliado via Google Core Web Vitals). Uso de cache L1 em Redis e compressão Gzip/Brotli. |
| **Rastreabilidade RF:** | [RF001](#rf001---cadastro-de-produtos-com-fotos-em-alta-resolução), [RF002](#rf002---exibição-de-especificações-técnicas-por-categoria), [RF012](#rf012---página-de-detalhes-do-produto-pdp-dedicada) |

#### RNF005 - Suporte a picos de tráfego e escalabilidade
| RNF005 - Suporte a picos de tráfego | Prioridade: Alta |
| :--- | :--- |
| **Descrição:** | A plataforma deve suportar aumentos substanciais e repentinos de requisições simultâneas durante campanhas promocionais sazonais (ex: Black Friday, Dias das Ferramentas) sem degradação perceptível do tempo de resposta. |
| **Métrica de Aceitação:** | Capacidade de processar até 500 requisições simultâneas por segundo (RPS) com taxa de erro HTTP 5xx inferior a 0,1%, suportado por arquitetura stateless e balanceamento de carga. |
| **Rastreabilidade RF:** | [RF018](#rf018---checkout-multi-meios-pix-cartão-boleto), [RF023](#rf023---gestão-de-catálogo-global-e-precificação-segmentada) |

---

### 5.3. Confiabilidade e Disponibilidade

#### RNF004 - Alta disponibilidade do sistema
| RNF004 - Disponibilidade do sistema | Prioridade: Alta |
| :--- | :--- |
| **Descrição:** | O serviço deve operar de maneira ininterrupta e confiável, assegurando alta taxa de atividade operacional durante todo o ano comercial. |
| **Métrica de Aceitação:** | Índice de disponibilidade (SLA) de 99,9% (tempo de inatividade não programado máximo de 43,8 minutos por mês ou 8,76 horas ao ano), contando com rotinas de backup diárias e failover automatizado. |
| **Rastreabilidade RF:** | Todos os módulos operacionais ([RF001](#rf001---cadastro-de-produtos-com-fotos-em-alta-resolução) a [RF025](#rf025---painel-analítico-de-vendas-e-relatórios-gerenciais)) |

---

### 5.4. Segurança e Privacidade

#### RNF003 - Proteção de dados financeiros e pessoais (LGPD e Criptografia)
| RNF003 - Proteção de dados e segurança | Prioridade: Alta |
| :--- | :--- |
| **Descrição:** | O sistema deve assegurar a integridade e sigilo dos dados cadastrais e transacionais dos clientes, aplicando criptografia de ponta a ponta e atendendo a todas as exigências legais da Lei Geral de Proteção de Dados (LGPD). |
| **Métrica de Aceitação:** | Tráfego 100% criptografado via TLS 1.3, dados sensíveis em repouso protegidos com AES-256, senhas com salt/Argon2id, políticas de anonimização e conformidade com PCI-DSS (sem armazenamento local de CVV/números de cartão). |
| **Rastreabilidade RF:** | [RF014](#rf014---autenticação-segura-e-cadastro-de-clientes-pfpj), [RF018](#rf018---checkout-multi-meios-pix-cartão-boleto), [RF019](#rf019---integração-com-gateway-de-pagamento-seguro) |

#### RNF008 - Proteção de diretórios via servidor (.htaccess / Apache)
| RNF008 - Proteção de diretórios via servidor | Prioridade: Média |
| :--- | :--- |
| **Descrição:** | O servidor web deve impedir terminantemente a listagem de arquivos (*directory browsing*) e o acesso direto via HTTP a pastas internas do código-fonte, templates, migrações e uploads brutos. |
| **Métrica de Aceitação:** | Diretivas `Options -Indexes` configuradas em todos os níveis do Apache/`.htaccess`, garantindo que apenas a pasta pública (`/public`) seja acessível diretamente e respostas HTTP 403 Forbidden para tentativas de varredura. |
| **Rastreabilidade RF:** | Infraestrutura geral e segurança de ativos de mídia ([RF001](#rf001---cadastro-de-produtos-com-fotos-em-alta-resolução), [RF005](#rf005---gestão-administrativa-crud-de-produtos-e-skus)) |

---

### 5.5. Integração e Interoperabilidade

#### RNF006 - Sincronização com ERP externo
| RNF006 - Sincronização com ERP externo | Prioridade: Média |
| :--- | :--- |
| **Descrição:** | O e-commerce deve comunicar-se com sistemas legados de gestão empresarial (ERP) da loja para sincronização bidirecional de estoque físico, pedidos efetuados, dados de faturamento e clientes. |
| **Métrica de Aceitação:** | Latência máxima de sincronização inferior a 3 segundos via Webhooks ou filas orientadas a eventos (RabbitMQ), garantindo consistência eventual resiliente com *retry queue*. |
| **Rastreabilidade RF:** | [RF006](#rf006---gestão-automática-e-baixa-de-inventário-em-tempo-real), [RF016](#rf016---histórico-e-rastreamento-de-compras-pelo-cliente), [RF020](#rf020---faturamento-e-emissão-de-nota-fiscal-eletrônica-nf-e) |

---

## 6. **Regras de Negócio**

As Regras de Negócio (**RN001 a RN018**) definem as políticas operacionais, validações de domínio, preceitos comerciais e exigências legais que regem as decisões automatizadas do sistema.

```
+-----------------------------------------------------------------------------------+
|                             MAPA DAS REGRAS DE NEGÓCIO                            |
+------------------------------------+----------------------------------------------+
| 6.1 Catálogo e Produtos            | 6.2 Inventário e Logística                   |
| RN001: Variação Unidades (m²/cx)   | RN005: Controle Rigoroso Estoque Realtime    |
| RN002: Dimensões/Cubagem Obrigat.  | RN006: Alerta de Baixo Estoque (Stockout)    |
| RN003: Specs Técnicas p/ Categoria | RN007: Múltiplas Opções de Frete por Cubagem |
| RN004: Kits e Combos Comerciais    | RN008: Frete Grátis e Retirada Presencial    |
+------------------------------------+----------------------------------------------+
| 6.3 Clientes, CDC e Atendimento    | 6.4 Precificação e Finanças                  |
| RN009: Política de Devolução Geral | RN015: Desconto Progressivo por Volume       |
| RN010: Trocas de Itens Sensíveis   | RN016: Desconto Meio À Vista (PIX/Dinheiro)  |
| RN011: Direito Arrependimento/CDC  | RN017: Preço Varejo vs. Atacado (CPF/CNPJ)   |
| RN012: Documentação Fiscal (NF-e)  | RN018: Campanhas Promocionais Sazonais       |
| RN013: Notificação Proativa Atraso |                                              |
| RN014: Suporte Técnico Especializ. |                                              |
+------------------------------------+----------------------------------------------+
```

---

### 6.1. Produtos e Catálogo

#### RN001 - Variações de unidades de medida (Venda Fracionada)
- **Identificador:** `RN001`
- **Domínio:** Catálogo de Produtos
- **Descrição da Regra:** Produtos das categorias de pisos, revestimentos, azulejos, cabos e tintas podem ser comercializados em diferentes unidades de medida (`unidade/peça`, `m²`, `caixa`, `metro linear`, `kg`, `litro`). O sistema deve calcular automaticamente a quantidade exata de caixas ou volumes necessários com base na metragem informada pelo cliente, calculando o valor monetário total com base na conversão configurada.
- **Validação / Condição:** A quantidade submetida ao carrinho deve ser convertida para a menor unidade de venda indivisível (ex: não é permitido vender meia caixa de porcelanato).
- **Benefício de Negócio:** Evita erros comuns de subdimensionamento de material em obras e aumenta o ticket médio ao vender embalagens completas.

#### RN002 - Peso e dimensões obrigatórios para cubagem
- **Identificador:** `RN002`
- **Domínio:** Engenharia de Dados de Catálogo
- **Descrição da Regra:** Todo produto ou variação de SKU cadastrado no sistema deve possuir obrigatoriamente os campos de **peso bruto** (kg), **altura** (cm), **largura** (cm) e **comprimento** (cm) devidamente preenchidos.
- **Validação / Condição:** O painel administrativo deve bloquear a publicação de qualquer produto que esteja com peso ou dimensões zerados ou ausentes.
- **Benefício de Negócio:** Garante a exatidão no cálculo do frete por cubagem, evitando prejuízos financeiros com fretes cobrados a menor das transportadoras.

#### RN003 - Especificações técnicas obrigatórias por categoria
- **Identificador:** `RN003`
- **Domínio:** Catálogo e Cadastro Técnico
- **Descrição da Regra:** Determinadas categorias exigem o preenchimento obrigatório de campos técnicos especializados:
  1. *Ferramentas elétricas/máquinas:* Tensão elétrica / voltagem (110V, 220V, Bivolt).
  2. *Tintas, impermeabilizantes e vernizes:* Rendimento estimado (m²/demão), tempo de secagem e instruções de diluição.
  3. *Cimentos e argamassas:* Classe de resistência (ex: CP-II, CP-III, AC-I, AC-III) e tempo de cura.
- **Validação / Condição:** Bloqueio no salvamento do formulário administrativo se o atributo obrigatório da categoria não for informado.
- **Benefício de Negócio:** Reduz expressivamente a taxa de devoluções por incompatibilidade de voltagem ou erro de uso pelo consumidor final.

#### RN004 - Kits e combos de produtos
- **Identificador:** `RN004`
- **Domínio:** Estratégia Comercial
- **Descrição da Regra:** O sistema permite a composição de kits promocionais agregando produtos complementares (ex: *Kit Construção Bruta:* cimento + areia + tijolos; *Kit Pintura:* tinta + rolo + trinchas + selador) aplicando desconto configurável sobre o valor individual dos itens.
- **Validação / Condição:** A disponibilidade do combo na vitrine é condicionada à existência de estoque positivo de cada item componente no inventário físico.
- **Benefício de Negócio:** Fomenta o *Cross-Selling*, aumenta o ticket médio dos pedidos e agiliza a experiência de compra do construtor.

---

### 6.2. Inventário e Logística

#### RN005 - Controle rigoroso de estoque em tempo real
- **Identificador:** `RN005`
- **Domínio:** Gestão de Estoque
- **Descrição da Regra:** Proibição estrita de "venda às cegas" (venda de produtos sem disponibilidade física em estoque). O saldo de estoque disponível deve ser debitado de forma atômica e em tempo real assim que o pagamento for aprovado pelo gateway.
- **Validação / Condição:** Se dois usuários tentarem comprar a última unidade simultaneamente, a transação do segundo deve ser abortada com mensagem explicativa amigável.
- **Benefício de Negócio:** Elimina o descumprimento de prazos de entrega e cancelamentos por falta de mercadoria, preservando a reputação da loja.

#### RN006 - Alerta proativo de baixo estoque (Stockout)
- **Identificador:** `RN006`
- **Domínio:** Gestão de Compras e Suprimentos
- **Descrição da Regra:** O sistema deve comparar continuamente o estoque atual de cada SKU com o seu ponto de pedido (*safety stock*). Ao atingir ou cruzar o limite mínimo, uma notificação de alta prioridade deve ser enviada para a equipe de compras.
- **Validação / Condição:** `Estoque_Atual <= Nível_Mínimo_Configurado`.
- **Benefício de Negócio:** Minimiza a ruptura de produtos de alto giro (curva A) e otimiza o ciclo de reposição com os fornecedores.

#### RN007 - Múltiplas opções de frete por cubagem
- **Identificador:** `RN007`
- **Domínio:** Logística e Despacho
- **Descrição da Regra:** O roteamento de frete deve segregar automaticamente os produtos do carrinho por cubagem e peso:
  - *Itens leves e pequenos (até 30kg e dentro dos limites postais):* Habilitação dos serviços de Correios (PAC / SEDEX) e transportadoras expressas.
  - *Cargas pesadas e volumosas (ex: cimento, telhas, areia, tubulações):* Restrição obrigatória para transportadoras de carga fracionada ou frota pesada dedicada com caçamba/munck.
- **Validação / Condição:** Se o carrinho contiver ao menos 1 item com flag `heavy_cargo = true`, as opções de Correios são desabilitadas automaticamente.
- **Benefício de Negócio:** Impede falhas operacionais na expedição e garante viabilidade técnica no transporte das mercadorias.

#### RN008 - Frete grátis condicionado e retirada na loja
- **Identificador:** `RN008`
- **Domínio:** Logística Comercial
- **Descrição da Regra:** O sistema permite a configuração de regras de frete grátis baseadas em valor mínimo de pedido e região geográfica (faixas de CEP do município ou estado). Como alternativa universal, a opção de Retirada na Loja Física (BOPIS) deve ser sempre disponibilizada gratuitamente.
- **Validação / Condição:** Aplicação da isenção de frete após checagem das condições de CEP e subtotal líquido do pedido.
- **Benefício de Negócio:** Atrai clientes locais e incentiva o aumento do volume de compras para atingir a régua de frete gratuito.

---

### 6.3. Clientes, Atendimento e CDC

#### RN009 - Política de devolução padronizada
- **Identificador:** `RN009`
- **Domínio:** Pós-Venda e Atendimento
- **Descrição da Regra:** Todas as solicitações de troca e devolução devem seguir fluxo padronizado de abertura de chamado pelo painel do cliente, triagem pela equipe de pós-venda e emissão de autorização de postagem ou agendamento de coleta no endereço do cliente.
- **Validação / Condição:** Solicitações devem ser abertas dentro do prazo de garantia legal (90 dias para vícios aparentes em bens duráveis).
- **Benefício de Negócio:** Organização do fluxo reverso e controle de custos logísticos de reenvio.

#### RN010 - Clareza e critérios em trocas de materiais sensíveis
- **Identificador:** `RN010`
- **Domínio:** Qualidade e Pós-Venda
- **Descrição da Regra:** Produtos sensíveis e customizados possuem regras restritivas de troca:
  - *Tintas manipuladas em máquina tintométrica (cores personalizadas):* Não são passíveis de troca por insatisfação de tom, salvo vício químico comprovado de fabricação.
  - *Pisos e revestimentos cerâmicos:* Apenas são aceitas trocas ou complementações quando comprovada a disponibilidade do mesmo lote e tonalidade (*bitola/tom*), alertando o cliente previamente sobre possíveis variações de nuances entre lotes distintos.
- **Validação / Condição:** Exibição de aviso com *checkbox* de ciência obrigatória na página do produto antes da adição ao carrinho.
- **Benefício de Negócio:** Protege a loja contra perdas financeiras com produtos personalizados de difícil revenda.

#### RN011 - Direito de arrependimento e exceção BOPIS (Art. 49 CDC)
- **Identificador:** `RN011`
- **Domínio:** Jurídico / Código de Defesa do Consumidor
- **Descrição da Regra:** 
  1. *Compras com entrega em domicílio:* O cliente tem o direito incondicional de solicitar o cancelamento e estorno em até **7 (sete) dias corridos** a contar da data de recebimento (Art. 49 do CDC), desde que o produto seja devolvido em sua embalagem original, lacrado, sem indícios de uso ou avaria.
  2. *Compras com retirada física presencial (BOPIS):* **Não se aplica o direito de arrependimento de 7 dias**, uma vez que a retirada presencial no balcão da loja equipara-se à compra presencial, permitindo ao consumidor vistoriar as especificações, estado e integridade física do produto no momento do recebimento.
- **Validação / Condição:** Verificação da data de entrega via tracking e validação do tipo de retirada no ato da solicitação no sistema.
- **Benefício de Negócio:** Total conformidade com a legislação brasileira, mitigando contenciosos judiciais e abusos em devoluções pós-retirada.

#### RN012 - Documentação obrigatória para retorno e NF-e
- **Identificador:** `RN012`
- **Domínio:** Fiscal e Contábil
- **Descrição da Regra:** Nenhum retorno físico de mercadoria ou estorno financeiro pode ser homologado sem a apresentação/validação do respectivo documento fiscal (DANFE da NF-e de venda emitida) e a correspondente emissão de uma NF-e de Entrada por Devolução.
- **Validação / Condição:** O sistema fiscal deve validar a chave de 44 dígitos da NF-e original antes de gerar a nota de devolução.
- **Benefício de Negócio:** Regularidade fiscal perante a Receita Federal e secretarias de fazenda estaduais (SEFAZ), garantindo o creditamento correto dos impostos (ICMS, PIS, COFINS).

#### RN013 - Comunicação proativa de atrasos na entrega
- **Identificador:** `RN013`
- **Domínio:** Experiência do Cliente (CX)
- **Descrição da Regra:** Sempre que uma transportadora registrar intercorrência na rota (ex: sinistro, retenção fiscal em posto, intempéries) ou o prazo previsto de entrega for extrapolado, o sistema deve disparar alerta automatizado via e-mail e SMS/WhatsApp para o cliente com uma nova data estimada.
- **Validação / Condição:** Evento de exceção capturado via webhook de rastreamento logístico.
- **Benefício de Negócio:** Reduz a ansiedade do cliente, diminui a abertura de chamados no SAC e melhora a credibilidade da marca.

#### RN014 - Suporte pós-venda técnico especializado
- **Identificador:** `RN014`
- **Domínio:** Atendimento ao Cliente
- **Descrição da Regra:** A plataforma deve fornecer canais acessíveis (chat online e central telefônica/WhatsApp) com direcionamento para atendentes capacitados tecnicamente para orientar o cliente sobre dosagens, tempos de secagem e métodos de aplicação dos materiais.
- **Validação / Condição:** Roteamento do chamado para fila técnica especializada conforme a categoria do produto comprado.
- **Benefício de Negócio:** Fidelização do consumidor e redução drástica de aplicações incorretas e reclamações indevidas de garantia.

---

### 6.4. Precificação e Condições Comerciais

#### RN015 - Desconto progressivo por volume
- **Identificador:** `RN015`
- **Domínio:** Precificação Comercial
- **Descrição da Regra:** O sistema deve aplicar descontos percentuais automáticos no valor unitário de produtos básicos (ex: cimento, areia, brita, tijolos, blocos) de acordo com faixas de quantidade adquirida (ex: 1 a 19 sacos = R$ 35,00/un; 20 a 49 sacos = R$ 32,00/un; 50+ sacos = R$ 29,50/un).
- **Validação / Condição:** Recálculo instantâneo no carrinho e checkout ao alterar a quantidade informada.
- **Benefício de Negócio:** Estimula compras de grande volume para obras completas, tornando a plataforma competitiva perante atacadistas tradicionais.

#### RN016 - Descontos por modalidade de pagamento à vista
- **Identificador:** `RN016`
- **Domínio:** Financeiro e Tesouraria
- **Descrição da Regra:** Concessão de desconto percentual fixo (ex: 5% a 10%) para pedidos liquidados em meios de pagamento à vista e com liquidação instantânea (PIX ou dinheiro/transferência).
- **Validação / Condição:** Aplicação da dedução no momento em que a opção PIX for selecionada na tela de pagamento do checkout.
- **Benefício de Negócio:** Redução dos custos com taxas de intermediação de cartão de crédito e melhora imediata no fluxo de caixa da empresa.

#### RN017 - Diferenciação de preço varejo (B2C) vs. atacado (B2B)
- **Identificador:** `RN017`
- **Domínio:** Estratégia de Precificação
- **Descrição da Regra:** O catálogo deve operar com dupla tabela de preços baseada no perfil de cadastro:
  - *Varejo (B2C):* Clientes cadastrados com CPF pagam os valores padrão de varejo com opções flexíveis de parcelamento.
  - *Atacado (B2B):* Clientes PJ (CNPJ de construtoras, empreiteiras, engenheiros cadastrados) com inscrição estadual ativa têm acesso a tabela com margens diferenciadas mediante volume mínimo de compra.
- **Validação / Condição:** Alternância automática de preços nas vitrines e no carrinho mediante autenticação do usuário B2B.
- **Benefício de Negócio:** Permite atender tanto o consumidor residencial que faz pequenas reformas quanto grandes construtoras no mesmo canal On-Premise.

#### RN018 - Campanhas promocionais sazonais e segmentadas
- **Identificador:** `RN018`
- **Domínio:** Marketing e Promoções
- **Descrição da Regra:** Capacidade de programar regras de desconto com vigência temporal (data/hora de início e fim) aplicadas a departamentos específicos (ex: "Semana dos Pisos e Porcelanatos", "Mês das Ferramentas", "Festival de Tintas").
- **Validação / Condição:** O desconto expira automaticamente pelo cronograma sem necessidade de intervenção manual da equipe.
- **Benefício de Negócio:** Agilidade operacional na execução do calendário de marketing e promoções do varejo.

---

## 7. **Matrizes de Relacionamento (Rastreabilidade)**

Para garantir a consistência formal e o rigor de engenharia de software, a rastreabilidade bidirecional é estabelecida entre requisitos e regras de negócio.

```
       +-----------------------------------------------------------------------+
       |             ENGENHARIA DE RASTREABILIDADE (MATRIZ BIDIRECIONAL)       |
       +-----------------------------------+-----------------------------------+
       |     Requisitos Funcionais (RF)    |<=======>|     Regras de Negócio (RN)      |
       +-----------------------------------+-----------------------------------+
       |     Requisitos Não Func. (RNF)    |<=======>|     Requisitos Funcionais (RF)  |
       +-----------------------------------+-----------------------------------+
```

---

### 7.1. Matriz Requisitos Funcionais (RF) x Regras de Negócio (RN)

A tabela abaixo valida que todos os requisitos funcionais com lógica de domínio implementam suas respectivas regras de negócio, e que nenhuma regra permanece órfã:

| ID Requisito Funcional | Nome do Requisito | Regras de Negócio Associadas | Impacto / Finalidade |
| :--- | :--- | :--- | :--- |
| **RF001** | Cadastro de produtos + fotos | [RN003](#rn003---especificações-técnicas-obrigatórias-por-categoria) | Qualidade visual e clareza de especificações. |
| **RF002** | Exibição de especificações técnicas | [RN002](#rn002---peso-e-dimensões-obrigatórios-para-cubagem), [RN003](#rn003---especificações-técnicas-obrigatórias-por-categoria) | Dados técnicos obrigatórios por categoria e cubagem. |
| **RF004** | Venda fracionada / unidades de medida | [RN001](#rn001---variações-de-unidades-de-medida-venda-fracionada) | Conversão de m², caixas, kg e litros no cálculo final. |
| **RF006** | Gestão de inventário e saldo | [RN005](#rn005---controle-rigoroso-de-estoque-em-tempo-real) | Baixa atômica síncrona/realtime pós-pagamento. |
| **RF007** | Kits e combos promocionais | [RN004](#rn004---kits-e-combos-de-produtos), [RN005](#rn005---controle-rigoroso-de-estoque-em-tempo-real) | Agrupamento de itens com dependência de estoque individual. |
| **RF010** | Cálculo de frete dinâmico | [RN002](#rn002---peso-e-dimensões-obrigatórios-para-cubagem), [RN007](#rn007---múltiplas-opções-de-frete-por-cubagem), [RN008](#rn008---frete-grátis-condicionado-e-retirada-na-loja) | Cálculo preciso por cubagem, segregação de carga e frete grátis. |
| **RF014** | Autenticação e cadastro (PF/PJ) | [RN017](#rn017---diferenciação-de-preço-varejo-b2c-vs-atacado-b2b) | Validação de CPF (Varejo) e CNPJ com Inscrição Estadual (Atacado). |
| **RF016** | Histórico de pedidos e faturamento | [RN012](#rn012---documentação-obrigatória-para-retorno-e-nf-e) | Disponibilização de DANFE/XML da NF-e para o cliente. |
| **RF018** | Multi-meios de pagamento | [RN016](#rn016---descontos-por-modalidade-de-pagamento-à-vista) | Bonificação/desconto configurável no pagamento via PIX. |
| **RF020** | Faturamento e emissão de NF-e | [RN012](#rn012---documentação-obrigatória-para-retorno-e-nf-e) | Cumprimento fiscal obrigatório para vendas e devoluções. |
| **RF021** | Modalidades de entrega / BOPIS | [RN008](#rn008---frete-grátis-condicionado-e-retirada-na-loja), [RN011](#rn011---direito-de-arrependimento-e-exceção-bopis-art-49-cdc) | Opção BOPIS sem frete e aplicação da exceção de 7 dias do CDC. |
| **RF022** | Rastreamento e status de entrega | [RN013](#rn013---comunicação-proativa-de-atrasos-na-entrega) | Notificação pró-ativa ao cliente em casos de atraso na rota. |
| **RF023** | Gestão de catálogo e precificação | [RN015](#rn015---desconto-progressivo-por-volume), [RN017](#rn017---diferenciação-de-preço-varejo-b2c-vs-atacado-b2b), [RN018](#rn018---campanhas-promocionais-sazonais-e-segmentadas) | Motor de precificação: volume, segmento B2B e campanhas sazonais. |
| **RF024** | Alerta de ruptura de estoque | [RN006](#rn006---alerta-proativo-de-baixo-estoque-stockout) | Disparo de alertas quando o saldo cruza o limite mínimo de segurança. |

---

### 7.2. Matriz Requisitos Não Funcionais (RNF) x Requisitos Funcionais (RF)

Mapeamento demonstrando como as restrições e qualidades técnicas do sistema cobrem os módulos funcionais:

| ID RNF | Categoria / Nome do RNF | Requisitos Funcionais Suportados | Diretriz Técnica de Implementação |
| :--- | :--- | :--- | :--- |
| **RNF001** | Usabilidade / Interface Intuitiva | [RF011](#rf011---mecanismo-de-busca-indexada-e-filtros-avançados), [RF012](#rf012---página-de-detalhes-do-produto-pdp-dedicada) | Design ergonômico, busca tolerante a erros e filtros facetados. |
| **RNF002** | Desempenho / Carregamento Rápido | [RF001](#rf001---cadastro-de-produtos-com-fotos-em-alta-resolução), [RF002](#rf002---exibição-de-especificações-técnicas-por-categoria), [RF012](#rf012---página-de-detalhes-do-produto-pdp-dedicada) | Compressão WebP, LCP < 2.5s, cache de fragmentos em Redis. |
| **RNF003** | Segurança / Proteção de Dados (LGPD) | [RF014](#rf014---autenticação-segura-e-cadastro-de-clientes-pfpj), [RF018](#rf018---checkout-multi-meios-pix-cartão-boleto), [RF019](#rf019---integração-com-gateway-de-pagamento-seguro) | Criptografia TLS 1.3, tokenização PCI-DSS, senhas com Argon2id. |
| **RNF004** | Confiabilidade / Disponibilidade (SLA 99.9%) | Todos ([RF001](#rf001---cadastro-de-produtos-com-fotos-em-alta-resolução) a [RF025](#rf025---painel-analítico-de-vendas-e-relatórios-gerenciais)) | Arquitetura resiliente, failover de banco e rotinas de backup. |
| **RNF005** | Escalabilidade / Picos de Tráfego | [RF018](#rf018---checkout-multi-meios-pix-cartão-boleto), [RF023](#rf023---gestão-de-catálogo-global-e-precificação-segmentada) | Sessões distribuídas em Redis e balanceamento stateless. |
| **RNF006** | Integração / Sincronização ERP | [RF006](#rf006---gestão-automática-e-baixa-de-inventário-em-tempo-real), [RF016](#rf016---histórico-e-rastreamento-de-compras-pelo-cliente), [RF020](#rf020---faturamento-e-emissão-de-nota-fiscal-eletrônica-nf-e) | Webhooks em tempo real e filas assíncronas RabbitMQ. |
| **RNF007** | Acessibilidade / Design Responsivo | [RF009](#rf009---carrinho-de-compras-interativo-e-persistente), [RF012](#rf012---página-de-detalhes-do-produto-pdp-dedicada), [RF018](#rf018---checkout-multi-meios-pix-cartão-boleto) | Adaptação responsiva multi-dispositivo de 320px a 3840px. |
| **RNF008** | Segurança / Proteção via Servidor | [RF001](#rf001---cadastro-de-produtos-com-fotos-em-alta-resolução), [RF005](#rf005---gestão-administrativa-crud-de-produtos-e-skus) | Diretiva `Options -Indexes` em `.htaccess` para proteção de pastas. |

---
