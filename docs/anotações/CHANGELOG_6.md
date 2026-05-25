# Registro Automático de Modificações (Fase de Automação de UI)

---

### Interceptador Dinâmico de Ações (Standalone AJAX)

**O que foi implementado:**
- Criação de um novo *Event Listener* em `common.js` que captura cliques em tags `<a>` ou `<button>` que contenham `data-oc-toggle="ajax"`.
- Adição de inteligência que extrai os atributos `data-*` (ex: `data-product_id="42"`) do elemento e os converte automaticamente no "payload" de uma requisição POST segura.

**Por que foi feito e Benefícios:**
O OpenCart 4 forçava o desenvolvedor a englobar qualquer botão com a tag `<form>` (mesmo para ações simples como "Remover do Carrinho" ou "Adicionar à Lista de Desejos"), o que sujava o HTML e dificultava a estilização flexível. Com esta implementação, abandonamos o "boilerplate" engessado da view legada. Agora, qualquer botão independente da interface pode disparar POSTs para a Alpha Engine injetando magicamente os seus dados.

---

### Correção de Contrato de Mapeador: CustomerMapper

**O que foi sugerido/implementado:**
- Ajuste da assinatura do método `save()` em `CustomerMapper.php` para respeitar o contrato polimórfico de `BaseMapper` (`InterfaceEntity` e `?int`).

**Por que foi feito e Benefícios:**
A tipagem estrita do PHP 8 estava disparando um erro fatal (*Fatal Error*) de compatibilidade de declaração durante o polimorfismo. O erro de backend corrompia o *Response* esperado em JSON, causando `SyntaxError` no JavaScript (`common.js`). Ao alinhar os parâmetros do Mapeador de Cliente à Interface Base, a hidratação e persistência do cadastro voltam a ocorrer sem gargalos.

---

### Resolução de Método Inexistente e Refatoração de Busca: CustomerMapper

**O que foi implementado:**
- Alteração da propriedade legado `$table` para o padrão `$tableName` compatível com a arquitetura `BaseMapper`.
- Substituição da chamada de método mágico inexistente (`findOneBy()`) por implementações concretas e explícitas em `findByEmail` e `findOneBy` utilizando o `QueryBuilder` aliado ao `dao->read()`.

**Por que foi feito e Benefícios:**
O método nativo de consulta `findOneBy` não estava presente na classe base gerando um erro fatal (HTTP 500) durante a checagem de e-mails existentes no registro. Ao implementar as consultas usando `$this->dao->read()`, a Alpha Engine garante que a Entidade de Cliente seja devolvida de forma "rica" e totalmente hidratada (acionando o Identity Map e relacionamentos internos), restabelecendo o fluxo seguro do Checkout e Cadastro.

---

### Resolução de Conflitos de Tipagem e Refatoração de Arrays: EntityMapper

**O que foi implementado:**
- Alteração no `EntityMapper::fillEntity()` para lidar adequadamente com `Arrays` iterados via requisição (`$_POST`), utilizando verificações escalares (`is_scalar()`) antes de testar cast de String.
- Evolução do método `convertValue` do `EntityMapper` injetando uma checagem inteligente: Se a entidade esperar uma `string`, mas o post entregar um `array` (ex: múltiplos `custom_fields`), o sistema fará a conversão para `JSON` automaticamente.
- Remoção de métodos defasados ou forçados de *Custom Fields* na orquestração do Controlador de Registro.

**Por que foi feito e Benefícios:**
Evita os travamentos silenciosos (`PHP Warning: Array to string conversion`) em requisições de formulários com múltiplos valores agrupados. Graças a essa conversão dinâmica (Array > Json), a necessidade de tratamentos manuais de *Custom Fields* nos Controladores acaba definitivamente, pois o EntityMapper compreende o domínio de forma natural, garantindo o princípio de *Skinny Controller*.

---

### Correção de Contrato e Tratamento de Falha Silenciosa: CustomerRepository

**O que foi implementado:**
- Atualização da assinatura do método `save()` em `CustomerRepository.php` alterando o retorno de `int` para `?int`, acompanhando a modificação prévia do Mapper.
- Adição de um mecanismo de *Fail Fast* (lançamento de `RuntimeException`) caso a persistência retorne nulo.

**Por que foi feito e Benefícios:**
Como o Mapper foi corrigido para aceitar retorno nulo em caso de erro na Query (evitando quebras de contrato polimórfico), o Repositório que o invoca também precisava refletir essa flexibilidade. O lançamento da exceção impede que a aplicação engula o erro do PDO (falha silenciosa) e direcione o cliente para a página de "Cadastro com Sucesso" sem que os dados realmente tenham sido gravados na base, orientando o desenvolvedor a checar o log.

---

### Correção do Anti-Pattern "NOT NULL sem Default": Injeção de Infraestrutura no Registro

**O que foi implementado:**
- Injeção de chaves de infraestrutura (`ip`, `token`, `code`, `date_added`) no array `$post_info` no controlador `register.php`, antes da invocação do `EntityMapper`.

**Por que foi feito e Benefícios:**
O banco de dados legado do OpenCart possui um anti-pattern estrutural onde diversas colunas exigem dados (`NOT NULL`), mas não possuem valores padrão (`DEFAULT`) no schema (ex: `ip` e `date_added`). Ao transicionar para persistência via DAO (que salva estritamente o estado da Entidade), a omissão desses campos resultava em rejeição no `INSERT` (retornando nulo/estourando exceção). Injetar os valores via Mapper resolve a pendência de forma segura e elegante, sem a necessidade de chumbá-los manualmente no controlador.

---

### Centralização e Expansão do SQL Debugger (DAO)

**O que foi implementado:**
- Refatoração da lógica *inline* de debug da `executeQuery` para um método privado isolado e reutilizável (`logDebugQuery`).
- Acoplamento do `logDebugQuery` no método `insertForClass` do *Data Access Object*.

**Por que foi feito e Benefícios:**
Aumenta drasticamente a observabilidade do sistema. Ao isolar o script criador de *queries executáveis* (que formata o Prepared Statement original para ser rodado no MySQL), e instanciá-lo na rotina principal de criação de entidades (`CREATE`), permitimos que erros complexos de inserção (como omissão de chaves ou quebras de constraint) sejam depurados instantaneamente no arquivo de logs (`queries.php`).

---

### Refatoração de ORM: Desduplicação de Colunas e Inferência de Entidades Nulas

**O que foi implementado:**
- Substituição do array simples de extração de métodos por um *HashMap* (`$columnsMap`) em `insertForClass` e `updateForClass` (DAO).
- Implementação de checagem do *Return Type* do método (`getReturnType`) para inferir chaves estrangeiras (sufixo `_id`), mesmo quando o método devolve `NULL`.

**Por que foi feito e Benefícios:**
Prevenção do erro de "Unknown Column" provocado pelo vazamento de nomes sem formatação de relacionamentos (ex: extrair `customer_group` em vez de `customer_group_id` por causa do objeto vazio). A utilização do *HashMap* consolida chaves duplicadas caso existam variações (como `getStatus()` e `isStatus()`), produzindo instruções limpas de `INSERT` e `UPDATE` e garantindo precedência para propriedades populadas contra relacionamentos não carregados.

---

### Correção de Cast de Tipos em Transações PDO (Booleans e DateTimes)

**O que foi implementado:**
- Adição de casting explícito de booleans para inteiros `(int)$val` na montagem do array de `$values` nos métodos `insertForClass` e `updateForClass` do `DataAccessObject`.
- Adição de fallback para formatação automática de instâncias de `\DateTimeInterface` para o padrão SQL `Y-m-d H:i:s`.

**Por que foi feito e Benefícios:**
O método `execute()` do PDO trata todos os parâmetros vinculados via array de forma genérica como `PDO::PARAM_STR`. Como resultado, propriedades booleanas como `false` (ex: desmarcar uma checkbox de Newsletter) são convertidas silenciosamente pelo PHP para uma string vazia `""`. Quando o MySQL opera em Strict Mode, ele rejeita `""` em colunas do tipo numérico (`TINYINT(1)`), estourando o erro fatal `Incorrect integer value: ''`. A coerção manual para `1` e `0` blinda a Alpha Engine contra este comportamento peculiar do driver de banco de dados, enquanto o tratamento de `DateTime` previne falhas de conversão de objetos em *timestamps* legados do OpenCart.

---

### Tratamento de Violação de Integridade (Unique Constraints) no DAO

**O que foi implementado:**
- Adição de blocos *try-catch* interceptando `PDOException` com código `23000` ou errorInfo `1062` (Duplicate Entry) nos métodos `insertForClass` e `updateForClass`.

**Por que foi feito e Benefícios:**
Prepara a arquitetura do ORM para lidar com aplicações rigorosas de *Constraints* (como adoção de índices `UNIQUE` no banco de dados para a coluna de E-mail). Em vez de permitir um *Fatal Error 500* que derruba a aplicação caso ocorra uma "condição de corrida" (duas requisições simultâneas tentando salvar o mesmo dado), o DAO lança uma `DomainException` limpa e controlada, indicando claramente a violação de integridade.

---

### Compatibilização da Camada de Autenticação Legada ao Schema Dinâmico (Cart/Customer)

**O que foi implementado:**
- Substituição do nome das colunas lidas nas querys de `customer_id` e `address_id` para o padrão normalizado `id` nos métodos de inicialização e `login()` de `system/library/cart/customer.php`.

**Por que foi feito e Benefícios:**
Como o Schema do banco foi refatorado para utilizar colunas normalizadas (`id`) em prol do funcionamento do *Mapeador Entidade-Relacionamento* (ORM), a biblioteca legada tentava acessar e atribuir propriedades usando os nomes das antigas chaves. Isso devolvia um `null` ocasionando o *Crash/Fatal Error* devido ao rigor de tipagem `int` na injeção de sessão. O ajuste restabelece o login em conformidade com o novo esquema unificado.

---

### Correção de Polimorfismo e Princípio de Liskov: WishlistRepository

**O que foi implementado:**
- Renomeação do método `getIndexData()` para `getWishlistViewData()` no `WishlistRepository.php` e no controlador associado (`wishlist.php`).

**Por que foi feito e Benefícios:**
Evita um *Fatal Error* de violação de contrato. A classe pai (`AbstractRepository`) já possuía uma assinatura rígida para o método `getIndexData` exigindo o retorno do tipo `array`. Ao sobrescrever o método forçando um retorno DTO `ViewResponse` e parâmetros customizados, a checagem do PHP 8 lançava uma incompatibilidade de declaração de método. A renomeação deixa explícito que se trata de uma extração de dados exclusiva do Domínio de Lista de Desejos para o Frontend.

---

### Remoção de Modelo Legado e Resolução de Contagem: Header e Wishlist

**O que foi implementado:**
- Adição do método `getTotalWishlist()` na arquitetura moderna do `WishlistRepository`.
- Substituição do carregamento de modelo legado (`$this->load->model('account/wishlist')`) pela injeção do repositório de domínio em `catalog/controller/common/header.php`.

**Por que foi feito e Benefícios:**
Soluciona o erro de `Undefined property: Proxy::getTotalWishlist`. O cabeçalho ainda dependia do motor legado do OpenCart para exibir a quantidade de itens na lista de desejos. Como a classe antiga foi depreciada ou perdeu referências no escopo, o Proxy nativo estourou o erro. Com isso, quitamos uma dívida técnica (`@todo`) pendente, centralizando 100% da inteligência da Lista de Desejos na Alpha Engine.

---

### Atualização da Documentação e Checkpoint de Projeto

**O que foi implementado:**
- Atualização do arquivo `docs/README1.md` (Documentação Central de Arquitetura) incluindo os avanços, otimizações e correções de ORM da Sprint atual na seção "Status Atual".

**Por que foi feito e Benefícios:**
Manter a documentação em sincronia com o código vivo é uma premissa fundamental da engenharia de software de alta performance. Este checkpoint solidifica os ganhos recentes na evolução da persistência de dados, adequação ao PHP 8.4 Strict e padronização do frontend, servindo como base histórica e norte arquitetural para novos desenvolvedores que venham a interagir com o ecossistema Alpha Engine.