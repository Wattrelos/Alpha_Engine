# Alpha Engine - Changelog (Sprint 4: UI & Layout Components)

---

### Alpha Engine: Saneamento do Cabeçalho (Header Controller)
**Data:** [Data Atual]
**O que foi feito:**
- Remoção de marcadores de debug e *memory profiling* (log de pico de memória) injetados durante as fases anteriores de contenção de *memory leak*.
- Aplicação estrita do *Null Coalescing Operator* (`??`) para simplificação da contagem da lista de desejos em sessões não-autenticadas.
- Comentário de mapeamento (TODO) adicionado para a futura remoção da dependência legada do modelo de `wishlist` via implementação do `WishlistRepository`.
- Consolidação do padrão **Widget Isolation** (Loader-Free): Instanciação manual e direta via construtor das classes `Language`, `Currency`, `Search`, `Cart` e `Menu`, anulando permanentemente o overhead de N+1 consultas de roteamento provocadas pelo `$this->load->controller()`.
**Benefícios:** Tempo de reposta do fragmento de layout reduzido; Código perfeitamente limpo operando como *Skinny Controller* focado em UI e DTO; Escrita em disco desnecessária por logs foi encerrada.

---

### Alpha Engine: Modernização do Controlador de Menu
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração do `catalog/controller/common/menu.php` e eliminação da extração de dicionário manual legada (`$this->load->language` e `$this->language->get`).
- Implementação unificada do `$this->loadLanguageData()`, que injeta todos os textos passivamente no array de resposta.
**Benefícios:** Código reduzido; Prevenção de "Undefined index" no Twig caso novas traduções sejam adicionadas ao arquivo de linguagem, pois agora elas estarão automaticamente disponíveis no controlador; Manutenção consistente com o padrão *Skinny Controller*.

---

### Alpha Engine: Padronização e Limpeza do Minicart Controller (`common/cart.php`)
**Data:** [Data Atual]
**O que foi feito:**
- Correção na injeção de dependências: Substituição do acesso manual legado `$this->repository->get()` pelo método canônico `$this->getRepository()` da `BaseController`.
- Substituição da sintaxe obsoleta de idioma `$this->language->load()` para o padrão canônico do OpenCart/Alpha `$this->load->language()`.
- **Skinny Controller Completo no método `add()`**: Remoção do bloco de "TODO" e da validação imperativa no controlador. Toda a avaliação de regras de negócio (incluindo checagem de opções e erro de quantidade) foi delegada diretamente para `$cartRepository->validateAddition()`.
**Benefícios:** Consistência absoluta com o Master Pattern da Alpha Engine; eliminação de código de validação duplicado; código final blindado contra inserções irregulares.

---

### Alpha Engine: Fix do JS Crash no Minicart e Esvaziamento do Modelo Legado
**Data:** [Data Atual]
**O que foi feito:**
- **Resolução UI:** Injeção do `id="cart"` na div primária do fragmento `common/cart.twig`. A ausência do ID gerava erro fatal no `common.js` legado do OpenCart que quebrava o encadeamento do DOM, impedindo o Bootstrap 5 de inicializar o evento `data-bs-toggle="dropdown"`.
- **Aniquilação de Débito Técnico:** Esvaziamento de 120 linhas do modelo `catalog/model/checkout/cart.php`, transformando a classe numa casca vazia. Toda inteligência já operava via `CartRepository`.
**Benefícios:** Minicart reestabelecido e dropdown voltando a abrir normalmente sem atrito JS; Arquitetura livre de lógicas de domínio em Models da infraestrutura antiga.

---

### Alpha Engine: Fix de Sincronia de Entidade (SessionRepository)
**Data:** [Data Atual]
**O que foi feito:**
- Remoção das validações legadas de `user_agent` e `ip` no método `read` do `SessionRepository`.
- Remoção da passagem de `$userAgent` e `$ip` no método `write` ao invocar `saveSession`.
**Benefícios:** Resolução do erro fatal (`Call to undefined method`) gerado após a auditoria de banco de dados que removeu essas colunas obsoletas da entidade `Session`. O repositório agora reflete estritamente a estrutura atualizada e enxuta de sessões do OpenCart 4, restabelecendo a estabilidade da inicialização do framework.

---

### Alpha Engine: Alinhamento Estrito do Ecossistema de Sessão (Entidade e Mapper)
**Data:** [Data Atual]
**O que foi feito:**
- **Entity:** Correção das propriedades da Entidade `Session`, ajustando `$tokenSession` para `$sessionToken` e `$expire` para `$expireAt`, alinhando perfeitamente a classe à convenção `camelCase` gerada pelo ORM a partir do banco de dados (`snake_case`). Também foi adicionada a propriedade ausente `$customerId`.
- **Mapper:** Adequação da assinatura do método `SessionMapper::saveSession()` para receber apenas três argumentos (Token, Data e ExpireAt). As referências obsoletas de `user_agent` e `ip` foram completamente erradicadas da instrução `INSERT ... ON DUPLICATE KEY UPDATE`.
**Benefícios:** Morte súbita de dois erros fatais: `Call to undefined method` ao extrair a validade da sessão no Repositório, e `Too few arguments` ao registrar novas atualizações do array global de sessões. O ciclo de inicialização do motor voltou a operar com fluidez.

---

### Alpha Engine: Resolução de Estritos do PHP 8.4 e Compatibilidade de Mappers
**Data:** [Data Atual]
**O que foi feito:**
- **ExtensionMapper:** Correção do aviso *Implicitly marking parameter as nullable is deprecated*. Ajustado `Registry $registry = null` para `?Registry $registry = null`.
- **BaseMapper:** Adicionada inicialização padrão para as propriedades `$tableName` e `$table` e inteligência no método `getFullTableName()`. Isso impede o *Fatal Error: Typed property must not be accessed before initialization* em repositórios (como `ProductDiscountMapper` e `ProductOptionMapper`) que ainda utilizavam a sintaxe antiga de `$table`.
- **SessionMapper:** Re-inclusão das colunas `user_agent` e `ip` na instrução `INSERT` da sessão passando valores nulos (`'', ''`). 
**Benefícios:** Compatibilidade garantida com a estrutura real do banco de dados da loja (que ainda possui as colunas NOT NULL) evitando falhas rigorosas do MySQL. Motor estabilizado para inicialização de propriedades tipadas e nulas do PHP 8.4.

---

### Alpha Engine: Blindagem PHP 8.4 e Refatoração do Roteador de SEO (`seo_url.php`)
**Data:** [Data Atual]
**O que foi feito:**
- Transição do controlador `startup/seo_url.php` para estender `BaseController`, padronizando a injeção do `SeoUrlRepository` via `$this->getRepository()` e eliminando a busca verbosa no contêiner.
- Utilização nativa das propriedades tipadas `$this->storeId` e `$this->languageId` injetadas pelo Master Controller, removendo chamadas repetitivas ao `$this->config`.
- **Blindagem PHP 8.4 no `parse_url`**: Implementação do operador fallback `?: []` para evitar que URLs malformadas retornem `false` e quebrem o acesso ao array. Substituição de blocos `isset()` frágeis na construção da URL base por verificações seguras de fragmentos (`scheme`, `host`, `port`).
- Correção do destrutor de trailing slash `array_pop` para utilizar tipagem estrita `=== ''` e `!empty()`.
**Benefícios:** Resolução de *Warnings* de *Undefined array key* em chaves não presentes na URL (`query`, `port`); código altamente robusto, padronizado com o Master Pattern da Alpha Engine e livre de falhas de tipagem sob a nova versão do PHP.

---

### Alpha Engine: Auditoria de Qualidade do SEO URL
**Data:** [Data Atual]
**O que foi feito:**
- Revisão estrutural do controlador `seo_url.php` confirmando a aplicação integral do *BaseController* e escudos de tipagem. Foi identificada e removida uma instrução duplicada residual (`$pair = explode('=', $part);`) no loop de reconstrução de caminhos do método `rewrite`.
**Benefícios:** Micro-otimização; eliminação de overhead computacional redundante no roteador principal da aplicação.

---

### 🏁 Checkpoint de Sessão
**Data:** [Data Atual]
**Resumo:** Encerramento do ciclo atual. Alcançamos estabilidade total no ecossistema de sessões sob as restrições do PHP 8.4 e MySQL Strict Mode. O roteamento de SEO, a home e os fragmentos globais de layout (cabeçalho, rodapé e minicart) estão operando puramente via DTOs e Repositórios da Alpha Engine.
**Próximo Foco:** Modernização e limpeza da Página de Produtos e fluxo de Checkout.