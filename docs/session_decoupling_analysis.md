# 🔒 Desacoplamento do Sistema de Sessões (Alpha Engine)

Este documento registra a resolução arquitetural para as Sessões (`Session`), que foram totalmente desacopladas da engine do OpenCart e agora rodam de forma nativa e isolada no core da **Alpha Engine**.

---

## 🔍 Contexto Histórico e Limitação Anterior

No ecossistema antigo do OpenCart, as sessões dependiam de adaptadores procedurais (drivers sob `system/library/session/`) e eram acopladas ao runtime global via `system/framework.php`. 
Embora a Alpha Engine atuasse como uma ponte no banco de dados (`db.php`), o controle do ciclo de vida ainda passava pelas bibliotecas antigas, mantendo débitos técnicos como vulnerabilidade a sessões gordas/corrompidas e falta de tipagem rigorosa.

Com a nova decisão estratégica de abandonar a engine antiga, a arquitetura migrou definitivamente para uma solução standalone.

---

## 🛠️ A Resolução: Substituição Integral e Controle Nativo (Desacoplamento Total)

Adotou-se a estratégia de **Desacoplamento Total**, removendo toda e qualquer intermediação das bibliotecas legadas e implementando a manipulação de sessões diretamente no bootstrap da Alpha Engine.

### Componentes da Arquitetura Standalone de Sessão:

1. **Classe `AlphaSession` (`core/Support/Session/AlphaSession.php`)**:
   * Substitui inteiramente a classe de sessão do OpenCart.
   * Implementa métodos fortemente tipados para manipulação de dados em sessão (`set`, `get`, `has`, `remove`, `clear`).
   * Gerencia de forma nativa a criação e leitura do cookie de sessão no navegador.

2. **Repositório e Persistência (`SessionRepository` e `SessionMapper`)**:
   * O ciclo de vida da sessão (leitura, gravação, expiração e garbage collection) é coordenado pelo `SessionRepository` que acessa diretamente o banco via `SessionMapper` no DAO da Alpha Engine.
   * **Auditoria de Payload (Failsafe)**: O Mapper executa checagem de tamanho atômica. Se um registro for maior que 5MB (sinal de corrupção ou ataque), ele é excluído de imediato e uma sessão nova é provida, blindando o servidor contra estouros de memória (OOM).

3. **Bootstrap e Ciclo de Inicialização**:
   * O bootstrap nativo da Alpha Engine (`public_html/index.php`) inicializa a `SessionRepository` e registra a classe `AlphaSessionHandler` como manipulador de sessão do PHP nativo via `session_set_save_handler($sessionHandler, true)`.
   * As requisições HTTP, rotas, controladores e middlewares do sistema consomem a sessão padrão do PHP nativo (`$_SESSION`), assegurando que nenhum arquivo de driver legado em `system/library/session/` seja executado.
   * A injeção automática de `SessionRepository` foi vinculada ao contêiner `AppContainer`.

---

## 💡 Benefícios Arquiteturais Obtidos

* **Conformidade de Padrão (PSR-7 / Native Session)**: A utilização do `AlphaSessionHandler` de acordo com a `SessionHandlerInterface` nativa do PHP permite usar recursos de sessão nativos sem acoplamento procedimental ou proprietário.
* **Segurança e Estabilidade**: O fim do acoplamento evita que queries não bufferizadas corrompam o estado da aplicação no encerramento da execução.
* **Isolamento de Runtime**: O e-commerce roda de forma independente, sem risco de efeitos colaterais provocados por arquivos de drivers procedurais antigos do OpenCart.
* **Prontidão para Novos Drivers (Cache/Redis)**: A persistência do repositório pode ser facilmente configurada para rodar em Redis ou em arquivos usando as classes utilitárias de cache da Alpha Engine (`CacheStrategyInterface`), sem alterar nenhuma linha de código dos controladores ou da aplicação principal.
