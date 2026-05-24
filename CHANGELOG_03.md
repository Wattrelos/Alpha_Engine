
---

### Alpha Engine: Refatoração do Fluxo de Informação (CMS)
**Data:** [Data Atual]
**O que foi feito:**
- Migração do controller `information.php` para utilizar a `BaseController`, transformando-o num verdadeiro *Skinny Controller*.
- Implementação do método `getInformationDisplayData` no `InformationRepository`, transferindo toda a construção do DTO, formatação de HTML (`html_entity_decode`) e resolução de Breadcrumbs para a camada de Domínio.
- `InformationMapper` atualizado para estender `BaseMapper`, herdando a injeção nativa de banco de dados, mas mantendo a consulta SQL altamente otimizada que resolve Multi-store, Status e Language diretamente no banco.
**Benefícios:** Código drasticamente reduzido no controller, eliminação de laços `foreach` de hidratação manual, menor acoplamento e suporte automático ao renderizador de layouts unificado da Alpha Engine.

---

### Alpha Engine: Refatoração do Fluxo de Carrinho (Cart)
**Data:** [Data Atual]
**O que foi feito:**
- Migração de lógica de exibição de `checkout/cart.php` para utilizar a `BaseController`, transformando-o num verdadeiro *Skinny Controller*.
- Implementação do método `getCartDisplayData` no `CartRepository`, extraindo loops, checagens de peso, verificações de sessão e hidratação de produtos da visão.
**Benefícios:** Enorme redução do tamanho do controller; reuso imediato das lógicas de validação de produtos do carrinho (evita duplicação entre minicart, api e página de cart principal); simplificação da leitura utilizando o pattern DTO (`ViewResponse`).## 🧠 O Que Mudou e Por Que?
1. **Aceleração da Vitrine (getOptions)**: Ao invés de 1 Query de Opções + X Queries de Valores, agora fazemos exatamente 2 consultas, independente de o produto ter 1 ou 100 opções variadas. Os dados são mesclados na memória do servidor, tirando uma carga enorme do MySQL.
2. **Nova Rota O(1) do Carrinho (getOptionValuesByIds)**: O CartRepository manda um array com todos os IDs de modificadores que o cliente comprou, e o banco devolve tudo de uma vez. Note que fiz o método já devolver os dados indexados pelo próprio ID da opção ($indexed[$result['product_option_value_id']]), o que torna a matemática no PHP quase instantânea e elimina a necessidade de array_search complexos.
3. **Mapeamento Transparente**: O prefixador pov.* garante a captura automática do price_prefix (+, -, *, /) e do weight_prefix, conectando as tabelas e unificando option_name com option_value_name.

---

## O Efeito dessa Refatoração:
1. liminamos completamente a dependência da classe da Entidade que causou a falha.
2. A requisição vai inserir os dados de forma extremamente rápida.
3. Sem o erro fatal do PHP, o controlador voltará a imprimir o JSON: {"success": "Você adicionou o produto..."}.
4. O common.js vai ler o JSON perfeitamente, acionar o alert verde na tela e disparar o Auto-Refresh que injetamos ontem atualizando o topo com a foto e os totais!

---

##  O Que Ganhamos Com Isso?
1.  **Ponto Único de Falha Isolado**: Qualquer bug no cálculo de impostos ou cupons só precisa ser consertado dentro do CartRepository::getTotals().
2.  **Coesão***: Se um cliente aplica um cupom na página de checkout, aquele desconto agora vai refletir instantaneamente se ele abrir o Minicart no cabeçalho.
3.  **Morte de Modelos Legados**:*Despedimo-nos oficialmente do catalog/model/checkout/cart.php.

---

## 🚀 Resolução da Orquestração de Layout do Carrinho
1. **Master Pattern (`render`) Ativado**: A página `checkout/cart` ainda utilizava o padrão legado do OpenCart de injetar manualmente as variáveis de layout (`column_left`, `header`, `footer`) via `load->controller()`. Isso ignorava a orquestração estrutural do `BaseController` da Alpha Engine, gerando um layout quebrado ou flutuante. A substituição para `$this->render()` garante o envelopamento nativo universal.
2. **Fim do Overhead do Loader (`getList`)**: Substituímos a chamada ineficiente `$this->load->controller('checkout/cart.getList')`, que instanciaria a classe inteira novamente, por uma chamada direta de método `$this->getList()`, garantindo complexidade temporal $O(1)$.
3. **Benefício Técnico**: O cabeçalho e rodapé passam a ser corretamente "abraçados" pelo gerenciador de Grid CSS da aplicação, e o tempo de reposta da renderização melhora significativamente sem chamadas recursivas no `loader.php`.

---

## 🛠️ Correção do Retorno Void no BaseController::render
1. **O Problema:** A página do carrinho começou a disparar um erro fatal de tipagem (`Argument #1 ($output) must be of type string, null given`) na classe `Response`. Isso ocorreu porque estávamos embrulhando a chamada do motor de design (`$this->render()`) dentro de um `$this->response->setOutput()`.
2. **A Causa:** O método `render()` do `BaseController` foi arquitetado para orquestrar o cabeçalho, rodapé e as colunas, e ele **já cuida nativamente de injetar o resultado final no Response**, retornando `void` (ou `null` no escopo de avaliação do PHP). Ao repassar esse `null` para o `setOutput()`, o PHP 8.4 quebrou pela verificação de tipos estritos.
3. **A Solução:** Removemos o invólucro legados do `setOutput()`, chamando apenas a instrução `$this->render('checkout/cart', $data)`, deixando o `BaseController` finalizar o fluxo sozinho de forma autônoma.

---

## 🧹 Padronização em Lote da Renderização (Categorias e Produtos)
1. **Refatoração do Controlador de Categorias:** O arquivo `catalog/controller/product/category.php` ainda possuía o padrão legado OpenCart, com a injeção mecânica do `header`, `footer` e barras laterais e o envelopamento no `setOutput($this->load->view())`. Reduzimos o boilerplate visual chamando diretamente `$this->render()`, envelopando de forma nativa a tela.
2. **Ajuste de Tipagem de Retorno em Produto:** No `catalog/controller/product/product.php`, a função estava usando o *anti-pattern* `return $this->render(...)`. Sendo um método tipado para `?Action`, e `render` retornando `void`, separamos as instruções: `$this->render()` e `return null;` para respeitar a nova semântica estrita do PHP 8.4 e evitar fatal errors.

---

## 🛡️ Failsafe de Memória e Otimização do PDO (Session Leak)
1. **Blindagem Ativa no `SessionMapper`:** As consultas na tabela de sessão agora são antecedidas por um `SELECT LENGTH(data)`, utilizando a resolução dinâmica `$this->getFullTableName()`. Se o pacote de sessão ultrapassar o limite seguro (~5MB), ele é pulverizado automaticamente antes de ser hidratado pelo banco, evitando que o servidor sofra `Fatal Error: Memory Exhausted`.
2. **Otimização do `ConnectionDB`:** Adição expressa da diretiva `PDO::ATTR_EMULATE_PREPARES => false` combinada com o buffer de queries. Isso impede o PDO de manter resultados antigos travados na memória, aniquilando definitivamente o erro _"2014 Cannot execute queries while other unbuffered queries are active"_.
3. **Limpeza Arquitetural:** Identificamos e orientamos a exclusão do arquivo fantasma `core/Model/Domain/Entities/SessionMapper.php`, garantindo que toda persistência seja estritamente processada pelo Mapper oficial em `core/Mappers/EntityMappers`.

---

## 🔗 Refatoração e Blindagem do Roteador de SEO
1. **Blindagem PHP 8.4 (`seo_url.php`):** A função nativa `parse_url` não retorna as chaves `query`, `scheme`, `host` e `path` quando elas não existem na URL solicitada. O código legado tentava acessar essas chaves diretamente, o que no PHP 8.4 gera _Warning: Undefined array key_ e compromete o processamento. Injetamos verificações limpas com `isset()` e o operador _Null Coalescing_ `??` para blindar a geração de rotas.
2. **Identity Map no Reverse Lookup (`SeoUrlRepository`):** O repositório já otimizava a busca de rotas com cache estático (`urlCache`), mas apenas na geração de links visuais (`getKeywordByQuery`). Espelhamos o padrão de _Identity Map_ também na função `getQueryByKeyword()` (usada na decodificação quando o usuário acessa uma página). Isso garante que múltiplas validações da mesma URL na mesma requisição sejam servidas em $O(1)$, economizando buscas repetidas no Redis/Memcached ou banco de dados.
3. **Orquestração Comprovada:** O Controller nativo agora confia $100\%$ no motor da Alpha Engine para todas as conversões bidirecionais (URL -> Rota interna -> URL), mantendo o código enxuto e escalável.

---

## 🛒 Resolução Completa da Página de Categoria (DTO e Paginação)
1. **Correção de TypeError (Entity vs Array):** O `CategoryRepository::getCategory()` estava projetado para retornar `?array` e repassando o resultado direto do `CategoryMapper::getCategory()`. No entanto, o Mapper retornava uma Instância de `Category`, causando `Fatal Error: Return value must be of type ?array, Category returned`. Alteramos o Mapper para retornar o array flat cru e completo (com `meta_description` e `meta_keyword`), sanando o erro estrito do PHP 8.
2. **Injeção Autônoma do DTO de UI:** O Repositório passou a orquestrar os *Breadcrumbs*, *Limits* (25, 50, 100) e *Sorts* (Ordenação A-Z, Menor Preço), permitindo que os Dropdowns do Bootstrap funcionem nativamente sem inflar o código do Controller.
3. **Alinhamento do Twig e Language:** A tag `{{ pagination }}` no `product/category.twig` foi substituída por `{{ pagination_html }}` para respeitar o nome instanciado pelo BaseController, e incluímos `$this->loadLanguageData('product/category', $data)` para habilitar todos os textos e botões na tela. A página de Categorias agora é completamente Loader-Free e opera via Alpha Engine!

---

## 🛠️ Blindagem Estrita e Correções Matemáticas no Catálogo
1. **Failsafe de Divisão por Zero (`category.php`):** A renderização da paginação calculava o total de páginas dividindo pelo limite da consulta (`$filter_data['limit']`). Se a configuração nativa do OpenCart (`config_pagination_catalog`) estivesse corrompida ou vazia, o limite caía para `0`, disparando um erro fatal matemático (Division by zero) no PHP 8. Adicionamos uma trava de segurança garantindo que o `$limit` seja sempre pelo menos `10`.
2. **Failsafe de Chave Indefinida (`ProductMapper.php`):** Em buscas de produtos e contagens, as queries tentavam avaliar o booleano de `$data['filter_sub_category']` diretamente em um operador ternário. No PHP 8.4, ler uma chave não definida de um array aciona um Warning rigoroso. Envolvemos a checagem usando `!empty()`, adequando-se à validação estrita da linguagem e estabilizando a geração do SQL subjacente.

---

## 🔍 Modernização do Sistema de Busca (Skinny Controller)
1. **Refatoração DTO no `ProductRepository`**: Assim como feito nas categorias, movemos a massiva montagem da árvore recursiva de departamentos, orquestração de limites de exibição (25, 50, 100) e vínculos de ordenação (`sort_order`, `price`, etc.) para o método isolado `getSearchData()`. O repositório agora assume a lógica da pesquisa e entrega um `ViewResponse` imaculado e limpo.
2. **Fim de Anti-Patterns em `search.php`**: O controlador que possuía quase 150 linhas foi desidratado e transformado em um puro _Skinny Controller_ da Alpha Engine. Aplicamos `?? ''` em vez de longos blocos `if(isset())`, implementamos proteção estrita de Divisão por Zero contra o número de resultados/limite (assim como fizemos na Categoria) e eliminamos _helpers_ de UI para dentro do Domínio.

### Alpha Engine: Refatoração do Fluxo de Informação (CMS)
**Data:** [Data Atual]
**O que foi feito:**
- Migração do controller `information.php` para utilizar a `BaseController`, transformando-o num verdadeiro *Skinny Controller*.
- Implementação do método `getInformationDisplayData` no `InformationRepository`, transferindo toda a construção do DTO, formatação de HTML (`html_entity_decode`) e resolução de Breadcrumbs para a camada de Domínio.
- `InformationMapper` atualizado para estender `BaseMapper`, herdando a injeção nativa de banco de dados, mas mantendo a consulta SQL altamente otimizada que resolve Multi-store, Status e Language diretamente no banco.
**Benefícios:** Código drasticamente reduzido no controller, eliminação de laços `foreach` de hidratação manual, menor acoplamento e suporte automático ao renderizador de layouts unificado da Alpha Engine.## 🌍 Orquestração Automática de Associações (Países, Zonas e Descrições)
**Data:** [Data Atual]

**O que foi feito:**
- Criação do `CountryRepository` na camada de Domínio, eliminando de vez a necessidade do modelo legado `localisation/country`.
- Mapeamento no `AlphaContainer` para que todos os acessos legados sejam injetados via Repository Pattern.
- Verificação da integridade do ORM (`DataAccessObject`): Como a Entidade `Country` agora possui o atributo `#[OneToMany]` para Zonas e Descrições, o simples ato do Mapper instanciar um `Country` engatilha o `processAssociations` no ORM, buscando automaticamente os arrays dependentes em um fluxo otimizado.

**Benefícios Técnicos:**
1. **Desacoplamento Rigoroso**: O repositório e o mapper não contêm nenhuma linha de `JOIN` manual para descrições. As estruturas de relacionamento são completamente invisíveis, definidas apenas por regras estruturais da Entidade PHP 8.4.
2. **Identity Map e O(1) Performance**: Com a camada de cache persistente implantada no Repositório, as requisições constantes de listagem de países e resolução de checkout/frete ocorrem com taxa zero de queries SQL após a primeira carga.
3. **Segurança de Tipos Estrita**: Com coleções padronizadas, métodos como `getName()` do país ou as repetições sobre as zonas vão beneficiar-se dos analisadores estáticos da Alpha Engine, reduzindo crashes não mapeados.

---

## 📄 Criação da Entidade CountryDescription
**Data:** [Data Atual]

**O que foi feito:**
- Implementação da entidade `CountryDescription`, garantindo que as propriedades `$countryId`, `$languageId` e `$name` estejam corretamente tipadas.
- Mapeamento exato do método `setCountryId()` para casar perfeitamente com a configuração `foreignKey: "countryId"` declarada no atributo `#[OneToMany]` da Entidade `Country`. Isso assegura a hidratação bidirecional (via ORM DataAccessObject) sem "mágica" oculta.