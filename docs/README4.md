# Alpha Engine - Segurança, Sistema e Infraestrutura
**Módulo de Core, Segurança e Auditoria**

Este documento registra os aspectos de segurança, gerenciamento do sistema e a infraestrutura de backend da Alpha Engine em sua arquitetura standalone.

---

## 🔐 [SECURITY] Sessões, Acesso e Metadados

1.  **Surrogate Keys (Identidade de Sessão)**: Utilização de PKs numéricas (`id`) internas nas tabelas do banco de dados, mantendo o tráfego externo isolado por meio de hashes seguras em `token_session`.
2.  **API Session Unification**: Centralização e isolamento do identificador de transporte (Token), com indexação e verificação rápida de credenciais.
3.  **GDPR Compliance**: Métodos nativos de expiração e eliminação atômica de contas e dados pessoais diretamente na camada do domínio.
4.  **Segurança de Metadados**: O `RequestHelper` valida a origem do tráfego e sanitiza URLs de redirecionamento, erradicando falhas comuns de *Open Redirect*.

---

## ⚙️ [SYSTEM] Localização, Regras Fiscais e Medidas

1.  **Motor de Taxação**: Processamento estrito de regras de impostos baseadas em zonas geográficas e grupos de clientes, utilizando valores flutuantes tipados para evitar erros de precisão.
2.  **Conversão de Moedas (Currency)**: O `CurrencyMapper` e `CurrencyRepository` gerenciam o cache e atualização ativa de taxas cambiais direto no runtime da Alpha Engine, removendo queries acopladas.
3.  **Medidas Físicas**: Unificação e normalização de `WeightClass` e `LengthClass` para cálculos logísticos e de frete de alta precisão.
4.  **Taxation Module Standalone**: Implementação das entidades `TaxClass`, `TaxRate` e `TaxRule` de forma isolada, garantindo cálculos e priorizações tributárias diretamente nas regras de domínio.

---

## 🏗️ [INFRA] Arquitetura Standalone e Performance

1.  **BaseController (Master Pattern)**: Centralização de injeção de dependências (mappers e repositórios) e resolução de internacionalização de forma nativa, extinguindo o carregador de arquivos procedural legadodo código legado (`loader.php`).
2.  **DataAccessObject Advanced**:
    *   **Identity Map**: Repositórios guardam referências em memória das entidades carregadas, impedindo idas repetidas ao banco de dados na mesma requisição.
    *   **Lazy Loading**: Uso do `LazyCollection` via Closures e Proxies PHP nativos para postergar o processamento de relações complexas até seu uso na view.
    *   **Batch Loading**: Consultas como listagem de categorias do menu e SEO amigável utilizam buscas em lote (`readByIds`), contornando o gargalo de consultas em loop.
3.  **Estratégia de Cache PSR-16**: Interface de cache (`CacheStrategyInterface`) implementada via Filesystem, permitindo fácil substituição por Redis/Memcached para cachear entidades estáticas (idiomas, moedas, configurações).
4.  **Gerenciamento do Ciclo de Vida da Sessão**: O runtime do sistema inicializa a sessão utilizando a classe `AlphaSession` diretamente pelo bootstrap da Alpha Engine, eliminando a dependência da biblioteca de sessão legadado código legado e de seus arquivos de driver antigos.
5.  **Auditoria ORM**: Ferramentas de verificação estática que eliminam a presença de atributos órfãos e entidades não mapeadas, mantendo o alinhamento total entre o PHP 8.4 e o banco de dados.

---

## 🏆 Vitórias da Engenharia de Infraestrutura

1.  **Fim da Execução Legada**: Bootstrap e Roteador pertencem inteiramente à Alpha Engine, inicializando controladores e views do zero e limpando as rotas originaisdo código legado.
2.  **Widget Isolation**: A camada visual desenhada em Twig separa componentes e fragmentos, prevenindo loops de memória e WSOD (White Screen of Death) no carregamento de cabeçalhos e rodapés.
3.  **Deprecation Logs**: Monitoramento constante via `CompatibilityLogger` que sinaliza chamadas a recursos procedurais antigos para eliminação definitiva do código.
4.  **Descomissionamento de Modelos**: Desativação total de modelos herdados (renomeados para `.old`), garantindo que o banco de dados seja acessado exclusivamente pelos Mappers e Repositórios da Alpha Engine.
