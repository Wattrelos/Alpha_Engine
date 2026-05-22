# Débitos Técnicos e Anti-Patterns do OpenCart (Defused)

Este documento serve como um registro das falhas de design arquitetural originais do OpenCart 4.x. 
Como a **Alpha Engine** opera usando o padrão *Strangler Fig* (modernização progressiva sem quebrar o legado), não podemos alterar o esquema de banco de dados imediatamente (o que quebraria o Painel de Administração antigo).

Aqui listamos essas "bombas-relógio" e documentamos como a camada de código (DAO/Mappers) as neutralizou.

---

## 1. O "Pseudo-Null" em Chaves Estrangeiras (FK = 0)
**Módulo Afetado:** Categorias (`parent_id`), Clientes (`customer_id` em Sessões/Carrinho), Downloads, etc.

### O Problema:
O OpenCart utiliza o valor inteiro `0` em vez de `NULL` para representar a ausência de um relacionamento (Ex: uma categoria que não tem pai, ou um visitante que não tem conta de cliente). 
* **Consequência:** Isso impede terminantemente a criação de *Foreign Keys* (Chaves Estrangeiras restritivas) reais no banco de dados (o banco acusa que não existe uma linha com `id = 0` na tabela referenciada). Em um ambiente fortemente tipado (DDD), isso causa conflitos de hidratação.

### O Escudo (Defuse) da Alpha Engine:
Em vez de alterar a tabela e quebrar o painel administrativo que busca por `WHERE parent_id = 0`, nós neutralizamos o erro na camada do `DataAccessObject` (DAO). 
Durante a hidratação recursiva via *Reflection* (`fillEntityRecursively`), o DAO possui um *failsafe*: se a propriedade for do tipo `InterfaceEntity` e o valor estrangeiro for `0`, o DAO simplesmente ignora o carregamento, deixando a propriedade como `null` no objeto PHP.

### 🧹 Ação Futura Necessária:
Quando o Painel Administrativo for refatorado para a arquitetura Alpha, as tabelas deverão sofrer `ALTER TABLE`, alterando as colunas `parent_id` e afins para permitir `NULL`. Em seguida, executar `UPDATE tabela SET coluna = NULL WHERE coluna = 0`.

---

## 2. Memory Leak de Eventos (O Monstro do JSON)
**Módulo Afetado:** Carregamento de Idiomas (`catalog/controller/event/language.php`).

### O Problema:
Para criar um contexto isolado de idiomas entre Controladores Pai e Filhos (partials), o OpenCart empacotava as variáveis de linguagem antigas em uma string usando `json_encode()` e guardava na propriedade estática `backup`. Com múltiplos *sub-controllers* carregados na tela (Colunas, Header, Footer), o JSON era envelopado recursivamente (JSON dentro de JSON). O tamanho da string crescia exponencialmente, chegando facilmente a dezenas de megabytes e disparando `Fatal Error: Allowed memory size exhausted` (128MB).

### O Escudo (Defuse) da Alpha Engine:
Substituímos o envelopamento de strings JSON por uma **Pilha (Stack) em Memória** puramente PHP (`self::$backupStack[] = $data`). Isso reduziu o consumo de memória RAM a quase zero, pois o PHP apenas repassa os ponteiros dos arrays.

---

## 3. Sessões Obesas (Unbuffered Queries)
**Módulo Afetado:** Gerenciamento de Sessão (`core/Mappers/EntityMappers/SessionMapper.php`).

### O Problema:
Quando erros silenciosos (como o Memory Leak do Idioma) ocorrem, strings gigantes de memória corrompida eram armazenadas na tabela `session` do OpenCart. Na próxima requisição, quando o PDO tentava dar `fetchAll()` na tabela de sessão via `SessionRepository`, o servidor crashava antes mesmo de montar o site, ou disparava o erro PDO "2014 Cannot execute queries while other unbuffered queries are active" no evento de shutdown do sistema.

### O Escudo (Defuse) da Alpha Engine:
Implementamos uma inspeção de peso (*Failsafe*). O `SessionMapper` executa um `SELECT id, LENGTH(data) AS size`. Se o tamanho do pacote da sessão for bizarro (ex: > 5MB), o Mapper considera a sessão "lixo nuclear", deleta a linha diretamente pelo Token e gera uma sessão em branco, curando o servidor de forma automática.
Adicionalmente, os atributos de PDO `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true` foram forçados no `ConnectionDB` da Alpha.

---
*Este documento deve ser atualizado sempre que um novo comportamento hostil da arquitetura legada for contornado sem alteração de banco de dados.*