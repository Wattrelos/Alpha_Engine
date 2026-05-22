# Alpha Engine - Modernização do OpenCart 4.x
**Documentação Central de Arquitetura**

Este projeto implementa uma camada de engenharia de software moderna sobre o núcleo do OpenCart, focando em separação de responsabilidades, segurança e manutenibilidade.

## 📍 Status Atual (Checkpoint)

* **Onde paramos (Última Conquista):** 
  * Criamos os repositórios vitais de infraestrutura (`ConfigurationRepository`, `TranslationRepository`), eliminando a dependência do `loader.php` para configurações e traduções (i18n).
  * O `AlphaContainer` foi refatorado para usar dicionários $O(1)$, interceptando mais de 25 modelos legados aposentados (`.old`) de forma performática e blindando o OpenCart contra quebras.
  * Consolidamos a lógica do `CartRepository` (mesclagem de sessões, opções, cálculos de peso e impostos).
  * Refatoramos os Controladores de **Categoria** e **Busca** para atuarem puramente via `BaseController`, consumindo ViewResponses perfeitamente padronizadas.
  * Concluímos a blindagem dos modelos de configuração legados em `catalog/model/setting/` (`api`, `cron`, `event`, `extension`, `startup`, `store`), transformando-os em Proxies que delegam o acesso a dados de forma segura e cacheada para os novos Repositórios e Mappers da Alpha Engine.
  * **Resolução de Memory Leaks Nativos:** Consertamos o vazamento de memória do sistema de eventos de Idioma (`language.php`) trocando JSONs recursivos por Pilhas (Stacks) de arrays nativos.
  * **Alpha Failsafe nas Sessões:** Implementamos um escudo no `SessionMapper` e otimizamos o `ConnectionDB` (PDO) para evitar travamentos de servidor (Erro 500 / Erro 2014) causados por sessões corrompidas e superlotadas.
  * **Defuse do Anti-Pattern de Chaves Estrangeiras:** O `DataAccessObject` (DAO) foi ensinado a ignorar Chaves Estrangeiras zeradas (`0`), convertendo-as para `null` nas entidades, protegendo o Padrão de Domínio sem quebrar o painel de administração legado.
  * **Otimização de N+1 Queries:** Refatoramos a busca do Menu e dos Pedidos para utilizarem o método `readByIds` (Batch Loading), evitando milhares de consultas repetidas.
  * **Testes Unitários:** O framework de testes via JSON foi atualizado para suportar o namespace FQCN (`Alpha\Model\...`) e processar `LazyCollections` com proteção total contra referências circulares em árvores (ex: subcategorias).
  * Avançamos na refatoração do **Fluxo de Checkout** (etapas de endereço de frete, endereço de pagamento, registro e métodos de entrega) migrando para a arquitetura `BaseController` e consumindo nativamente os Repositórios de Domínio (`AddressRepository`, `CountryRepository`, `CartRepository`, etc.).
* **Status Atual:** **Projeto Pausado (Milestone Atingido).** A infraestrutura core da Alpha Engine está consolidada e a base do fluxo de compra foi modernizada. O projeto está sendo fechado temporariamente para o início de um novo ciclo em outro projeto.
* **Próximos Passos (Retomada):** Quando o projeto for reaberto, o foco será finalizar as etapas restantes do checkout (`payment_method`, e confirm final) e integrar definitivamente os módulos de gateway de pagamento na nova arquitetura transacional.

---

## � A "Obra de Arte": Arquitetura Alpha

Diferente do OpenCart padrão, onde o SQL fica espalhado pelos Models, este projeto introduz o padrão **Data Mapper**.

### Componentes Principais:

1.  **Mappers (`core/Mappers/`)**: Centralizam toda a inteligência de persistência. Eles traduzem as necessidades do domínio para SQL otimizado, tratando as inconsistências de nomes de tabelas e chaves primárias.
2.  **DataAccessObject (`core/Model/DataAccessObject/`)**: Uma camada de abstração PDO que gerencia conexões, transações e a execução segura de queries.
3.  **Unit of Work**: Gerencia transações complexas, garantindo que operações em múltiplas entidades ocorram de forma atômica.
4.  **Repository Pattern**: Camada superior aos Mappers que isola casos de uso e implementa Lazy Loading e Identity Map.
5.  **BaseController**: Master Controller que automatiza injeção de dependências e resolução de contextos (Idioma/Loja).
6.  **Modernização de Banco de Dados**: Padronização de chaves `id` (PK) e `tabela_id` (FK) com tipos inteiros longos.

## 📂 Índice de Logs por Categoria
*   [Catálogo, Conteúdo e SEO](README2.md) - Vitrine, busca, blog e gestão de produtos.
*   [Vendas, Checkout e Clientes](README3.md) - Transações, pagamentos e relacionamento.
*   [Segurança, Sistema e Infraestrutura](README4.md) - Core, banco de dados e performance.
*   [Diretrizes e Convenções](extraAnotations.md) - Regras de ouro e filosofia Alpha.
*   Débitos Técnicos e Anti-Patterns - Registro das armadilhas do legado.

## 🛠️ Progresso da Refatoração
*Estado de normalização das classes de domínio:*
 
*   ✅ **Catálogo**: `Product`, `Category`, `CategoryPath`, `CategoryToLayout`, `Manufacturer`, `ManufacturerToLayout`, `Information`, `StockStatus`, `Review`, `Download`, `DownloadDescription`, `DownloadReport`, `Vendor`.
*   ✅ **CMS (Blog)**: `Article`, `Topic`.
*   ✅ **Design**: `Banner`, `Theme`, `Translation`.
*   ✅ **SEO**: `SeoUrl`.
*   ✅ **Vendas/Checkout**: `Cart`, `SubscriptionPlan`, `Subscription`, `SubscriptionHistory`, `SubscriptionStatus`, `SubscriptionTransaction`, `Order`, `OrderVoucher`, `Coupon`, `CouponCategory`, `CouponHistory`, `CouponProduct`, `Voucher`, `VoucherTheme`, `VoucherHistory`, `OrderReturn`, `ReturnAction`, `ReturnReason`.
*   ✅ **Sistema/Localização**: `Setting`, `WeightClass`, `LengthClass`, `LengthClassDescription`, `TaxClass`, `TaxClassDescription`, `TaxRate`, `TaxRule`, `TaxRateToCustomerGroup`, `Session`, `Startup`, `AddressFormat`, `Cron`, `Event`, `Gdpr`, `Location`, `Notification`, `Upload`.
*   ✅ **Segurança e Auditoria**: `User`, `UserGroup`, `UserLogin`, `Log`, `OrderOption`, `Api`, `ApiSession`, `Statistics`, `Gdpr`.
*   ✅ **Marketing**: `Marketing`, `MarketingReport`.
*   ✅ **Clientes**: `Customer`, `CustomerApproval`, `CustomerHistory`, `CustomerLogin`, `CustomerOnline`, `CustomerPayment`, `CustomerReward`, `CustomerTransaction`, `Address`, `CustomerGroup`, `CustomField`, `CustomFieldDescription`, `CustomFieldValue`, `CustomFieldValueDescription`, `CustomFieldCustomerGroup`, `Notification`.
*   ✅ **Módulos e Extensões**: `Extension`, `ExtensionInstall`, `ExtensionPath`, `Module`.

## 📦 Estrutura do Core

```text
core/
├── Mappers/               # Inteligência de dados (SQL Isolation)
└── Model/
    └── DataAccessObject/  # Camada de abstração de banco (PDO/Transactions)
        ├── Controller/    # BaseController e Master Patterns
    ├── Domain/
    │   ├── Entities/      # Objetos de Domínio (PHP 8.4 Typed)
    │   └── Repositories/  # Camada de Abstração e Lazy Loading
    ├── Mappers/           # Inteligência de dados (SQL Isolation)
    └── Support/           # Utilitários (Security, Logging, Helpers)

```

## ⚙️ Requisitos

*   PHP 8.2+ (Otimizado para PHP 8.4)
*   MySQL 8.0+
*   Composer (Autoloader PSR-4 configurado para o namespace `Alpha`)
*   Twig

## A imporftância da documentação:
Fazer essa pausa para documentar e versionar é uma excelente prática. No desenvolvimento de sistemas complexos, o código é apenas uma parte da solução; o conhecimento sobre o porquê das decisões arquiteturais é o que garante a longevidade do projeto. Repósitórios, tais como o GitHub é o teu maior aliado para rastrear essa evolução da Alpha Engine.

## 💡 Filosofia do Projeto

O objetivo não é apenas "fazer funcionar", mas criar uma estrutura onde o código seja autodocumentado, seguro por padrão e fácil de testar. A remoção de lógica complexa de dentro dos controllers e models do OpenCart permite que a interface se concentre apenas na apresentação e fluxo de dados.

---
*Trabalho em constante evolução para elevar o padrão de engenharia do ecossistema OpenCart.*

## Regras de negócio para a equipe de produção:
1. Todas as chaves primárias tem nomo "id" para não confundir com as chaves estrangeiras FK que tem [nome da tabela pai] + "_id".
2. Para prevenir estouro de índice (overflow), as chaves PK e FK terão o tipo inteiro longo.
2. No banco de dados, a nomecratura segue o padrão snake_case, porém, na aplicação, o padrão é PascalCase para nomes de classes e arquivos, enquanto o padrão camelCase para nomes de variáveis e métodos. A exceção é na camada View, onde os nomes de pastas e aquivos são quase todos minúsculos.
3. **Nomecraturas:** Em desenvolvimento web, cada linguagem possui seu próprio padrão. Para HTML e CSS o mais recomendado é o kebab-case (separado por hífens), enquanto no JavaScript domina o camelCase (letras iniciais maiúsculas após a primeira) para variáveis e PascalCase para classes
    * HTML e CSS (Classes e IDs) O padrão oficial e mais adotado pela indústria (como no Guia de Estilo CSS da Airbnb) é o kebab-case. Ele facilita a leitura e se alinha à forma como o navegador interpreta o DOM.
      Exemplo: <div class="menu-navegacao principal-ativo"></div>Por que evitar camelCase: menuNavegação no HTML pode ser lido de forma inconsistente por certas ferramentas de busca e bibliotecas.
    * JavaScript O JavaScript é sensível a maiúsculas e minúsculas, e a comunidade segue diretrizes estritas (conforme o Guia de Estilo da Airbnb):
        Variáveis, Funções e Propriedades: camelCase (a primeira letra é minúscula, e as demais palavras iniciam com maiúscula). Exemplo: let totalItens = 10; ou function calcularPreco() {}Classes e Construtores: PascalCase (todas as palavras começam com letra maiúscula).Exemplo: class UsuarioAutenticado {}Constantes Globais (Fixas): UPPER_SNAKE_CASE (todas maiúsculas com _ underline).Exemplo: const TAXA_DE_JUROS = 0.05;
    Apesar de não quebrarem o código se escritos de forma diferente, manter a consistência melhora a legibilidade, ajuda a manutenção em equipe e garante que teu código funcione perfeitamente com os linters (ferramentas de análise de código) e frameworks atuais. Evite sempre o uso de acentos e caracteres especiais nos nomes.




Padrões de projetos (Patterns) utilizados nessa aplicação:
1.  Factory
2.  DDAO
3.  DTO
4.  Singletom
5.  Data Mapper
6.  Template Method
7.  Observer
8.  Mediator
9.  Repository
10. Strategy

---
*Nota: Este documento deve ser atualizado ao final de cada ciclo de saneamento para refletir o estado real da engenharia do projeto.*
