# Alpha Engine - Segurança, Sistema e Infraestrutura
**Módulo de Core e Auditoria**

Este log documenta a fundação técnica, segurança de acesso e utilitários globais do motor Alpha.

## 🔐 [SECURITY] Sessões e Acesso

1.  **Surrogate Keys (Web & API)**: Migração para PKs numéricas (`id`) e isolamento de hashes em `token_session`.
2.  **API Session Unification**: Isola o identificador de banco do identificador de transporte (Token), com índices de alta performance ($O(1)$).
3.  **GDPR Compliance**: Ciclo de vida automatizado para expiração de dados e deleção atômica de contas de clientes.
4.  **Request Metadata Security**: Implementação do `RequestHelper` para prevenir ataques de *Open Redirect* e validar origem de tráfego.

## ⚙️ [SYSTEM] Localização e Regras Fiscais

1.  **Taxation Engine**: Mapeamento de regras fiscais complexas (Geozones + CustomerGroups) com tipagem float matematicamente infalível.

2.  **Currency Refresh**: Automação de atualização de taxas de câmbio via Alpha Engine.
3.  **Medidas Físicas**: Normalização de `WeightClass` e `LengthClass` para cálculos de frete volumétrico de alta precisão.
4.  **Taxation Module Modernization**: Implementação completa das entidades `TaxClass`, `TaxRate`, `TaxRule` e seus respectivos Mappers/Repositories, garantindo cálculos fiscais precisos e aplicação de regras baseada em prioridade.
5.  **LengthClass Conversion**: Implementação de um repositório dedicado para conversão precisa entre diferentes unidades de comprimento.
6.  **LengthClass Modernization**: Repositório refatorado para implementar `BaseRepositoryInterface`, garantindo acesso tipado e desacoplado às unidades de medida de comprimento no ecossistema Alpha.

## 🏗️ [INFRA] Arquitetura e Performance

1.  **BaseController (Master Pattern)**: Centralização de injeção de mappers, repositórios e resolução de idiomas, neutralizando o `loader.php`.
2.  **DataAccessObject Advanced**:
    *   **Identity Map**: Cache de instâncias em memória para evitar consultas redundantes.
    *   **Lazy Loading**: Implementação de `LazyCollection` via Closures para adiar a execução de queries pesadas.
    *   **Batch SEO Loading**: Carregamento em lote de URLs amigáveis para vitrines.
    *   **PSR-16 Cache Strategy**: Implementação do contrato abstrato `CacheStrategyInterface` inspirado na PSR-16, acompanhado do motor de cache robusto via *Filesystem* para armazenamento serializado.
    *   **Cache-Aware Repositories**: Infraestrutura base preparada para injeção de cache em todos os repositórios de domínio.
    *   **Domain Entity Caching**: Implementação de cache de longa duração para entidades de localização e sistema (Language, Currency, TaxClass, TaxRate e TaxRule).
3.  **AlphaContainer (Legacy Bridge)**: Interceptor de modelos legados que redireciona chamadas para Repositórios modernos, permitindo migração progressiva.
    *   **$O(1)$ Dictionary Mapping**: Resolução de mais de 25 modelos interceptados via arrays estáticos em milissegundos.
4.  **Observabilidade**: Sistema de logs de depreciação com rastreamento de IP/Rota para erradicação de débito técnico.
5.  **Configuration & I18N**: `ConfigurationRepository` e `TranslationRepository` isolam o sistema de configurações e traduções do motor legado, operando 100% via memória injetada no Registry.
6.  **Automação e Auditoria ORM**: Coleção de scripts utilitários (ex: *Detector de Zumbis* e *Renomeador de Referências*) que garantem alinhamento estrutural perfeito entre Entidades PHP 8.4 e colunas do Banco de Dados via Reflection API.


## 🖼️ [DESIGN] Motor de Renderização

1.  **Layout Resolution Autonomy**: Resolução de design (Layout/Módulos) 100% via Alpha Engine, encerrando dependência do motor legado na estrutura global.
2.  **Unified Driver Sessão**: Conversão do driver nativo do OpenCart em um Proxy para o `SessionRepository`.

## ️ Vitórias de Infraestrutura
1.  **Identity Map no DAO**: Cache de instâncias em memória evita que a mesma entidade seja lida do banco mais de uma vez por requisição.
2.  **Lazy Loading Strategy**: Implementação de `LazyCollection` via Closures, adiando queries SQL pesadas para o momento do acesso real aos dados.
3.  **BaseController Mastery**: Centralização da injeção do `MapperFactory`, tornando o `$this->load->model` legado opcional.
4.  **Legacy Model Decommissioning**: 11 modelos legados renomeados para `.old`, provando a autoridade da Alpha Engine.
5.  **Depreciation Logging**: `CompatibilityLogger` rastreia tentativas de chamadas a modelos desativados com IP e Rota.
6.  **Request Metadata Security**: `RequestHelper` centraliza validação de referers e previne ataques de Open Redirect.
7.  **Refatoração do SessionRepository**: Implementação dos métodos de leitura/escrita delegando ao Mapper e limpeza de redundâncias.
8.  **Refatoração do LayoutRepository**: Adoção do padrão Proxy via `__get` para acesso facilitado a serviços do Registry e resolução de módulos.
9.  **Padronização de Repositórios**: Implementação do método `findAll` na `BaseRepositoryInterface` e `AbstractRepository`, permitindo a remoção de código redundante em toda a camada de domínio.
10. **Infraestrutura de Lazy Loading**: Implementação de `LazyCollection` no core da Alpha Engine para suportar hidratação sob demanda de coleções pesadas.
11. **Domínio de Sessão Modernizado**: Implementação da entidade `Session` e `SessionMapper`, consolidando a transição para Surrogate Keys e eliminando de vez o SQL legado do driver de sessão.
12. **Modernização de Sessão de API**: Refatoração do `ApiSessionRepository` e seu Mapper, isolando o token de transporte da PK numérica e padronizando a segurança de acesso remoto.
13. **PSR-16 Cache**: Padronização da interface de cache para viabilizar integração transparente de novos drivers em memória (Redis/Memcached) no futuro, já consolidado com driver nativo em arquivos.
14. **Auditoria ORM**: Erradicação da possibilidade de "Entidades Zumbis" e atributos órfãos através de varredura inteligente, assegurando 100% de estabilidade e mapeamento fidedigno.

### 💡 Insights de Core
*   A inicialização de propriedades com `0` ou `''` nas entidades é vital para prevenir o erro de `uninitialized property` do PHP 8.4.
*   A blindagem no `CollectionToArrayConverter` evita que coleções esquecidas quebrem o motor de renderização.
*   O `AlphaContainer` atua como interceptor, entregando Repositórios modernos em chamadas de modelos antigos para migração progressiva.

---
*A fundação sólida que permite a longevidade e escalabilidade da Alpha Engine.*
