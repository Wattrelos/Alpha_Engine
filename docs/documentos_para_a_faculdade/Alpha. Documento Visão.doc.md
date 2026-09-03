

# ***Alpha Engine: Plataforma E-commerce On-Premise & Ponto de Venda para Materiais de Construção***

***Documento de Visão***

1. **Índice**

[1\.	Índice	2](#índice)  
[2\.	Objetivo	3](#objetivo)  
[3\.	Necessidade do Negócio	3](#heading)  
[4\.	Descrição do Escopo	3](#heading-1)  
[5\.	Equipe	3](#heading-2)  
[6\.	Especificações Técnicas	3](#heading-3)  
[7\.	Riscos	3](#heading-4)  
[8\.	Cronograma de Marcos Resumido	4](#heading-5)  
[9\.	Orçamento Resumido	4](#heading-6)

2. # **OBJETIVOS**

   2.1. ## **Objetivo Geral**

Desenvolver uma plataforma de comércio eletrônico (*e-commerce*) praticamente do zero e com suporte nativo para o mercado brasileiro, com um motor (*engine*) de *back-end* desacoplado baseado nos padrões de projeto do *Gang of Four* (GoF), além de um *front-end* baseado em telas dinâmicas Twig, com *Java Script,* gerenciamento de sessão com assinaturas de páginas e certificados digitais, utilizando ferramentas de assistência baseadas em Inteligência Artificial para viabilizar a arquitetura e garantir a conformidade (*compliance*) fiscal e logística do modelo de negócios.

   2.2. ## **Objetivos Específicos**

Para alcançar o objetivo geral, estabelecem-se os seguintes objetivos específicos:

* **Desacoplamento.** Utilizando arquiteturas de *e-commerce*, com foco no desacoplamento e dinamismo de consultas SQL de operações CRUD na camada de persistência;  
* **Projetar e implementar um motor de *back-end* agnóstico** utilizando padrões GoF (como *Strategy* e *Factory*), garantindo que as entidades do sistema sejam descobertas e manipuladas exclusivamente em tempo de execução;  
* **Desenvolver uma camada de tradução sintática eficiente** capaz de mediar a comunicação entre o padrão *snake\_case* (padrão em banco de dados) e os padrões *PascalCase* e *camelCase* exigidos pela arquitetura de objetos do *back-end,* além do *kebab case* geralmente utilizados em páginas html;  
* **Avaliar o impacto da utilização do assistentes de códigos baseados em I.A.** no processo de desenvolvimento solo, mensurando sua eficácia na detecção instantânea de inconsistências lógicas e erros de digitação (*typos*) sob restrições de tempo;  
* **Incorporar nativamente os requisitos fiscais e logísticos** obrigatórios do cenário tributário brasileiro (ICMS, NCM, Inscrição Estadual, CPF, CNPJ), e campos de endereço como Bairro e CEP) à lógica de fechamento de pedido *checkout*);  
* **Validar a solução proposta** por meio de um estudo de caso aplicado a uma loja de varejo do segmento de materiais de construção, demonstrando a manutenibilidade do sistema e a eficiência de sua arquitetura independente.


<!-- Descreva aqui o objetivo final do projeto, por exemplo, a criacao de um sistema de software. Qual? -->
Ao final, o objetivo é ter um sistema de e-commerce funcional e escalável, que possa ser utilizado por lojas de materiais de construção de pequeno e médio porte. O sistema deve ser capaz de gerenciar produtos, clientes, pedidos, estoque, pagamentos, entregas, etc. O sistema deve ser capaz de funcionar como um sistema de e-commerce e como um sistema de ponto de venda, com sincronização em tempo real entre as duas modalidades. 

3. **Necessidade do Negócio**

**O Cenário do Comércio Eletrônico no Brasil e as Barreiras de Localização**

3.1 O setor de comércio varejista de materiais de construção historicamente fundamentou suas operações em interações físicas, dependendo fortemente do atendimento em balcão e de canais de comunicação tradicionais, como o telefone. No entanto, o cenário contemporâneo apresenta uma transformação impulsionada pela digitalização dos hábitos de consumo. Constata-se que tanto o consumidor final quanto profissionais da área (engenheiros, arquitetos e empreiteiros) demandam maior agilidade, transparência de preços e conveniência no processo de aquisição de insumos.

Diante desse panorama, a ausência de um canal de vendas digital gera uma série de gargalos operacionais e comerciais para a organização em estudo, os quais justificam a necessidade latente de modernização tecnológica. A implementação de plataformas internacionais de *e-commerce* no mercado nacional frequentemente esbarra na complexidade das obrigações acessórias e na estrutura logística do país. Softwares globais conceituados demandam extensas customizações para mitigar lacunas funcionais que não atendem às especificidades brasileiras. Sob a perspectiva fiscal e operacional, essas peculiaridades dividem-se em dois eixos centrais:

3.1.1 **Limitação Geográfica e de Horário:** As vendas ficam restritas ao horário comercial e ao alcance físico da loja, impedindo a captação de clientes que realizam planejamentos de obras ou compras em horários alternativos.

3.1.1.1 **Ineficiência no Processo de Orçamentação:** O modelo tradicional exige que o cliente solicite orçamentos manualmente. Isso gera sobrecarga na equipe de atendimento e lentidão nas respostas, resultando em perda de vendas para concorrentes mais ágeis.

3.1.1.2 **Complexidade na Gestão de Catálogo e Estoque:** Materiais de construção possuem alta diversidade de SKUs (unidades de manutenção de estoque), variações de peso, volume e restrições de entrega logística. A falta de uma plataforma integrada dificulta a exibição em tempo real da disponibilidade dos produtos.

Portanto, a necessidade do negócio centraliza-se na expansão de sua presença de mercado e na otimização de suas operações por meio de uma plataforma de comércio eletrônico. A opção pelo modelo Software as a Service (On-Premise) justifica-se pela urgência em adotar uma solução robusta, escalável e de rápida implementação, reduzindo a necessidade de investimentos elevados em infraestrutura de TI local e permitindo que a empresa foque em sua atividade-fim: a comercialização e a logística de materiais de construção.

3.1.2. **Peculiaridades Tributárias:**  
3.1.2.1. **Cálculo Automático de ICMS:** Necessidade de processamento do ICMS-ST (Substituição Tributária) e do Diferencial de Alíquota (DIFAL) nas operações interestaduais, dinâmicas que variam conforme o estado de destino;  
3.1.2.2. **Gestão de NCM:** Integração da Nomenclatura Comum do Mercosul (NCM) no catálogo de produtos, métrica indispensável para garantir a correta classificação fiscal e evitar autuações ou taxações incorretas;  
3.1.2.3. **Validação de Inscrição Estadual (IE):** Parametrização nativa para validar a IE de clientes cadastrados como Pessoa Jurídica (PJ), definindo a emissão de notas como isento ou não-contribuinte;  
3.1.2.4. **Emissão de Notas Fiscais (NF-e/NFC-e):** Integração para emissão de documentos fiscais eletrônicos diretamente pelo sistema, exigindo sincronização em tempo real com o controle de estoque.  
  *A engenharia de software aplicada ao comércio eletrônico no cenário brasileiro enfrenta desafios que superam as regras de negócio tradicionais de plataformas internacionais. A localização de um software para o mercado nacional exige o mapeamento nativo de obrigações acessórias fiscais e regras logísticas complexas. Sob a perspectiva tributária, o sistema deve computar de forma assíncrona o Imposto sobre Circulação de Mercadorias e Serviços (ICMS), gerenciando as particularidades da Substituição Tributária (ICMS-ST) e do Diferencial de Alíquota (DIFAL) nas operações interestaduais. Ademais, a classificação fiscal exige a vinculação da Nomenclatura Comum do Mercosul (NCM) ao catálogo de produtos, mitigando riscos de autuações fiscais. Diante do panorama contemporâneo de modernização fiscal, a arquitetura do banco de dados e do motor de persistência deve ser projetada de forma extensível para suportar a transição da Reforma Tributária nacional, prevendo a integração do Imposto sobre Bens e Serviços (IBS) e da Contribuição sobre Bens e Serviços (CBS).*

  3.3. **Logística e Formato de Endereçamento:** Ao contrário de sistemas estrangeiros orientados por *Zip Codes* genéricos, o ecossistema brasileiro exige campos estruturados para o Código de Endereçamento Postal (CEP) no formato 00000-000. Sistemas eficientes demandam rotinas de preenchimento automático (autocompletar) integradas à base dos Correios para determinar os logradouros, bairros, cidades e estados a partir do código, além de campos segregados para número e complemento. Adicionalmente, faz-se necessária a cubagem e cálculo nativo de frete integrando APIs dos Correios (SEDEX/PAC) e transportadoras regionais privadas.

4. **Descrição do Escopo**

Descreva o sistema de software que sera criado – quais subsistemas / modulos irao compor o produto final e as principais funcionalidades inclusas. Faça apenas uma breve descrição, os requisitos serãoo detalhados em outro documento.  
Aproveite essa secao para discriminar tambem todos os itens que estarao fora do escopo, com proposito de gerenciar expectativas do cliente. Ex. Modulos do sistema que nao serao implementados, mas poderao vir a ser, se o cliente optar por contratar um projeto de expansao apos a entrega do software.

5. **Equipe**

<!-- Liste os integrantes da equipe, formacao, experiencia e papeis e responsabilidades no projeto. -->
5.1 ***Integrantes da Equipe:***
- Josias da Conceição Sobrinho
- Gabriel Calidônio André

5.2 ***Colaboradores:***
- Wallace Francis Miranda (Turma ADS 2-20025 - FATEC-FV)


6. **Especificações Técnicas**

A plataforma **Alpha Engine** adota uma arquitetura desacoplada, modular e orientada a padrões de projeto modernos (*Design Patterns*), assegurando alta performance, conformidade estrita com o padrão PSR da comunidade PHP-FIG, facilidade de manutenção e segurança robusta para operações de comércio eletrônico e ponto de venda (PDV).

6.1. **Arquitetura de Software e Padrões de Projeto (GoF & PSR)**
* **Arquitetura em Camadas Desacopladas (Clean / Action-Domain-Responder):** Estrutura orientada a *Single Action Controllers* (invokables) integrados ao Slim Framework 4, eliminando controllers monolíticos e garantindo responsabilidade única.
* **Padrões de Projeto Gang of Four (GoF):**
  * *Strategy Pattern:* Utilizado para algoritmos intercambiáveis de cálculo de frete (Correios SEDEX/PAC, transportadoras privadas e retirada presencial), regras fiscais/tributárias e estratégias de cache (Redis vs. FileCache).
  * *Factory / Abstract Factory:* Instanciação dinâmica de DAOs, repositórios e entidades em tempo de execução, garantindo que o motor de persistência seja agnóstico.
  * *Data Access Object (DAO) & Repository Pattern:* Camada de persistência com tradução sintática automatizada entre convenções de banco de dados (*snake_case*) e a arquitetura de objetos do domínio (*PascalCase* e *camelCase*), com hidratação dinâmica e suporte a *Lazy Loading*.
  * *Observer Pattern (Event-Driven):* Sistema de publicação e assinatura de eventos desacoplados (ex.: fechamento de pedido, baixa de estoque e auditoria), permitindo integração assíncrona com RabbitMQ.
  * *Singleton / Dependency Injection:* Gestão centralizada de instâncias e injeção de dependências compatível com PSR-11 via Container (PHP-DI).
* **Conformidade com PHP-FIG (PSRs):**
  * `PSR-4`: Autoloading padronizado de namespaces e classes.
  * `PSR-7`: Abstração de mensagens e requisições/respostas HTTP.
  * `PSR-11`: Interface padronizada para containers de injeção de dependência.
  * `PSR-15`: Middlewares de requisição HTTP em pipeline (autenticação, CORS, CSRF e Rate Limiting).

6.2. **Stack Tecnológica de Back-end & Persistência**
* **Linguagem:** PHP 8.2+ com tipagem estrita (`declare(strict_types=1);`), *match expressions*, *readonly properties*, atributos nativos e tratamento avançado de exceções.
* **Microframework:** Slim Framework 4 (leve, veloz e focado em orquestração de rotas e middlewares).
* **Banco de Dados Relacional:** MySQL 8.0+ / MariaDB 10.5+ (motor InnoDB com suporte a transações ACID, chaves estrangeiras com integridade referencial rigorosa, índices compostos e busca Full-Text nativa via `MATCH() AGAINST()`).
* **Multi-Tenancy e Isolamento:** Isolamento lógico rígido por inquilino (`store_id`), permitindo escalabilidade On-Premise sobre infraestrutura compartilhada sem vazamento cruzado de dados.
* **Camada de Cache & Mensageria:**
  * *Redis:* Armazenamento em memória para sessões autenticadas, dados de catálogo voláteis e controle de taxa (*Rate Limiting*).
  * *Fallback File/Memory Cache:* Mecanismo inteligente de contingência para execução fluida em hospedagens sem servidor Redis nativo (ex.: Hostinger e hospedagens compartilhadas).
  * *RabbitMQ:* Fila de mensageria assíncrona para processamento desacoplado de eventos pesados e comunicação com ERPs externos.

6.3. **Stack Tecnológica de Front-end & Apresentação**
* **Motor de Templates:** Twig Template Engine (renderização *Server-Side Rendering* - SSR moderna, modular, com herança de templates, auto-escape contra vulnerabilidades XSS e hidratação dinâmica de variáveis).
* **Estilização (CSS/SCSS):** Vanilla SCSS/CSS modular e especializado (estruturado em componentes autônomos como `buttons.css`, `addresses.scss`, `orders.scss`, `returns-institutional.scss`), garantindo carregamento ultrarrápido sem dependência de frameworks CSS pesados.
* **Comportamento & Interatividade:** Vanilla JavaScript (ES6+) assíncrono (Fetch API para cálculo dinâmico de frete, preenchimento inteligente de endereços a partir do CEP via ViaCEP, atualização de carrinho sem recarregar a página e filtros em tempo real).
* **Interface do PDV (Ponto de Venda):** Interface otimizada para terminais físicos e tablets, com comandos rápidos de teclado e fluxo de atendimento ágil para balcão.

6.4. **Segurança, Governança e Conformidade (LGPD & OWASP)**
* **Proteção contra Ameaças OWASP Top 10:**
  * *Proteção CSRF:* Tokens criptográficos únicos de sessão validados em todas as requisições com mutação de estado (POST/PUT/DELETE).
  * *Prevenção de SQL Injection:* Todas as consultas ao banco de dados intermediadas pelo DAO via *Prepared Statements* parametrizados.
  * *Security Headers HTTP:* Configuração rigorosa de cabeçalhos de resposta (`Content-Security-Policy`, `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security`, `Referrer-Policy: strict-origin-when-cross-origin`).
* **Sessões e Cookies:** Cookies configurados obrigatoriamente com identificador criptografado e diretivas `; Secure; HttpOnly; SameSite=Strict`.
* **Proteção de Diretórios e Ativos:** Diretivas Apache/`.htaccess` bloqueando listagem de diretórios (`Options -Indexes`) e isolando a raiz pública (`public/`) dos códigos-fonte e diretórios de armazenamento (`storage/`).
* **Conformidade com a LGPD:** Criptografia de senhas com algoritmos seguros (Argon2id), anonimização e sanitização de logs de auditoria para mascarar dados sensíveis de clientes e operadores.

6.5. **Engenharia de Qualidade, Testes e Assistência por IA**
* **Testes Automatizados (PHPUnit):** Suíte de testes unitários e de integração validando entidades de domínio, DAOs, camadas de serviço e autenticação.
* **Testes Comportamentais BDD (Behat + Gherkin):** Especificação executável de cenários de negócio (Catálogo, Carrinho, Checkout, Regras Fiscais, Segurança e PDV) em arquivos `.feature`.
* **Análise Estática e Padronização:** PHPStan configurado em nível rigoroso de checagem e PHP-CS-Fixer para conformidade estrita com o padrão de formatação PSR-12.
* **Assistência com Inteligência Artificial:** Uso de ferramentas de IA (Google Antigravity) como acelerador de desenvolvimento, validação arquitetural contra diagramas PlantUML (`.puml`) e detecção preventiva de *bugs*.

7. **Riscos e Plano de Contingência**

O gerenciamento de riscos busca antecipar eventos de incerteza técnica, operacional, gerencial ou externa que possam impactar o cronograma, a conformidade legal ou a estabilidade da plataforma Alpha Engine.

7.1. **Matriz de Riscos (Probabilidade x Impacto)**

| ID | Descrição do Risco | Categoria | Probabilidade | Impacto | Nível de Risco | Estratégia |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **RT01** | Complexidade no cálculo tributário brasileiro (ICMS-ST, DIFAL, Reforma Tributária) | Técnico | Média | Alta | **Alto** | Mitigar |
| **RT02** | Indisponibilidade de servidor Redis em ambientes de hospedagem compartilhada | Técnico | Média | Média | **Médio** | Mitigar |
| **RT03** | Concorrência de estoque simultânea entre E-commerce e PDV Físico | Técnico | Média | Alta | **Alto** | Mitigar |
| **RT04** | Instabilidade ou lentidão em APIs externas (Correios, Gateway de Pagamento, ViaCEP) | Técnico/Externo | Alta | Média | **Médio** | Mitigar |
| **RG01** | Sobrecarga de desenvolvimento sob restrições de tempo (equipe reduzida) | Gerencial | Média | Alta | **Alto** | Mitigar |
| **RG02** | Expansão descontrolada de escopo (*Scope Creep*) durante o desenvolvimento | Gerencial | Baixa | Alta | **Médio** | Prevenir |
| **RO01** | Resistência ou dificuldade de adaptação dos operadores do balcão ao PDV Web | Organizacional | Média | Média | **Médio** | Mitigar |
| **RE01** | Vazamento de dados sensíveis ou não-conformidade com a LGPD | Externo/Legal | Baixa | Crítica | **Alto** | Prevenir |
| **RE02** | Mudanças abruptas nas normas tributárias ou leiautes de documentos fiscais | Externo/Legal | Alta | Média | **Médio** | Assumir / Mitigar |

7.2. **Detalhamento dos Planos de Mitigação e Contingência**

* **RT01 - Complexidade nas Regras Tributárias e Fiscais Nacionais:**
  * *Ação Preventiva/Mitigação:* Encapsulamento de toda a lógica fiscal em classes dedicadas utilizando o padrão *Strategy*, isolando alíquotas e regras de ICMS-ST/DIFAL/IBS/CBS por estado de destino.
  * *Plano de Contingência:* Implementação de bateria de testes unitários automatizados com tabela de equivalência para os 26 estados e Distrito Federal, permitindo simulação e auditoria prévia antes da emissão.
* **RT02 - Ausência de Redis em Hospedagens Compartilhadas:**
  * *Ação Preventiva/Mitigação:* Adoção de camada de abstração de cache (*Cache Interface*) que detecta a presença da extensão e do serviço Redis em tempo de inicialização.
  * *Plano de Contingência:* Acionamento automático e transparente de fallback para *FileCache* baseado em sistema de arquivos local com invalidação por TTL, garantindo 100% de funcionalidade sem interrupção.
* **RT03 - Concorrência e Baixa Simultânea de Estoque (PDV x Web):**
  * *Ação Preventiva/Mitigação:* Utilização de transações ACID no MySQL (`BEGIN ... COMMIT`) com controle de concorrência e atualização atômica de saldo (`quantity = quantity - :sold WHERE quantity >= :sold`).
  * *Plano de Contingência:* Registro de eventos de reserva temporária no carrinho e fila de reconciliação de inventário via mensageria RabbitMQ.
* **RT04 - Indisponibilidade de Serviços e APIs de Terceiros:**
  * *Ação Preventiva/Mitigação:* Implementação de *Timeouts* curtos, cache local de faixas de CEP e políticas de retentativa (*Retry Pattern* com *exponential backoff*).
  * *Plano de Contingência:* Apresentação de estimativas de frete baseadas em tabelas de contingência offline pré-carregadas caso a API dos Correios/transportadora fique fora do ar.
* **RG01 - Sobrecarga de Trabalho e Prazo Escasso:**
  * *Ação Preventiva/Mitigação:* Utilização de assistência contínua por agentes de Inteligência Artificial (Google Antigravity) para geração automatizada de boilerplate, refatorações assistidas, geração de testes BDD e validação de consistência.
  * *Plano de Contingência:* Priorização estrita das entregas divididas em marcos modulares (conforme seção 8), garantindo a estabilização do MVP (Produto Mínimo Viável) antes da inclusão de recursos complementares.
* **RO01 - Usabilidade e Adoção do Ponto de Venda (PDV):**
  * *Ação Preventiva/Mitigação:* Desenvolvimento da interface do PDV centrada no operador, com atalhos de teclado ágeis (tecla Enter, navegação sem mouse, leitor de código de barras direto), busca rápida e interface limpa e responsiva.
  * *Plano de Contingência:* Elaboração de manuais ilustrados, vídeos demonstrativos curtos e suporte durante a fase de implantação piloto na loja.
* **RE01 - Segurança da Informação e Conformidade com a LGPD:**
  * *Ação Preventiva/Mitigação:* Criptografia ponta a ponta com TLS 1.3, hashing seguro com Argon2id, controle de permissões por papéis administrativos (*User Groups*), proteção CSRF e sanitização estrita de dados em logs.
  * *Plano de Contingência:* Protocolo documentado de resposta a incidentes, rotação imediata de chaves e credenciais, e isolamento de acessos em caso de comportamento anômalo detectado pelo Rate Limiter.

8. **Cronograma de Marcos Resumido**
- _Observação: O projeto em si, já vinha sendo desenvolvido há mais de 2 anos de forma contínua a passos lentos. Com a ajuda de agentes IA, especificamente Antigravity, conseguimos resolver problemas técnicos do código, deixá-lo mais robusto e com um design mais moderno e atualizado. Como já tinhamos a ideia, requisitos e a "planta-baixa" do projeto em linguagem UML, conseguimos avançar mais rapidamente e com incrível nível de assertos.
Com o início do curso de Análise e Desenvolvimento de Sistemas na FATEC-FV, tivemos a oportunidade de transformar o projeto em um projeto acadêmico e, com isso, tivemos a oportunidade de melhorar o projeto e deixá-lo mais robusto, com melhores práticas de desenvolvimento e arquitetura de software. 
Os itens deste cronograma estão dispostos em ordem cronológica, ou seja, o primeiro item foi o primeiro a ser entregue, o segundo item foi o segundo a ser entregue, e assim sucessivamente. Além disso, os itens estão agrupados por fases, o que facilita o acompanhamento do desenvolvimento do projeto. O identificador “#ID” refere-se ao número da tarefa, que pode ser utilizado para identificar a tarefa no sistema de controle de versões (Git)._

| ID | Marco | Data de entrega |
| :--- | :--- | :--- |
| #01 | Product Registration in Admin Dashboard | 18/06/2026 17:18:04 |
| #02 | Implementar Exclusão de Produto no Dashboard | 18/06/2026 18:17:34 |
| #03 | Conclusão da Migração do Sistema de Idiomas (Compatibilidade PSR-11) | 20/06/2026 09:29:13 |
| #04 | Relatório do andamento do projeto Alpha em 2026-06-05 | 20/06/2026 09:35:02 |
| #05 | Generalização do Sistema de Autenticação | 20/06/2026 09:45:05 |
| #06 | Unificação Visual e Funcional da Busca com a Página de Categoria | 20/06/2026 09:55:16 |
| #07 | Adaptar Descrição do Produto para Markdown | 20/06/2026 09:59:45 |
| #08 | Hydrate Sorts and Limits for Category and Search Pages | 20/06/2026 10:04:15 |
| #09 | Autofill CEP on Cart Page | 20/06/2026 10:09:07 |
| #10 | Plan: Populate permissions and implement login logging | 20/06/2026 10:14:31 |
| #11 | Adição de Fabricante e Logotipo nos Produtos | 21/06/2026 09:25:09 |
| #12 | Variações de Produto no Padrão On-Premise (Pai e Filho) | 21/06/2026 10:05:11 |
| #13 | Adicionar Edição de Imagem para Variações de Produto | 22/06/2026 12:55:11 |
| #14 | Refatoração do Carrinho de Compras para Tratar Variações de Produtos | 22/06/2026 13:34:44 |
| #15 | Exibição de Intervalos de Preços ("A partir de") para Variações | 22/06/2026 14:34:12 |
| #16 | Adicionar opção de categoria aos produtos no painel de administração | 22/06/2026 15:44:01 |
| #17 | Ocultar Produtos e Variações Fora de Estoque | 22/06/2026 16:18:31 |
| #18 | Implementar contatos no painel de administração de fornecedores | 22/06/2026 18:36:45 |
| #19 | Dashboard Language Selection Implementation Plan | 22/06/2026 19:58:34 |
| #20 | Hydration of Twig Variables with Selected Language in Admin Dashboard | 22/06/2026 20:10:34 |
| #21 | Internacionalização das Configurações da Loja | 25/06/2026 17:26:21 |
| #22 | Refatoração e Internacionalização do Módulo de Fabricantes | 25/06/2026 17:38:29 |
| #23 | Refatoração e Internacionalização do Módulo de Clientes | 25/06/2026 17:54:39 |
| #24 | Refatoração e Internacionalização do Módulo de Endereços do Cliente | 25/06/2026 18:06:28 |
| #25 | Refatoração e Internacionalização do Módulo de Autenticação Admin | 25/06/2026 18:14:18 |
| #27 | Refatoração e Internacionalização do Módulo de Devoluções | 25/06/2026 18:40:02 |
| #28 | Tradução e Localização de Pedidos e Faturas | 25/06/2026 18:57:45 |
| #29 | Suporte a Variações de Produtos no Carrinho do Visitante | 27/06/2026 19:12:52 |
| #30 | Adaptar Diagrama de Sequência do PDV (POS) para a Arquitetura do Projeto | 27/06/2026 21:07:19 |
| #31 | Implementação da Tela do Vendedor (PDV / POS) | 27/06/2026 21:30:01 |
| #32 | Implementação do Módulo do Caixa (PDV / POS Cashier) | 27/06/2026 21:53:02 |
| #33 | Refatoração de Estilos do PDV | 28/06/2026 11:31:35 |
| #34 | Controle de Concorrência Otimista (RMA) | 28/06/2026 16:38:42 |
| #36 | Análise de Reaproveitamento de Estilos CSS e Otimização de Templates Twig | 09/07/2026 17:47:15 |
| #37 | Fase 2 (Consolidação de Estilos de Pedidos) | 09/07/2026 17:50:03 |
| #38 | Fase 3 (Consolidação de Estilos de Devoluções e Institucional) | 09/07/2026 17:53:02 |
| #39 | Fase 4 (Otimização Arquitetural e Modularização CSS) | 09/07/2026 18:12:36 |
| #40 | Refatoração de Botões (buttons.css) e Reaproveitamento de Variáveis | 09/07/2026 19:20:02 |
| #41 | Modularização e Especialização de CSS/SCSS | 10/07/2026 09:01:53 |
| #42 | Conversão de `returns-institutional.css` para SCSS e Melhorias no Compilador | 10/07/2026 09:22:31 |
| #43 | Conversão de `addresses.css` para SCSS | 10/07/2026 09:26:48 |
| #44 | Conversão de `orders.css` para SCSS | 10/07/2026 09:30:48 |
| #45 | Implementação de Sistema de Eventos (Observer) e Integração com RabbitMQ | 10/07/2026 16:25:03 |
| #46 | Otimização de Documentação e Glossário para Agentes de IA | 16/07/2026 13:33:09 |
| #47 | Aperfeiçoamento da Pasta de Documentação (`docs/`) | 16/07/2026 13:51:01 |
| #48 | Remoção Segura de Tabelas Obsoletas | 28/07/2026 18:18:26 |
| #49 | Prioridade Alta (Vulnerabilidades Críticas de Aplicação Web) | 28/07/2026 18:46:07 |
| #50 | Proteção CSRF (Cross-Site Request Forgery) | 28/07/2026 18:58:20 |
| #51 | Implementação de Cabeçalhos de Segurança HTTP (Security Headers) | 28/07/2026 19:06:40 |
| #52 | Implementação: Flag `; Secure` Condicional em Cookies de Sessão | 28/07/2026 19:14:06 |
| #53 | Limitação de Taxa por IP (Rate Limiting com Redis) | 28/07/2026 21:19:38 |
| #54 | Desativação do Modo de Depuração (Debug Mode) em Produção | 28/07/2026 21:44:10 |
| #55 | Isolamento Rígido de Tenants (store_id) | 28/07/2026 21:51:45 |
| #56 | Proteção dos Diretórios de Uploads (public_html/image e storage/) | 28/07/2026 21:57:29 |
| #57 | Conformidade LGPD (Anonimização e Sanitização de Logs) | 28/07/2026 22:03:54 |
| #58 | Ajuste de Isolamento Multi-tenant (`store_id = 1`) | 30/07/2026 08:14:14 |
| #59 | Estrutura e Exemplos Spec-Driven (`docs/specs/`) | 30/07/2026 09:22:44 |
| #60 | Gestão de Funcionários e Papéis/Permissões no Dashboard Admin | 30/07/2026 11:19:56 |
| #61 | Atalhos Dinâmicos no Dashboard Condicionados ao Papel (`UserGroup`) | 30/07/2026 11:33:52 |
| #62 | Internacionalização de Papéis de Usuário (User Group Descriptions) | 30/07/2026 15:49:13 |
| #63 | Aperfeiçoamento do mecanismo de busca por produtos: Busca Full-Text de Produtos (MySQL MATCH/AGAINST) | 02/08/2026 09:26:14 |
| #64 | On-Premise Tenant Provisioning & Setup Wizard | 02/08/2026 10:50:54 |
| #65 | Configuração Dinâmica do Prefixo de Banco de Dados e Mascaramento do Dashboard | 02/08/2026 15:36:26 |
| #66 | Eliminação de Códigos SQL Soltos nas Actions do Painel Administrativo | 05/08/2026 19:06:00 |
| #67 | Bateria de Testes Automatizados de Validação de Software (PHPUnit) | 09/08/2026 12:01:13 |
| #68 | Configuração e Otimização do PHPStan para Agentes de IA | 09/08/2026 13:04:59 |
| #69 | Novos Diagramas de Sequência | 09/08/2026 19:50:46 |
| #70 | Novos Diagramas de Atividades (Activity Diagrams) | 09/08/2026 20:10:11 |
| #71 | Diagramas de Componentes de Arquitetura | 09/08/2026 22:05:15 |
| #72 | Fazer a tela para o administrador da loja inserir inforações | 10/08/2026 19:56:11 |
| #73 | Reorganizar e Consolidar Testes em `tests/Validation` | 10/08/2026 21:00:52 |
| #74 | Reestruturação da Arquitetura: Isolamento da Pasta `backend/` | 12/08/2026 15:15:07 |
| #75 | Adaptação de Cache para Hospedagens sem Redis (Hostinger) | 12/08/2026 17:33:59 |
| #76 | Ajuste no Mecanismo de Implantação (Deploy & Setup Wizard) | 13/08/2026 11:41:46 |
| #77 | Reforço e Validação de Segurança & Auditoria (Alpha Engine) | 13/08/2026 18:18:27 |
| #78 | Fallback de Auditoria em Banco de Dados (MySQL) | 13/08/2026 18:59:43 |
| #79 | Testes com Gherkin (Behat) & Integration com Testes Existentes | 16/08/2026 12:33:58 |
| #80 | Criação dos Arquivos Gherkin (.feature) para Cart e Checkout | 16/08/2026 14:02:25 |
| #81 | Suíte de Testes BDD Modular e Especializada (Alpha Engine) `features/security/` | 16/08/2026 14:51:13 |
| #82 | Estrutura Completa & Modular de Testes BDD (Alpha Engine) | 16/08/2026 15:11:30 |
| #83 | Estruturação e Preenchimento do Documento de Requisitos Acadêmico | 17/08/2026 08:36:02 |
| #84 | Estruturação e Preenchimento do Documento de Atividades do Negócio | 17/08/2026 09:22:12 |

9. **Orçamento Resumido**

Apresentar um orcamento reduzido considerando:

* Custos fixos   
  * Hardware:
    Cenário 1:
      Como é um sistema On-Premise, o cliente só precisará dos hardwares de PDV (Ponto de Venda), ou seja, os hardwares necessários são: Computador ou Tablet com navegador instalado e conexão com a internet, leitor de código de barras e impressora de nota fiscal.
    Cenário 2: 
      Caso o cliente deseje que o software funcione em modo local e/ou offline, o cliente precisará de um servidor local, o que implicaria em um custo adicional de hardware, tais como roteador, switches, cabos, computadores, etc. De forma similar ao cenário 1, precisará tambem de leitor de código de barras e impressora de nota fiscal.
    Resumindo: O custo dependerá da escolha do cliente em relação ao modelo de implantação (On-Premise vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.
    A estimativa de mínima de custo para um ponto de venda seria em torno de R$ 2.500,00, considerando: Computador ou Tablet, Leitor de código de barras e Impressora de nota fiscal.    

  * Licensas de software  
      O sistema em si não exigirá licensas de software, pois ele será desenvolvido utilizando tecnologias de código aberto.
      No entanto, dependendo das funcionalidades que o cliente desejar, pode haver necessidade de licensas de software, como por exemplo: 
      * Sistema de pagamento: Se o cliente desejar que o software tenha sistema de pagamento integrado, será necessário contratar um gateway de pagamento, que terá custo mensal.    
  * Treinamentos  
      O treinamento será realizado de forma presencial ou remota, dependendo da preferência do cliente.
* Custos variaveis (dependem do esforco de desenvolvimento e aumentam conforme o tempo do projeto)  
  * Hospedagem: Para o modelo On-Premise, o cliente precisará contratar uma hospedagem para o software, que pode ser em um servidor local ou em um servidor na nuvem. O custo da hospedagem dependerá do plano escolhido, alem de 20% sobre cada venda realizada a título de consultoria. Há também o custo de dominio que é cobrada anualmente pelo Registro.br. Se o cliente optar pelo modelo local, não terá esse custo.   
  * Contrato de manutenção e assistência técnica: O custo de manutenção e assistência técnica dependerá do modelo de implantação (On-Premise vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV. Como o software é livre, a manutenção e assistência técnica serão cobradas à parte, conforme o serviço contratado e de livre escolha do cliente.
  * Custo de instalacoes: O custo de instalacoes dependerá do modelo de implantação (On-Premise vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.  
  * Consumo de energia e materiais: O custo de consumo de energia e materiais dependerá do modelo de implantação (On-Premise vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.  
  * Operacao da rede de computadores: O custo de operacao da rede de computadores dependerá do modelo de implantação (On-Premise vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.  
* Orçamento para riscos (margem de contingência)

10. **Plano de Negócios**

10.1. **Modelo de Negócio**
Como o software é livre, o custo de desenvolvimento inicial será pago pelo próprio desenvolvedor. O retorno financeiro virá de serviços de consultoria, instalação, treinamento, manutenção e adaptações para clientes. Para a manutenção e assistência técnica, o cliente terá total liberdade de escolha, podendo contratar qualquer empresa ou profissional. O pagamento será feito diretamente ao desenvolvedor, sem intermediação de qualquer plataforma.  