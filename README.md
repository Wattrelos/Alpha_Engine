# 🚀 Alpha Engine - Documentação Técnica

Bem-vindo ao repositório central da **Alpha Engine**, um sistema de e-commerce moderno e standalone desenvolvido totalmente do zero (abrangendo bootstrap, rotas, controllers e views). A engine antigado código legado foi completamente abandonada de nosso runtime, restando seu código apenas como referência conceitual e de banco de dados. Este ecossistema implementa padrões rígidos como **Repository Pattern**, **Data Mappers** e **Domain-Driven Design (DDD)** para assegurar máxima performance e escalabilidade.

## 📑 Índice de Documentação

Para facilitar a navegação, a documentação detalhada foi dividida nos seguintes módulos dentro da pasta `docs/`:

### 1. Arquitetura Geral
Visão geral da estrutura de pastas em `core/`, separação de camadas e princípios de design aplicados.
*   Arquitetura e Engenharia de Software da Alpha Engine
*   Documentação Central de Arquitetura Standalone
*   Débitos Técnicos e Anti-Patterns Sanados

### 2. Modelos de Domínio e Entidades
Detalhamento das árvores de agregação e grafos de objetos:
*   ✅ **Catálogo**: `Product`, `Category`, `CategoryPath`, `CategoryToLayout`, `Manufacturer`, `ManufacturerToLayout`, `Information`, `StockStatus`, `Review`.
*   ✅ **CMS (Blog)**: `Article`, `Topic`.
*   ✅ **Design**: `Banner`, `Theme`, `Translation`.
*   ✅ **SEO**: `SeoUrl`.
*   ✅ **Vendas/Checkout**: `Cart`, `SubscriptionPlan`, `Subscription`, `SubscriptionHistory`, `SubscriptionStatus`, `Order`, `Coupon`, `CouponCategory`, `CouponHistory`, `CouponProduct`, `OrderReturn`, `ReturnAction`, `ReturnReason`.
*   ✅ **Sistema/Localização**: `Setting`, `WeightClass`, `LengthClass`, `LengthClassDescription`, `TaxClass`, `TaxRate`, `TaxRule`, `TaxRateToCustomerGroup`, `Session`, `Startup`, `AddressFormat`, `Cron`, `Event`, `Gdpr`, `Location`, `Notification`, `Upload`.
*   ✅ **Segurança e Auditoria**: `User`, `UserGroup`, `UserLogin`, `OrderOption`, `Api`, `Statistics`, `Gdpr`.
*   ✅ **Marketing**: `Marketing`, `MarketingReport`.
*   ✅ **Clientes**: `Customer`, `CustomerApproval`, `CustomerHistory`, `CustomerLogin`, `CustomerOnline`, `CustomerReward`, `CustomerTransaction`, `Address`, `CustomerGroup`, `Notification`.
*   ✅ **Módulos e Extensões**: `Extension`, `ExtensionInstall`, `ExtensionPath`, `Module`.

### 3. Infraestrutura, Segurança e Banco de Dados
Explicação técnica sobre o `DataAccessObject` (DAO), abstração de transações aninhadas e hardening de segurança com PDO.
*   Segurança, Sistema e Infraestrutura
*   ✅ **[Guia de Recomendações e Diretrizes de Segurança](file:///var/www/html/agsonhos/docs/architecture/security_recommendations.md)** (100% Implementado: CSRF, Security Headers, Secure Cookies, Rate Limiting com Redis, Hardening Produção, Isolamento Multi-Tenant, Proteção de Uploads e LGPD).
*   Desacoplamento do Sistema de Sessões

### 4. Persistência (Mappers & Repositories)
Fluxo de salvamento e recuperação de dados, incluindo o ciclo de vida de uma entidade do domínio até o banco de dados.

### 5. Serviços de Entrega (Shipping)
A camada de lógica de negócios e cálculo de frete (Shipping Services) foi **integralmente concluída** e refatorada para a arquitetura Alpha Engine. Foram modernizados e validados os serviços base (`WeightBased`, `FreeShipping` e `FlatRate`), os quais agora operam de forma isolada, consumindo repositórios de domínio para conversão unitária de medidas (peso/dimensão) e checagem nativa de zonas geográficas.

### 6. Guia de Diagramas
Instruções para visualizar e editar os diagramas PlantUML (`.puml`) localizados na pasta `diagrams/`.

### 7. Ferramentas de Auditoria e Automação ORM
Coleção de scripts vitais em `tests/scripts_uteis/` (como o *Detector de Zumbis* e *Renomeador de Referências*) que garantem sincronia absoluta entre Entidades PHP, Banco de Dados e Namespaces.

### 8. Estratégia de Cache e Performance
Adoção do contrato `CacheStrategyInterface` (inspirado na PSR-16), permitindo injeção de drivers de cache em memória nas instâncias de Repository para mitigação de consultas repetidas (N+1 Queries).

### 9. Subsistema de Catálogo (Categorias e Produtos)
Detalhes sobre a implementação de rotas amigáveis, paginação de categorias e refatoração completa do visual das páginas de produto no padrão BEM/CSS sem Bootstrap. (Ver [Progresso do Catálogo](file:///var/www/html/agsonhos/docs/catalog_progress.md))

### 10. Análises de Viabilidade Técnica e Migrações
Estudos de impacto para decisões arquiteturais de banco de dados e infraestrutura:
*   [Análise de Viabilidade de Migração de PKs/FKs para BIGINT](file:///var/www/html/agsonhos/docs/pks_fks_bigint_feasibility.md)

### 11. Subsistema do Carrinho de Compras e Checkout (Alpha Engine)
Substituição completa da biblioteca de carrinho legada pelo `CartRepository` e `CartMapper` desacoplados. Registro do helper de pesos `Weight` no Registry global e ativação da rota de checkout dinâmica `/{lang}/checkout` com interface responsiva e interativa no padrão BEM/CSS. (Ver [Progresso do Carrinho](file:///var/www/html/agsonhos/docs/cart_progress.md))

### 12. Subsistema de Autenticação e Cadastro (Auth)
Refatoração integral do fluxo de login, registro e gerenciamento de conta via middlewares (Slim) e abstração `AuthService`. (Ver [Progresso de Autenticação](file:///var/www/html/agsonhos/docs/auth_progress.md))

### 13. Componentização e Apresentação Visual (Twig)
Mapeamento da estrutura visual adotando *Atomic Design* (Atoms, Molecules, Organisms, Layouts e Pages) e reestruturação da interface de usuário em subdiretórios. (Ver [Estrutura de Diretórios](file:///var/www/html/agsonhos/docs/directories_structure.md))

### 14. Subsistemas Auxiliares e Transversais
*   Refatoração da Biblioteca de Moedas (Currency)

### 15. Compras, Fornecedores e Localização Geo (Procurement)
Implementação do sistema de fornecedores na área administrativa, acoplado a um mecanismo robusto de endereços com suporte a países, estados (zones) e cidades (Geo entities).

### 16. Saneamento de Pseudo-Nulls e Integridade de Dados
Fim definitivo do anti-pattern `FK = 0` na base de dados para relacionamentos do catálogo (categorias e fabricantes), migrando registros para `NULL` e adicionando chaves estrangeiras (`FOREIGN KEY`) restritivas.

### 17. Modernização Visual do Painel Administrativo (Admin)
Extração de estilos inline e centralização em arquivos externos (`/css/admin/`), acompanhado de um upgrade estético completo: tons suaves (Slate 50), sombras 3D em camadas de profundidade, transições suaves e feedback físico interativo em hovers e cliques.

---
*Nota: Este índice foi gerado para organizar o conteúdo distribuído. Os arquivos `.md` mencionados acima devem ser mantidos em sincronia com as evoluções do código em `core/`.*
