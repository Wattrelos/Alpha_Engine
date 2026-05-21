# Alpha Engine - Diretrizes e Filosofia

Este arquivo preserva a cultura de engenharia e as regras de negócio vitais para a equipe de produção.

## ⚖️ Regras de Negócio de Persistência
1. **Chaves Primárias**: Devem ser nomeadas apenas como `id` para não confundir com chaves estrangeiras (FK), que seguem o padrão `[tabela_pai]_id`.
2. **Prevenção de Overflow**: Todas as chaves PK e FK devem utilizar o tipo inteiro longo (BIGINT).
3. **Snake vs Pascal**:
    *   **Banco de Dados**: Tudo em `snake_case`.
    *   **Aplicação (PHP)**: Classes e Arquivos em `PascalCase`. Variáveis e Métodos em `camelCase`.
    *   **View (Pastas/Arquivos)**: Tudo em minúsculo para compatibilidade total com sistemas de arquivos.

## 🏷️ Convenções de Nomenclatura (Frontend)
*  **HTML e CSS**: Seguir o padrão `kebab-case` (ex: `menu-navegacao-principal`). Evitar `camelCase` no HTML para garantir consistência em ferramentas de busca e bibliotecas legadas.
*  **JavaScript**:
    *   Variáveis/Funções: `camelCase`.
    *   Classes/Construtores: `PascalCase`.
    *   Constantes Globais: `UPPER_SNAKE_CASE`.

## 🛡️ Estratégia de Migração (Legacy Bridge)
Adotamos a técnica de **Esvaziamento Progressivo**:
1.  Modelos legados são esvaziados e transformados em pontes que preferencialmente invocam os **Repositórios** (e, na ausência destes, os Mappers Alpha). O fluxo correto é: `Controller -> Repository -> Mapper -> DAO`.
2.  Controladores migram para a `BaseController`, eliminando o overhead do `loader.php`.
3.  Arquivos homologados são renomeados para `.old` antes da exclusão definitiva.

## 🧩 Padrões de Projeto Aplicados
A Alpha Engine não é apenas código, é uma aplicação rigorosa de design patterns:
1. **Factory**: Instanciação centralizada de Mappers e Repositórios.
2. **Data Mapper**: Isolamento total do SQL dentro dos Mappers.
3. **DTO (Data Transfer Object)**: Transporte tipado de dados entre Repositório e View.
4. **Observer**: Desacoplamento de efeitos colaterais (ex: disparar e-mail ao salvar pedido).
5. **Unit of Work**: Garantia de atomicidade em operações multi-entidade.
6. **Bridge**: Manutenção da compatibilidade com o núcleo do OpenCart durante a transição.
7. **Repository**: Camada de abstração sobre os Mappers para gerenciar agregados e lógica de domínio.
8. **Snapshot Pattern**: "Congela" os dados no momento da transação para integridade histórica.
9. **Identity Map**: (Via DAO) Garante que a mesma entidade não seja carregada múltiplas vezes na mesma requisição.

## 💡 Filosofia da Documentação
O código é apenas uma parte da solução. O conhecimento sobre **por que** uma decisão arquitetural foi tomada garante a longevidade do projeto. 
*   Use o Javadoc-style para documentar métodos complexos.
*   Se uma propriedade é `float`, explique se ela tem impacto fiscal ou logístico.
*   Autoexplicação é o objetivo: se um engenheiro abrir a classe `Product` daqui a 6 meses, a tipagem deve servir como o primeiro manual de instrução.

---

## 🔄 Status de Saneamento (Alpha Engine)

### 🏗️ Em Andamento
*   **Repository Pattern Standard**: Estamos formalizando a `BaseRepositoryInterface` e a `AbstractRepository`.
    *   ✅ `LanguageRepository`: Refatorado para implementar a interface e utilizar o `LanguageMapper`. Agora suporta `find`, `findAll`, `findBy`, `findOneBy` e o método especializado `getByCode`.
    *   ✅ `ProductRepository`: Refatorado para implementar a interface e utilizar o `ProductMapper`. Implementação completa dos métodos de busca do contrato.
    *   ✅ `CurrencyRepository`: Criado e refatorado para implementar a interface e utilizar o `CurrencyMapper`.
    *   ✅ `ManufacturerRepository`: Refatorado para implementar a interface e utilizar o `ManufacturerMapper`.
    *   ✅ `CategoryRepository`: Criado e refatorado para implementar a interface, com injeção de `CacheStrategyInterface` para otimizar o carregamento da árvore de menus via cache de longa duração.
    *   ✅ `LayoutRepository`: Refatorado para implementar a interface, utilizar o `LayoutMapper` e injeção de `CacheStrategyInterface` para otimizar a resolução de layouts e rotas em toda a loja.
    *   ✅ `TaxClassRepository`: Implementação da lógica de cache nos métodos de busca (`find`, `findAll`, `findOneBy`).
    *   ✅ `TaxRateRepository`: Implementação da lógica de cache e padronização da interface.
    *   ✅ `TaxRuleRepository`: Implementação da lógica de cache e padronização da interface.
    *   ✅ `LengthClassRepository`: Refatorado para o padrão BaseRepositoryInterface utilizando o LengthClassMapper.
    *   ✅ `WeightClassRepository`: Refatorado para o padrão BaseRepositoryInterface utilizando o WeightClassMapper e suporte a cache de conversão.
    *   ✅ `OrderMapper`: Refatorado para persistência atômica de Cupons (Snapshot Pattern) via UnitOfWork.
    *   ✅ `OrderRepository`: Criado/Refatorado para encapsular o `OrderMapper` e abstrair a orquestração do `UnitOfWork` (transações e estoque) nos ciclos de venda.
    *   ✅ `CartRepository`: Criado para encapsular o `CartMapper`, abstraindo a manipulação do carrinho e resolvendo automaticamente a transição do `session_id` para `customer_id`.
    *   ✅ `CouponRepository`: Implementação com validação de regras de domínio e integração com o histórico de uso (Snapshot).
    *   ✅ `CouponMapper`: Implementado para suportar persistência via DAO e consultas de validade.
    *   ✅ `VoucherRepository`: Implementação para gestão de cartões-presente com cálculo rigoroso de saldo restante via histórico (Snapshot).
    *   ✅ `CustomerRepository`: Refatorado para encapsular o `CustomerMapper`, centralizando autenticação, re-hash de senhas modernas, proteção contra força bruta e validações de registro.
    *   ✅ `AddressRepository`: Criado para encapsular o `AddressMapper`, centralizando a busca segura por proprietário (prevenindo IDOR) e resolvendo dinamicamente a formatação do layout postal (`AddressFormat`).
    *   ✅ `SeoUrlRepository`: Criado para encapsular o `SeoUrlMapper`, centralizando o primeCache e a resolução de URLs amigáveis, com injeção de `CacheStrategyInterface` para otimização extrema.
    *   ✅ `GeoZoneRepository`: Criado em conjunto com o `GeoZoneMapper` para encapsular a lógica de validação de endereços dentro de zonas geográficas e aliviar chamadas dinâmicas nos módulos de frete e impostos.
* **Camada de Serviços (Shipping & Business Logic)**:
    *   ✅ `FlatRateShippingService`: Modernização do cálculo de frete fixo com suporte a injeção de repositórios Alpha para normalização de medidas.
    *   ✅ `FreeShippingService`: Implementação moderna do cálculo de frete grátis, validando o valor mínimo exigido no carrinho e abstraindo as chamadas de configuração/linguagem.
    *   ✅ `WeightBasedShippingService`: Implementação moderna do frete por peso volumétrico, garantindo a normalização da unidade de medida do carrinho antes de validar as faixas de preço (rates).
*   **Camada de Cache de Entidades**:
    *   ✅ `CacheStrategyInterface`: Finalizada implementação do contrato para padronizar drivers de cache (Redis/Filesystem) alinhado aos conceitos da PSR-16.
    *   ✅ `FilesystemCacheStrategy`: Implementação robusta baseada em arquivos com controle de expiração (TTL), serialização segura e `LOCK_EX` para prevenção de concorrência.
*   **Camada de Controladores (Master Pattern)**:
    *   ✅ `BaseController`: Implementação da classe abstrata em `Alpha\Controller`, automatizando a injeção de dependências (Repositórios e Mappers) e padronizando respostas JSON para API e Frontend.
    *   ✅ `Cart Controllers`: Migração de `api/cart.php` e `checkout/cart.php` para utilizar a `BaseController`, injeção do `CartRepository` e padronização das saídas JSON.
    *   ✅ `Product Controller`: Migração do `product/product.php` para utilizar a `BaseController`, consumindo as instâncias da `MapperFactory` em vez de instanciar os Mappers manualmente.
    *   ✅ `Checkout Controller`: Migração do `checkout/checkout.php` para utilizar a `BaseController`, aplicando o `CartRepository` para gerenciamento seguro da sessão de checkout.
    *   ✅ `Home Controller`: Migração do `common/home.php` para utilizar a `BaseController` com o super método `$this->render()`, injetando automaticamente cabeçalhos e rodapés, e corrigindo montagem da DTO.
