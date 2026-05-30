# 🚀 Alpha Engine - Documentação Técnica

Bem-vindo ao repositório central da **Alpha Engine**, um sistema de e-commerce moderno e standalone desenvolvido totalmente do zero (abrangendo bootstrap, rotas, controllers e views). A engine antiga do OpenCart foi completamente abandonada de nosso runtime, restando seu código apenas como referência conceitual e de banco de dados. Este ecossistema implementa padrões rígidos como **Repository Pattern**, **Data Mappers** e **Domain-Driven Design (DDD)** para assegurar máxima performance e escalabilidade.

## 📑 Índice de Documentação

Para facilitar a navegação, a documentação detalhada foi dividida nos seguintes módulos dentro da pasta `docs/`:

### 1. Arquitetura Geral
Visão geral da estrutura de pastas em `core/`, separação de camadas e princípios de design aplicados.

### 2. Modelos de Domínio e Entidades
Detalhamento das árvores de agregação e grafos de objetos:
*   ✅ **Catálogo**: `Product`, `Category`, `CategoryPath`, `CategoryToLayout`, `Manufacturer`, `ManufacturerToLayout`, `Information`, `StockStatus`, `Review`, `Download`, `DownloadDescription`, `DownloadReport`.
*   ✅ **CMS (Blog)**: `Article`, `Topic`.
*   ✅ **Design**: `Banner`, `Theme`, `Translation`.
*   ✅ **SEO**: `SeoUrl`.
*   ✅ **Vendas/Checkout**: `Cart`, `SubscriptionPlan`, `Subscription`, `SubscriptionHistory`, `SubscriptionStatus`, `Order`, `Coupon`, `CouponCategory`, `CouponHistory`, `CouponProduct`, `OrderReturn`, `ReturnAction`, `ReturnReason`.
*   ✅ **Sistema/Localização**: `Setting`, `WeightClass`, `LengthClass`, `LengthClassDescription`, `TaxClass`, `TaxRate`, `TaxRule`, `TaxRateToCustomerGroup`, `Session`, `Startup`, `AddressFormat`, `Cron`, `Event`, `Gdpr`, `Location`, `Notification`, `Upload`.
*   ✅ **Segurança e Auditoria**: `User`, `UserGroup`, `UserLogin`, `OrderOption`, `Api`, `Statistics`, `Gdpr`.
*   ✅ **Marketing**: `Marketing`, `MarketingReport`.
*   ✅ **Clientes**: `Customer`, `CustomerApproval`, `CustomerHistory`, `CustomerLogin`, `CustomerOnline`, `CustomerReward`, `CustomerTransaction`, `Address`, `CustomerGroup`, `CustomField`, `CustomFieldDescription`, `CustomFieldValue`, `CustomFieldValueDescription`, `CustomFieldCustomerGroup`, `Notification`.
*   ✅ **Módulos e Extensões**: `Extension`, `ExtensionInstall`, `ExtensionPath`, `Module`.

### 3. Infraestrutura e Banco de Dados
Explicação técnica sobre o `DataAccessObject` (DAO), abstração de transações aninhadas e segurança com PDO. (Ver Diagrama)

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

---
*Nota: Este índice foi gerado para organizar o conteúdo distribuído. Os arquivos `.md` mencionados acima devem ser mantidos em sincronia com as evoluções do código em `core/`.*

