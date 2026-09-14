# Plano de Implementação: Apresentação Acadêmica Web Interativa (FATEC-FV - LES)

Desenvolvimento de uma plataforma web de apresentação acadêmica e documentação técnica navegável em `apresentação_HTML/`, compilando e enriquecendo todo o acervo de Engenharia de Software do projeto **Alpha Engine** (E-commerce On-Premise & Ponto de Venda para Materiais de Construção) desenvolvido para a disciplina de **Laboratório de Engenharia de Software** da **Faculdade de Tecnologia de Ferraz de Vasconcelos (FATEC-FV / Centro Paula Souza)**.

---

## 1. Visão Geral e Objetivos do Projeto

A apresentação será concebida com um padrão visual e funcional de excelência técnica, atendendo com rigor metodológico a dois públicos e cenários de uso complementares:

1. **Modo Apresentação (Slides / Pitch para Banca Avaliadora):**
   - Estrutura visual em seções/slides de alto impacto, cartões síntese com métricas, badges conceituais e diagramas com foco executivo.
   - Navegação facilitada por atalhos de teclado (`←` e `→` para navegar, `Espaço`, `F` para tela cheia).
2. **Modo Dossiê Acadêmico (Leitura Integral e Avaliação da Professora):**
   - Acesso ao conteúdo integral e formal de cada documento de engenharia de software (Visão, Processos/Atividades, Requisitos RF/RNF/RN, Casos de Uso, Matriz de Rastreabilidade, DER/EER e 14 Diagramas de Sequência).
   - Formatação acadêmica rigorosa, tabelas completas e notas de rodapé técnicas.

### Principais Inovações Interativas Previstas:
- **Visualizador Modal de Diagramas SVG com Pan & Zoom:** Ferramenta interativa nativa em JavaScript puro para permitir zoom in/out, arrasto (pan) e visualização em alta fidelidade dos diagramas complexos (como o EER Diagram de 303KB e os 14 diagramas de sequência).
- **Matriz de Rastreabilidade Dinâmica (RN × RF):** Tabela cruzada interativa com busca, filtros por módulo e iluminação bidirecional (ao clicar em uma RN ou RF, os nós relacionados são destacados e seus detalhes exibidos).
- **Design System Institucional com Tema Claro / Escuro:** Paleta refinada com as cores da FATEC e do Centro Paula Souza (vermelho carmesim `#b20000`, azul profundo, grafite e modo escuro sofisticado com glassmorphism).
- **Autonomia e Portabilidade Total:** O diretório `apresentação_HTML/` será autossuficiente (assets, logos e diagramas SVG inclusos localmente), funcionando perfeitamente tanto via servidor HTTP (`/var/www/html/...`) quanto abrindo o `index.html` diretamente em qualquer navegador.

---

## 2. User Review Required

> [!IMPORTANT]
> **Estrutura Autossuficiente vs. Links Externos:**
> Propomos copiar os diagramas SVG de `docs/` e os logos de `apresentação_laboratorio-engenharia_de_software/logo/` para subpastas em `apresentação_HTML/assets/`. Isso garante que a pasta da apresentação possa ser compactada em ZIP, movida para pendrive ou aberta em qualquer máquina sem quebrar caminhos relativos de imagens.

> [!NOTE]
> **Correção de Inconsistências nos Documentos Originais:**
> Identificamos referências quebradas nos arquivos markdown originais (ex.: caminhos apontando para `/docs/documentos_para_a_faculdade/...` que foi renomeado, e links com aspas extras `svg"`). Propomos corrigir essas referências nos markdowns de origem para manter a integridade documental do repositório.

---

## 3. Estrutura de Arquitetura Proposta para `apresentação_HTML/`

```
apresentação_HTML/
├── index.html                     # Capa acadêmica, apresentação do projeto, sumário executivo e portal de acesso
├── 01_visao.html                  # Documento de Visão: Objetivos, Escopo, Dores do Varejo, Peculiaridades Tributárias e Stack
├── 02_processos_atividades.html   # Atividades do Negócio e Técnicas (10 Atividades completas com diagramas SVG)
├── 03_requisitos_regras.html      # Engenharia de Requisitos: 25 RFs, 8 RNFs e 18 RNs com filtros interativos
├── 04_casos_de_uso.html           # Atores do Sistema, 3 Diagramas Gerais e 10 Casos de Uso detalhados
├── 05_matriz_rastreabilidade.html # Matriz Cruzada Interativa RN x RF com auditoria de nós órfãos
├── 06_arquitetura_dados.html      # C4 Component Architecture, DER/EER Diagram, Grafo de Persistência e Padrões GoF
├── 07_diagramas_sequencia.html    # Galeria interativa dos 14 Diagramas de Sequência técnicos
├── 08_checklist_disciplina.html   # Checklist formal de conformidade com os requisitos da disciplina FATEC-FV
└── assets/
    ├── css/
    │   ├── style.css              # Design System, variáveis CSS, temas Dark/Light, tipografia, grid, animações
    │   └── print.css              # Estilos otimizados para impressão e exportação em PDF de dossiê
    ├── js/
    │   ├── main.js                # Orquestrador de UI: alternador de tema, modo apresentação, menu mobile, atalhos
    │   ├── svg-viewer.js          # Módulo nativo de Pan & Zoom com suporte a mouse wheel, drag e fullscreen
    │   └── matrix-filter.js       # Filtros reativos e interatividade da matriz de rastreabilidade
    └── img/
        ├── logos/                 # Fatec_logo.svg, logo-cps-2022.svg, sp.jpg
        └── diagrams/              # Cópia dos diagramas SVG essenciais de docs/
```

---

## 4. Detalhamento das Páginas e Conteúdo

### 4.1. `index.html` - Capa e Portal Executivo
- **Cabeçalho Institucional:** Logos oficiais alinhados da FATEC, Centro Paula Souza e Governo de SP.
- **Identificação Acadêmica:** Aluno (Josias da Conceição Sobrinho), Orientadora (Profa. Patrícia Sarno), Instituição (FATEC-FV / Centro Paula Souza), Curso (Análise e Desenvolvimento de Sistemas), Disciplina (Laboratório de Engenharia de Software).
- **Tema do Projeto:** *Alpha Engine: Plataforma E-commerce On-Premise & Ponto de Venda para Materiais de Construção*.
- **Sumário Executivo Interativo:** Cards dinâmicos para cada um dos 8 módulos com indicadores de progresso, quantidade de requisitos e atalhos rápidos.
- **Destaques do Projeto:** Cards com badges conceituais (Clean Architecture, GoF Patterns, PSR-4/7/11/15, Localização Fiscal Brasil ICMS/NCM/DIFAL/NFC-e, Concorrência com Optimistic Locking).

### 4.2. `01_visao.html` - Documento de Visão
- **Fundamentação & Objetivos:** Objetivo Geral e Objetivos Específicos (desacoplamento GoF, tradução sintática snake_case/PascalCase/camelCase, validação no segmento de materiais de construção, compliance fiscal).
- **Necessidade de Negócio:** Análise detalhada das dores do setor (venda fracionada m², cubagem complexa, limitações de horário, orçamentação morosa).
- **Localização Fiscal e Logística Brasileira:** ICMS-ST, DIFAL, NCM, Inscrição Estadual, CEP e validação de endereços.
- **Especificações Técnicas e Padrões GoF:** Clean ADR com Slim 4, Strategy para Frete e Impostos, Factory/DAO, Observer para eventos com RabbitMQ, PSR-11 (PHP-DI) e PSR-15 (Middlewares).

### 4.3. `02_processos_atividades.html` - Processos & Atividades do Negócio
- Organização em duas trilhas com tabs ou seletor de fluxo:
  1. **Visão Comercial e Operacional (Atividades 1 a 6):**
     - Venda POS com Tratamento de Rejeição de Pagamento;
     - Atendimento Presencial no Balcão e Modalidades de Entrega;
     - Fluxo de Compra e Árvore de Decisão do Checkout E-Commerce;
     - Ciclo de Vida e Processamento de Devolução (RMA / CDC);
     - Gestão de Inventário e Baixa de Estoque Concorrente;
     - Governança de Dados, Sanitização e Direito ao Esquecimento (LGPD).
  2. **Visão Técnica e Arquitetura (Atividades 7 a 10):**
     - Autenticação Dual com Cache Redis e Fallback Gracioso para Sessão PHP;
     - Controle de Idempotência e Proteção de Filas Assíncronas;
     - Tratamento de Falhas de Concorrência com Bloqueio Otimista;
     - Esteira de Segurança HTTP e Middlewares Slim 4 (Anti-CSRF, Rate Limit, RBAC).
- Cada atividade contará com o diagrama SVG incorporado, acoplado ao visualizador com Pan & Zoom, além de tabela descritiva de atores, pré-condições, passos e pós-condições.

### 4.4. `03_requisitos_regras.html` - Engenharia de Requisitos
- **Barra de Ferramentas Interativa:** Busca em tempo real e filtros por módulo (Produtos, Compras, Clientes, Pagamentos, Logística, Administração) e por prioridade (Alta, Média, Baixa).
- **Requisitos Funcionais (RF001 a RF025):** Exibição em cards com tabela técnica, regra de negócio vinculada e detalhes de implementação.
- **Requisitos Não Funcionais (RNF001 a RNF008):** Categorias de Usabilidade, Desempenho, Confiabilidade, Segurança e Interoperabilidade com critérios de aceitação mensuráveis.
- **Regras de Negócio (RN001 a RN018):** Cartões detalhando restrições fiscais, cubagem, cálculo fracionado, política de cancelamento CDC e limites de concorrência.

### 4.5. `04_casos_de_uso.html` - Casos de Uso do Sistema
- **Matriz de Atores:** Perfil, privilégios e escopo dos 7 atores (Visitante, Cliente PF/PJ, Vendedor POS, Caixa POS, Operador do Painel, Administrador, Gateways Externos).
- **3 Diagramas Gerais de Casos de Uso com Zoom Interativo:**
  - Loja Virtual (Cliente);
  - Ponto de Venda (POS - Vendedor e Caixa);
  - Painel Administrativo (Dashboard On-Premise).
- **10 Especificações Modulares Completas:** Casos de uso core com atores, pré-condições, fluxo principal passo a passo, fluxos alternativos, fluxos de exceção e pós-condições.

### 4.6. `05_matriz_rastreabilidade.html` - Matriz de Rastreabilidade (RN × RF)
- **Fundamentação Teórica:** O papel da rastreabilidade bidirecional na governança de software.
- **Grid Interativo Completo:** Tabela cruzada entre as 18 Regras de Negócio e os 25 Requisitos Funcionais:
  - Células ativas com `[X]` clicáveis;
  - Ao passar o cursor ou clicar, destaca a linha e a coluna correspondentes;
  - Painel lateral dinâmico que exibe o resumo imediato da RN e do RF selecionados.
- **Auditoria de Integridade (Check de Nós Órfãos):** Painel visual atestando 0% de regras órfãs e 0% de requisitos sem justificação de negócio (*Zero Gold Plating*).
- **Análise de Impacto de Mudanças:** Mapeamento do impacto de mudanças em legislações (ex.: CDC, ICMS, Reforma Tributária).

### 4.7. `06_arquitetura_dados.html` - Arquitetura de Software & Banco de Dados
- **Diagrama de Componentes C4:** Estrutura em camadas (Slim 4, Middlewares, Actions, Presentation Twig, Domain DDD, Persistence DAO/Mappers, Infrastructure).
- **Diagrama Entidade-Relacionamento (EER / DER Completo):** Exibição em alta resolução do diagrama de banco de dados (`EER_Diagram.svg`) com Pan & Zoom suave.
- **Grafo de Persistência do Pedido:** Visualização da agregação do Pedido (`GrafoPersistenciaPedido.svg`) e ciclo de vida de persistência.
- **Catálogo de Padrões GoF Implementados:** Detalhamento arquitetural com exemplos práticos no código (Strategy, Factory, DAO, Observer, Proxy, Template Method).

### 4.8. `07_diagramas_sequencia.html` - Galeria de Diagramas de Sequência
- Galeria interativa dos **14 Diagramas de Sequência** oficiais:
  1. Autenticação Dual Redis com Fallback PHP Session;
  2. Busca Indexada de Produtos;
  3. Calculadora de Revestimento e Pisos Cerâmicos;
  4. Cálculo de Frete com Strategy Pattern;
  5. Devolução de Produtos e Logística Reversa (RMA);
  6. Idempotência Técnica e Proteção de Filas;
  7. Fluxo Completo de Criação e Leitura de Pedidos;
  8. Fluxo de Venda no Ponto de Venda (PDV);
  9. Lazy Loading via ProxyFactory no DAO;
  10. Concorrência e Tratamento de Lock Otimista;
  11. Fusão de Carrinho Anônimo no Login;
  12. Pipeline de Middlewares Anti-CSRF;
  13. Resolução e Cache de URLs Amigáveis (SEO);
  14. Webhook de Pagamento com GoF Adapter e Observers.
- Cada item conta com o diagrama SVG interativo em modal de alta resolução, contextualização do desafio técnico e mapeamento dos componentes envolvidos.

### 4.9. `08_checklist_disciplina.html` - Checklist de Conformidade da Disciplina
- Checklist interativo dos itens requeridos pela ementa de Laboratório de Engenharia de Software da FATEC-FV:
  - Documento de Visão;
  - Diagramas de Atividades do Negócio;
  - Requisitos Funcionais, Não-Funcionais e Regras de Negócio;
  - Matriz de Relacionamento RN x RF;
  - Diagrama de Entidade e Relacionamento (DER/EER);
  - Diagramas de Casos de Uso e Especificações;
  - Diagramas de Sequência Técnicos;
  - Grafo de Entidades e Arquitetura.
- Cada item traz status de conformidade ("Concluído / Atendido"), links diretos para a página correspondente e justificativa técnica.

---

## 5. Proposed Changes

### Componente: Recursos e Assets Compartilhados

#### [NEW] `apresentação_HTML/assets/css/style.css`
- Sistema de variáveis CSS (tokens de cor para temas claro e escuro, tipografia, espaçamentos, elevação e sombras).
- Layout responsivo com sidebar retrátil, barra superior de navegação e barra de progresso.
- Estilização de cards em glassmorphism, tabelas técnicas, badges de prioridade, abas interativas e botões de ação.
- Estilos para visualizador modal de SVG (controles de zoom in/out, reset, pan, arrasto).

#### [NEW] `apresentação_HTML/assets/css/print.css`
- Regras `@media print` para formatação de página A4, quebra de páginas controlada, ocultação de menus e exibição limpa para geração de PDF/impressão.

#### [NEW] `apresentação_HTML/assets/js/main.js`
- Alternância de temas (Claro / Escuro) com persistência em `localStorage`.
- Modo Apresentação vs. Modo Dossiê com ajuste de tamanho de fonte e densidade de leitura.
- Barra de progresso de leitura/apresentação no topo.
- Controle de atalhos de teclado (`←`, `→`, `F`, `T`, `Esc`).
- Menu responsivo (drawer para mobile/telas compactas).

#### [NEW] `apresentação_HTML/assets/js/svg-viewer.js`
- Mecanismo de manipulação de SVGs integrado: Pan (arrasto do mouse/toque), Zoom (roda do mouse e botões +/-), Reset de escala e modo tela cheia.

#### [NEW] `apresentação_HTML/assets/js/matrix-filter.js`
- Lógica de interação da Matriz RN × RF: filtro por texto, realce de células e painel dinâmico de detalhes.

#### [NEW] Cópia dos Logos e Diagramas SVG para `apresentação_HTML/assets/`
- Copiar logos oficiais de `apresentação_laboratorio-engenharia_de_software/logo/` para `apresentação_HTML/assets/img/logos/`.
- Copiar os SVGs de diagramas de `docs/` para `apresentação_HTML/assets/img/diagrams/`.

---

### Componente: Páginas HTML da Apresentação

#### [NEW] [index.html](/apresenta%C3%A7%C3%A3o_HTML/index.html)
- Capa acadêmica institucional completa, dados da FATEC-FV, orientadora, aluno, resumo executivo e índice navegável.

#### [NEW] [01_visao.html](/apresenta%C3%A7%C3%A3o_HTML/01_visao.html)
- Documento de Visão, objetivos, dores do mercado, especificidades brasileiras e arquitetura geral.

#### [NEW] [02_processos_atividades.html](/apresenta%C3%A7%C3%A3o_HTML/02_processos_atividades.html)
- 10 Atividades do Negócio e Técnicas com diagramas SVG integrados e visualizador com zoom.

#### [NEW] [03_requisitos_regras.html](/apresenta%C3%A7%C3%A3o_HTML/03_requisitos_regras.html)
- 25 Requisitos Funcionais, 8 Não Funcionais e 18 Regras de Negócio com filtros dinâmicos.

#### [NEW] [04_casos_de_uso.html](/apresenta%C3%A7%C3%A3o_HTML/04_casos_de_uso.html)
- Atores, 3 diagramas gerais de casos de uso e especificações detalhadas dos 10 casos de uso principais.

#### [NEW] [05_matriz_rastreabilidade.html](/apresenta%C3%A7%C3%A3o_HTML/05_matriz_rastreabilidade.html)
- Matriz interativa RN × RF, auditoria de integridade (zero órfãos) e análise de impacto.

#### [NEW] [06_arquitetura_dados.html](/apresenta%C3%A7%C3%A3o_HTML/06_arquitetura_dados.html)
- Diagrama C4, DER/EER Diagram de banco de dados, grafo de entidades e padrões GoF.

#### [NEW] [07_diagramas_sequencia.html](/apresenta%C3%A7%C3%A3o_HTML/07_diagramas_sequencia.html)
- Galeria dos 14 diagramas de sequência comentados com abertura em modal de zoom.

#### [NEW] [08_checklist_disciplina.html](/apresenta%C3%A7%C3%A3o_HTML/08_checklist_disciplina.html)
- Checklist interativo dos critérios da disciplina de Laboratório de Engenharia de Software da FATEC-FV.

---

### Componente: Correções nos Documentos de Origem

#### [MODIFY] [Requisitos_para_a_matéria_de_Laboratório_de_Engenharia_de_Software.md](/apresenta%C3%A7%C3%A3o_laboratorio-engenharia_de_software/Requisitos_para_a_mat%C3%A9ria_de_Laborat%C3%B3rio_de_Engenharia_de_Software.md)
- Corrigir caminhos quebrados que apontavam para `/docs/documentos_para_a_faculdade/...` direcionando para os arquivos correspondentes na pasta atual.
- Corrigir aspas residuais em links de imagem (`.svg"`).

#### [MODIFY] [4. Casos de Uso.doc.md](/apresenta%C3%A7%C3%A3o_laboratorio-engenharia_de_software/4.%20Casos%20de%20Uso.doc.md)
- Corrigir links cruzados internos que referenciavam a pasta legada `/docs/documentos_para_a_faculdade/`.

---

## 6. Plano de Verificação e Validação

### Testes Visuais e de Navegação
- **Validação com o Navegador (`browser_subagent`):**
  - Abrir `index.html` e todas as 8 páginas secundárias no navegador.
  - Testar a alternância entre temas (Dark Mode e Light Mode) e verificar contraste, legibilidade e harmonia de cores.
  - Testar o atalho de navegação por teclado (`←`, `→`, `F`).
  - Testar o visualizador de SVG: clicar em um diagrama complexo (ex: `EER_Diagram.svg` e `POS-Sales_and_Checkout_Sequence_Diagram.svg`), aplicar zoom in, zoom out, arrastar com o mouse e fechar modal com `Esc`.
  - Testar os filtros dinâmicos na página de Requisitos e na Matriz de Rastreabilidade.
  - Verificar a integridade e carregamento de todos os logos e diagramas sem nenhum erro 404 no console do navegador.

### Validação Estrutural e Semântica
- Verificar conformidade HTML5 semântico (tags `<header>`, `<nav>`, `<main>`, `<article>`, `<section>`, `<footer>`).
- Validar se todos os links relativos entre páginas funcionam bidirecionalmente.
- Garantir que a apresentação funciona tanto servida via HTTP no Apache local quanto acessada diretamente via protocolo `file://`.



# Walkthrough: Apresentação Acadêmica Web Interativa (FATEC-FV - LES)

Criamos uma plataforma web navegável, moderna e interativa em [apresentação_HTML/](/apresenta%C3%A7%C3%A3o_HTML/index.html) para apresentar o trabalho acadêmico da plataforma **Alpha Engine** na disciplina de **Laboratório de Engenharia de Software** da **FATEC Ferraz de Vasconcelos (Centro Paula Souza)**.

---

## 1. O que foi Construído

### 1.1. Estrutura Modular das Páginas HTML

| Página | Título / Conteúdo Principal | Destaques Técnicos & Interativos |
| :--- | :--- | :--- |
| [index.html](/apresenta%C3%A7%C3%A3o_HTML/index.html) | **Capa Acadêmica & Portal Executivo** | Logos institucionais oficiais (FATEC, CPS, SP), dados do aluno e orientadora, métricas de engenharia e hub de navegação. |
| [01_visao.html](/apresenta%C3%A7%C3%A3o_HTML/01_visao.html) | **Documento de Visão** | Objetivos gerais/específicos, dores do varejo de construção civil, localização fiscal brasileira (ICMS-ST, DIFAL, NCM, IE) e Diagrama C4. |
| [02_processos_atividades.html](/apresenta%C3%A7%C3%A3o_HTML/02_processos_atividades.html) | **Processos & Atividades do Negócio** | 10 Atividades comerciais e de infraestrutura com fluxogramas SVG PlantUML integrados ao visualizador interativo. |
| [03_requisitos_regras.html](/apresenta%C3%A7%C3%A3o_HTML/03_requisitos_regras.html) | **Engenharia de Requisitos** | 25 Requisitos Funcionais, 8 Requisitos Não Funcionais e 18 Regras de Negócio com busca em tempo real e filtros de categoria. |
| [04_casos_de_uso.html](/apresenta%C3%A7%C3%A3o_HTML/04_casos_de_uso.html) | **Casos de Uso do Sistema** | 7 Atores mapeados, 3 Diagramas Gerais SVG (Loja, PDV, Painel) e 10 Especificações Modulares Detalhadas com fluxos de exceção. |
| [05_matriz_rastreabilidade.html](/apresenta%C3%A7%C3%A3o_HTML/05_matriz_rastreabilidade.html) | **Matriz de Rastreabilidade** | Grade cruzada 18 RNs × 25 RFs com busca dinâmica, destaque bidirecional, inspetor dinâmico de regras e auditoria de 0 órfãos. |
| [06_arquitetura_dados.html](/apresenta%C3%A7%C3%A3o_HTML/06_arquitetura_dados.html) | **Arquitetura & Banco EER** | Diagrama Entidade-Relacionamento (EER) de 303KB com Pan & Zoom, Grafo de Persistência do Pedido e Catálogo de Padrões GoF/PSR. |
| [07_diagramas_sequencia.html](/apresenta%C3%A7%C3%A3o_HTML/07_diagramas_sequencia.html) | **Diagramas de Sequência Técnicos** | Galeria dos 14 diagramas SVG oficiais comentados e categorizados (Segurança, Concorrência, GoF, Checkout, PDV). |
| [08_checklist_disciplina.html](/apresenta%C3%A7%C3%A3o_HTML/08_checklist_disciplina.html) | **Checklist da Disciplina** | Quadro formal de evidências com status **100% Conforme** para todos os critérios de entrega da FATEC-FV. |

---

### 1.2. Recursos Interativos e Design System

- **Design System Institucional:** Desenvolvido em [assets/css/style.css](/apresenta%C3%A7%C3%A3o_HTML/assets/css/style.css) com tipografia moderna (Google Fonts Outfit & Plus Jakarta Sans), paleta da FATEC (carmesim `#b20000`), cartões em glassmorphism e estilização `@media print` dedicada em [assets/css/print.css](/apresenta%C3%A7%C3%A3o_HTML/assets/css/print.css).
- **Tema Claro / Escuro:** Alternador com persistência no `localStorage` e ícone dinâmico.
- **Duplo Modo de Visualização:**
  - *Modo Apresentação (Slides/Pitch):* Aumenta fontes, expande cards e otimiza layout para apresentação em projetor/banca.
  - *Modo Dossiê Acadêmico:* Visão textual detalhada e densa para leitura e avaliação da professora.
- **Visualizador Modal de Diagramas SVG com Pan & Zoom ([assets/js/svg-viewer.js](/apresenta%C3%A7%C3%A3o_HTML/assets/js/svg-viewer.js)):**
  - Zoom suave via roda do mouse (`wheel`) ou botões `+` e `-`.
  - Arrasto de diagrama com o mouse (Pan).
  - Botão de reset para 100%, botão para download do SVG original e fechamento com tecla `Esc`.
- **Matriz de Rastreabilidade Dinâmica ([assets/js/matrix-filter.js](/apresenta%C3%A7%C3%A3o_HTML/assets/js/matrix-filter.js)):**
  - Filtro em tempo real por palavras-chave.
  - Ao passar o cursor em qualquer célula, o inspetor exibe a descrição da RN e do RF, confirmando o status da rastreabilidade.
- **Atalhos de Teclado Globais ([assets/js/main.js](/apresenta%C3%A7%C3%A3o_HTML/assets/js/main.js)):**
  - `←` e `→`: Navega sequencialmente entre as 9 páginas.
  - `T`: Alterna entre Tema Escuro e Tema Claro.
  - `P`: Alterna entre Modo Apresentação e Modo Dossiê.
  - `F`: Ativa / Desativa Modo Tela Cheia (Fullscreen).
  - `Esc`: Fecha modais e menus.

---

### 1.3. Correção de Inconsistências nos Documentos de Origem

- [Requisitos_para_a_matéria_de_Laboratório_de_Engenharia_de_Software.md](/apresenta%C3%A7%C3%A3o_laboratorio-engenharia_de_software/Requisitos_para_a_mat%C3%A9ria_de_Laborat%C3%B3rio_de_Engenharia_de_Software.md): Corrigidos links quebrados para caminhos locais e removidas aspas residuais nos links SVG (`.svg"`).
- [4. Casos de Uso.doc.md](/apresenta%C3%A7%C3%A3o_laboratorio-engenharia_de_software/4.%20Casos%20de%20Uso.doc.md): Atualizados links internos que referenciavam a pasta legada `/docs/documentos_para_a_faculdade/`.

---

## 2. Validação e Testes Realizados

1. **Validação de Códigos HTTP (Todas as páginas e assets ativos):**
   - `index.html`: **HTTP 200**
   - `01_visao.html`: **HTTP 200**
   - `02_processos_atividades.html`: **HTTP 200**
   - `03_requisitos_regras.html`: **HTTP 200**
   - `04_casos_de_uso.html`: **HTTP 200**
   - `05_matriz_rastreabilidade.html`: **HTTP 200**
   - `06_arquitetura_dados.html`: **HTTP 200**
   - `07_diagramas_sequencia.html`: **HTTP 200**
   - `08_checklist_disciplina.html`: **HTTP 200**
   - Assets de CSS, JS, Logos oficiais e Diagramas SVG: **HTTP 200**
2. **Servidor HTTP Local:**
   - O servidor de desenvolvimento Python está em execução no endereço: `http://localhost:8080/index.html`.
3. **Subagente de Navegador:**
   - A ferramenta `open_browser_url` encontrou um erro de resolução de porta CDP (`failed to resolve CDP URLs: failed to parse CDP port`). Conforme as instruções do sistema, o usuário foi notificado para orientar os próximos passos.

