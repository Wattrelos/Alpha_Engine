
![Status do Pipeline](https://github.com/Wattrelos/Alpha_Engine/actions/workflows/playwright.yml/badge.svg)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)
![Slim Framework](https://img.shields.io/badge/framework-Slim_4-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Testing](https://img.shields.io/badge/tests-PHPUnit%20%7C%20Behat%20%7C%20Playwright-brightgreen.svg)
![Code Quality](https://img.shields.io/badge/quality-PHPStan%20%7C%20PSR--12-informational.svg)
![Nginx Proxy Manager](https://img.shields.io/badge/nginx_proxy_manager-%23F15833.svg)
![Apache2](https://img.shields.io/badge/apache-%23D42029.svg)
![Redis](https://img.shields.io/badge/redis-%23DD0031.svg)
![RabbitMQ](https://img.shields.io/badge/RabbitMQ-%23FF6600.svg)


## Entregas da Faculdade

### [1. Documento de Visão](/docs/1.%20Documento%20de%20Visão.doc.md)
### [2. Atividade do Negócio](/docs/2.%20Atividades%20do%20Negócio.doc.md)
### [3. Requisitos do Sistema](/docs/3.%20Requisitos%20do%20Sistema.doc.md)
### [4. Casos de Uso](/docs/4.%20Casos%20de%20Uso.doc.md)


## [Requisitos para a matéria de Laboratório de Engenharia de Software](/docs/Requisitos_para_a_matéria_de_Laboratório_de_Engenharia_de_Software.md)



## Técnicas de desenvolvimento de software
- Mockup de telas para a apresentação do produto e definição de identidade visual;
- Desenvolvimento em fatias (Slice Architecture);
- Backlog do projeto
## Introdução

O Projeto meusite visa oferecer um serviço de entrega de produtos diretamente na casa do cliente, proporcionando agilidade e praticidade no processo de compra e recebimento.


## Padrões de Projeto (Design Patterns)

### Aplicados na camada de persistência:
- DAO (Data Access Object)
- Singleton
- Data Mapper
- Query Builder (Fluent Interface)
- Unit of Work ()

### Aplicados na camada de negócio:
- Strategy
- Repository
- Dependency Injection
- Facade
* Interface

### Aplicados na camada de aplicação:
- Factory
- Decorator

### Aplicados na camada de controle:
- Observer
- Interpreter

### Aplicados na camada de interface:

Decisões de arquitetura (Architectural Decision Records - ADRs):

- [Modularização e compilação de CSS/SCSS](/docs/architecture/adr/0001-css-modularization-architecture.md)
- [Checkout Transactional Idempotency](/docs/architecture/adr/0002-checkout-transactional-idempotency.md)
- [Locales JSON PSR-11](/docs/architecture/adr/0003-Locales_JSON_PSR-11.md)
- [Alpha Core Stack](/docs/architecture/adr/0004-alpha-core-stack.md)
- [Use DateTime over Timestamp](/docs/architecture/adr/0005-use-datetime-over-timestamp.md)
- [Version Control Software](/docs/architecture/adr/0006-version_control_software.md)
- [Audictory](/docs/architecture/adr/0007-audictory.md)

## Planos de implementações

- [#01](/docs/architecture/deployment/deploy-plan_001.md) Product Registration in Admin Dashboard
- [#02](/docs/architecture/deployment/deploy-plan_002.md) Implementar Exclusão de Produto no Dashboard
- [#03](/docs/architecture/deployment/deploy-plan_003.md) Conclusão da Migração do Sistema de Idiomas (Compatibilidade PSR-11)
- [#04](/docs/architecture/deployment/deploy-plan_004.md) Relatório do andamento do projeto Alpha em 2026-06-05
- [#05](/docs/architecture/deployment/deploy-plan_005.md) Generalização do Sistema de Autenticação
- [#06](/docs/architecture/deployment/deploy-plan_006.md) Unificação Visual e Funcional da Busca com a Página de Categoria
- [#07](/docs/architecture/deployment/deploy-plan_007.md) Adaptar Descrição do Produto para Markdown
- [#08](/docs/architecture/deployment/deploy-plan_008.md) Hydrate Sorts and Limits for Category and Search Pages
- [#09](/docs/architecture/deployment/deploy-plan_009.md) Autofill CEP on Cart Page 
- [#10](/docs/architecture/deployment/deploy-plan_010.md) Plan Populate permissions and implement login logging 
- [#11](/docs/architecture/deployment/deploy-plan_011.md) Adição de Fabricante e Logotipo nos Produtos 
- [#12](/docs/architecture/deployment/deploy-plan_012.md) Variações de Produto no Padrão On-Premise (Pai e Filho) 
- [#13](/docs/architecture/deployment/deploy-plan_013.md) Adicionar Edição de Imagem para Variações de Produto 
- [#14](/docs/architecture/deployment/deploy-plan_014.md) Refatoração do Carrinho de Compras para Tratar Variações de Produtos 
- [#15](/docs/architecture/deployment/deploy-plan_015.md) Exibição de Intervalos de Preços ("A partir de") para Variações 
- [#16](/docs/architecture/deployment/deploy-plan_016.md) Adicionar opção de categoria aos produtos no painel de administração 
- [#17](/docs/architecture/deployment/deploy-plan_017.md) Ocultar Produtos e Variações Fora de Estoque 
- [#18](/docs/architecture/deployment/deploy-plan_018.md) Implementar contatos no painel de administração de fornecedores 
- [#19](/docs/architecture/deployment/deploy-plan_019.md) Dashboard Language Selection Implementation Plan 
- [#20](/docs/architecture/deployment/deploy-plan_020.md) Hydration of Twig Variables with Selected Language in Admin Dashboard 
- [#21](/docs/architecture/deployment/deploy-plan_021.md) Internacionalização das Configurações da Loja 
- [#22](/docs/architecture/deployment/deploy-plan_022.md) Refatoração e Internacionalização do Módulo de Fabricantes 
- [#23](/docs/architecture/deployment/deploy-plan_023.md) Refatoração e Internacionalização do Módulo de Clientes 
- [#24](/docs/architecture/deployment/deploy-plan_024.md) Refatoração e Internacionalização do Módulo de Endereços do Cliente 
- [#25](/docs/architecture/deployment/deploy-plan_025.md) Refatoração e Internacionalização do Módulo de Autenticação Admin 
- [#27](/docs/architecture/deployment/deploy-plan_027.md) Refatoração e Internacionalização do Módulo de Devoluções 
- [#28](/docs/architecture/deployment/deploy-plan_028.md) Tradução e Localização de Pedidos e Faturas 
- [#29](/docs/architecture/deployment/deploy-plan_029.md) Suporte a Variações de Produtos no Carrinho do Visitante 
- [#30](/docs/architecture/deployment/deploy-plan_030.md) Adaptar Diagrama de Sequência do PDV (POS) para a Arquitetura do Projeto 
- [#31](/docs/architecture/deployment/deploy-plan_031.md) Implementação da Tela do Vendedor (PDV / POS) 
- [#32](/docs/architecture/deployment/deploy-plan_032.md) Implementação do Módulo do Caixa (PDV / POS Cashier) 
- [#33](/docs/architecture/deployment/deploy-plan_033.md) Refatoração de Estilos do PDV 
- [#34](/docs/architecture/deployment/deploy-plan_034.md) Controle de Concorrência Otimista (RMA) 
- [#36](/docs/architecture/deployment/deploy-plan_036.md) Análise de Reaproveitamento de Estilos CSS e Otimização de Templates Twig 
- [#37](/docs/architecture/deployment/deploy-plan_037.md) Fase 2 (Consolidação de Estilos de Pedidos) 
- [#38](/docs/architecture/deployment/deploy-plan_038.md) Fase 3 (Consolidação de Estilos de Devoluções e Institucional) 
- [#39](/docs/architecture/deployment/deploy-plan_039.md) Fase 4 (Otimização Arquitetural e Modularização CSS) 
- [#40](/docs/architecture/deployment/deploy-plan_040.md) Refatoração de Botões (buttons.css) e Reaproveitamento de Variáveis 
- [#41](/docs/architecture/deployment/deploy-plan_041.md) Modularização e Especialização de CSS/SCSS 
- [#42](/docs/architecture/deployment/deploy-plan_042.md) Conversão de `returns-institutional.css` para SCSS e Melhorias no Compilador 
- [#43](/docs/architecture/deployment/deploy-plan_043.md) Conversão de `addresses.css` para SCSS 
- [#44](/docs/architecture/deployment/deploy-plan_044.md) Conversão de `orders.css` para SCSS 
- [#45](/docs/architecture/deployment/deploy-plan_045.md) Implementação de Sistema de Eventos (Observer) e Integração com RabbitMQ 
- [#46](/docs/architecture/deployment/deploy-plan_046.md) Otimização de Documentação e Glossário para Agentes de IA 
- [#47](/docs/architecture/deployment/deploy-plan_047.md) Aperfeiçoamento da Pasta de Documentação (`docs/`) 
- [#48](/docs/architecture/deployment/deploy-plan_048.md) Remoção Segura de Tabelas Obsoletas 
- [#49](/docs/architecture/deployment/deploy-plan_049.md) Prioridade Alta (Vulnerabilidades Críticas de Aplicação Web) 
- [#50](/docs/architecture/deployment/deploy-plan_050.md) Proteção CSRF (Cross-Site Request Forgery) 
- [#51](/docs/architecture/deployment/deploy-plan_051.md) Implementação de Cabeçalhos de Segurança HTTP (Security Headers) 
- [#52](/docs/architecture/deployment/deploy-plan_052.md) Implementação  Flag `; Secure` Condicional em Cookies de Sessão 
- [#53](/docs/architecture/deployment/deploy-plan_053.md) Limitação de Taxa por IP (Rate Limiting com Redis) 
- [#54](/docs/architecture/deployment/deploy-plan_054.md) Desativação do Modo de Depuração (Debug Mode) em Produção 
- [#55](/docs/architecture/deployment/deploy-plan_055.md) Isolamento Rígido de Tenants (store_id) 
- [#56](/docs/architecture/deployment/deploy-plan_056.md) Proteção dos Diretórios de Uploads (public_html/image e storage/) 
- [#57](/docs/architecture/deployment/deploy-plan_057.md) Conformidade LGPD (Anonimização e Sanitização de Logs) 
- [#58](/docs/architecture/deployment/deploy-plan_058.md) Ajuste de Isolamento Multi-tenant (`store_id = 1`) 
- [#59](/docs/architecture/deployment/deploy-plan_059.md) Estrutura e Exemplos Spec-Driven (`docs/specs/`) 
- [#60](/docs/architecture/deployment/deploy-plan_060.md) Gestão de Funcionários e Papéis/Permissões no Dashboard Admin 
- [#61](/docs/architecture/deployment/deploy-plan_061.md) Atalhos Dinâmicos no Dashboard Condicionados ao Papel (`UserGroup`) 
- [#62](/docs/architecture/deployment/deploy-plan_062.md) Internacionalização de Papéis de Usuário (User Group Descriptions) 
- [#63](/docs/architecture/deployment/deploy-plan_063.md) Aperfeiçoamento do mecanismo de busca por produtos
- [#64](/docs/architecture/deployment/deploy-plan_064.md) On-Premise Tenant Provisioning & Setup Wizard 
- [#65](/docs/architecture/deployment/deploy-plan_065.md) Configuração Dinâmica do Prefixo de Banco de Dados e Mascaramento do Dashboard 
- [#66](/docs/architecture/deployment/deploy-plan_066.md) Eliminação de Códigos SQL Soltos nas Actions do Painel Administrativo 
- [#67](/docs/architecture/deployment/deploy-plan_067.md) Bateria de Testes Automatizados de Validação de Software (PHPUnit) 
- [#68](/docs/architecture/deployment/deploy-plan_068.md) Configuração e Otimização do PHPStan para Agentes de IA 
- [#69](/docs/architecture/deployment/deploy-plan_069.md) Novos Diagramas de Sequência 
- [#70](/docs/architecture/deployment/deploy-plan_070.md) Novos Diagramas de Atividades (Activity Diagrams) 
- [#71](/docs/architecture/deployment/deploy-plan_071.md) Diagramas de Componentes de Arquitetura 
- [#72](/docs/architecture/deployment/deploy-plan_072.md) Fazer a tela para o administrador da loja inserir inforações 
- [#73](/docs/architecture/deployment/deploy-plan_073.md) Reorganizar e Consolidar Testes em `tests/Validation` 
- [#74](/docs/architecture/deployment/deploy-plan_074.md) Reestruturação da Arquitetura  Isolamento da Pasta `backend/` 
- [#75](/docs/architecture/deployment/deploy-plan_075.md) Adaptação de Cache para Hospedagens sem Redis (Hostinger) 
- [#76](/docs/architecture/deployment/deploy-plan_076.md) Ajuste no Mecanismo de Implantação (Deploy & Setup Wizard) 
- [#77](/docs/architecture/deployment/deploy-plan_077.md) Reforço e Validação de Segurança & Auditoria (Alpha Engine) 
- [#78](/docs/architecture/deployment/deploy-plan_078.md) Fallback de Auditoria em Banco de Dados (MySQL) 
- [#79](/docs/architecture/deployment/deploy-plan_079.md) Testes com Gherkin (Behat) & Integration com Testes Existentes 
- [#80](/docs/architecture/deployment/deploy-plan_080.md) Criação dos Arquivos Gherkin (.feature) para Cart e Checkout 
- [#81](/docs/architecture/deployment/deploy-plan_081.md) Suíte de Testes BDD Modular e Especializada (Alpha Engine) `features/security/` 
- [#82](/docs/architecture/deployment/deploy-plan_082.md) Estrutura Completa & Modular de Testes BDD (Alpha Engine) 
- [#83](/docs/architecture/deployment/deploy-plan_083.md) Estruturação e Preenchimento do Documento de Requisitos Acadêmico 
- [#84](/docs/architecture/deployment/deploy-plan_084.md) Estruturação e Preenchimento do Documento de Atividades do Negócio 
- [#85](/docs/architecture/deployment/deploy-plan_085.md) Arquivos importantes para a raiz do site 
- [#86](/docs/architecture/deployment/deploy-plan_086.md) Adicionar Testes BDD de Autenticação (`login.feature`) 
- [#87](/docs/architecture/deployment/deploy-plan_087.md) Edição de Atributos Estendidos de Produtos e Organização em Abas 
- [#88](/docs/architecture/deployment/deploy-plan_088.md) Implementação de Calculadora de Materiais de Construção (Pisos e Revestimentos) 
- [#89](/docs/architecture/deployment/deploy-plan_089.md) Implementação do Módulo de Cotação de Projetos e Prestadores de Serviço (RFQ, BoQ e Material Takeoff) 
- [#90](/docs/architecture/deployment/deploy-plan_090.md) Correção dos Atalhos da Área "Minha Conta" Redirecionando para Login 
- [#91](/docs/architecture/deployment/deploy-plan_091.md) Implementamos o tratamento e geração dinâmica de imagens sob demanda 
- [#92](/docs/architecture/deployment/deploy-plan_092.md) Atualização do Diagrama de Casos de Uso do Cliente 
- [#93](/docs/architecture/deployment/deploy-plan_093.md) Implementação da Pirâmide de Testes e a Divisão de Responsabilidades 
- [#94](/docs/architecture/deployment/deploy-plan_094.md) Evolução da Suíte E2E Playwright 
- [#95](/docs/architecture/deployment/deploy-plan_095.md) Refatoração daEstrutura de Execução de Testes E2E Playwright 
- [#96](/docs/architecture/deployment/deploy-plan_096.md) Implementação de Configuração para Permitir/Bloquear Compras de Visitantes (Guest Checkout) 
- [#97](/docs/architecture/deployment/deploy-plan_097.md) Implementação de Auto-Login no Cadastro e Atualização de Testes E2E 
- [#98](/docs/architecture/deployment/deploy-plan_098.md) Implementação de Auditoria e Monitoramento de Acessos no Dashboard 
- [#99](/docs/architecture/deployment/deploy-plan_099.md) Adequação e Suporte ao PHP 8.2 na Alpha Engine para compatibilidade, por exemplo, XAMPP 
- [#101](/docs/architecture/deployment/deploy-plan_101.md) Implementação de Detalhes do Produto no PDV 
- [#102](/docs/architecture/deployment/deploy-plan_102.md) Elaboração das Especificações de Casos de Uso & Reorganização de Pastas  
- [#103](/docs/architecture/deployment/deploy-plan_103.md) Plano de Implementação  Apresentação Acadêmica Web Interativa (FATEC-FV - LES) 
- [#104](/docs/architecture/deployment/deploy-plan_104.md) Planejamento  Criação dos Artefatos de Casos de Uso Core em `/docs/business/use-cases/core/` 

