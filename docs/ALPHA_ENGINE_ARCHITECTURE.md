# 🏛️ Alpha Engine - Arquitetura e Engenharia de Software

## 1. Visão Geral
A Alpha Engine é uma reformulação arquitetural profunda aplicada sobre o ecossistema original do OpenCart 4. O objetivo é modernizar o processamento de dados e regras de negócio introduzindo conceitos de **Domain-Driven Design (DDD)**, **Repository Pattern** e **Data Mappers**. A transição é feita utilizando o padrão **Strangler Fig**, permitindo uma reestruturação progressiva de rotas e banco de dados sem quebrar o legado.

---

## 2. Padrões de Projeto Aplicados

*   **Repository Pattern**: Centraliza a lógica de domínio e agregações de negócio. Repositórios injetam os Mappers para abstrair persistência complexa.
*   **Data Mapper**: Separação total entre as Entidades do Domínio e a infraestrutura de banco de dados (SQL).
*   **Identity Map**: Implementado estaticamente no `DataAccessObject` (DAO). Garante que, caso um mesmo produto ou cliente seja requisitado múltiplas vezes durante a mesma conexão, o objeto seja retornado do cache em memória RAM. Elimina gargalos de N+1 queries.
*   **Proxy Pattern e Lazy Loading**: Associações `#[ManyToOne]` ou coleções `#[OneToMany]` gigantes não travam a memória. O DAO hidrata essas propriedades usando Classes Proxy (para Entidades) e `LazyCollection` (para Arrays), disparando uma consulta ao banco *somente* se a propriedade for percorrida em tela.
*   **Unit of Work**: O DAO abstrai transações em cascata (PDO `beginTransaction()`, `commit()`, `rollBack()`), garantindo que inserções em múltiplas tabelas (ex: Criar Pedido, Atualizar Estoque, Gravar Cupom) ocorram de forma 100% atômica.

---

## 3. Topologia de Camadas

### 📦 3.1. Entidades de Domínio (`core/Model/Domain/Entities/`)
- Objetos puramente PHP (POPOs), fortemente tipados para os padrões restritivos do PHP 8.4.
- Ausência total de lixo legado (Anotações Doctrine em comentários foram banidas). O modelo adota **Atributos Nativos PHP** (`#[ManyToOne]`, `#[OneToMany]`).
- Herdam a abstração `BaseEntity`, garantindo injeção estrita da chave primária (`id`).
- São "agnósticas": não possuem nenhuma linha de instrução MySQL.

### 💾 3.2. Data Access Object - DAO (`core/Model/DataAccessObject/`)
O verdadeiro motor da Alpha Engine. Responsável por:
- Construção de Queries blindadas contra *SQL Injection* baseadas no `QueryBuilder`.
- Varredura por Refração (Reflection API) para hidratação massiva e recursiva dos POPOs.
- Mapeamento dinâmico entre o padrão do Banco (`snake_case`) e as Classes do PHP (`camelCase`).
- Resolução automática de Inserções em tabelas-pivô para os relacionamentos *Many-To-Many*.

### 🗺️ 3.3. Data Mappers (`core/Mappers/`)
Mappers lidam com os cenários onde uma entidade precisa de múltiplas *JOINs* complexas. Eles detêm os verbos essenciais (`findById`, `findAll`) e escondem instruções vitais de filtro (ex: validar Loja `store_id`, Status `status = 1` e Idioma) antes de mandar o dado para o DAO renderizar.

### 🏛️ 3.4. Repositórios (`core/Model/Domain/Repositories/`)
- A interface conversacional com os Controladores da Vitrine/Painel.
- Agrupam o ciclo de vida. Exemplo: O `CartRepository` sabe exatamente como instanciar o Carrinho, calcular Totais chamando as extensões logísticas, validar cupons e retornar os produtos do DAO, tudo em uma única fachada.

### 🎮 3.5. BaseController (`core/Controller/`)
- Mata a lentidão de chamadas estáticas `load->controller()` e o *Spaghetti Code*.
- Implementa o **Master Pattern** via método autônomo `$this->render()`, envelopando magicamente o `header`, `footer` e barras laterais.
- Transforma Controladores originais pesados (com quase mil linhas de cálculos visuais e limpezas de array) em *Skinny Controllers*, focados unicamente em pegar a Rota, repassar para o Repository, e entregar para o Twig.

---

## 4. Blindagens e Defuse de Legados (Anti-Patterns Contornados)

O OpenCart possui dívidas técnicas que fariam bancos de dados estritos travarem. A Alpha Engine isola o sistema das seguintes armadilhas:

- **A Fraude do Pseudo-Null (FK = 0)**: 
  O OpenCart se recusa a usar `NULL` em chaves estrangeiras vazias (ex: categoria raiz não tem pai, portanto envia `parent_id = 0`). Como isso quebraria um ORM rigoroso e regras matemáticas de *Foreign Key* (já que ID 0 não existe na tabela pai), o nosso `DataAccessObject` escaneia os IDs no momento da hidratação. Se flagrar um `0` num atributo tipo Classe, ele omite a construção e define a entidade adequadamente como nula.
  
- **O Apocalipse de Sessão (Session OOM Crash)**: 
  Falhas antigas provocavam encadeamentos recursivos de strings JSON guardados no Banco. Em requisições massivas (via bot), as `sessions` chegavam a pesar 15 MB e quebravam o servidor por esgotamento de memória.
  O nosso motor executa verificação ativa (`SELECT LENGTH(data)`). Caso identifique sujeira nuclear, ele elimina o token agressor, recriando a sessão branca em frações de milissegundo. O Motor de DB customizado da Alpha também impõe *buffered queries* nativas, prevenindo "Lock/Wait".

---

## 5. Fluxo de Execução Simplificado (Leitura do Banco)

**1. Controlador Requisita**  
`$product = $this->productRepository->find(10);`

**2. Repositório Orquestra**  
Aciona o cache e dispara o Mapper para o objeto.

**3. Mapper Solicita**  
`$this->dao->hydrate('Product', $row);`

**4. DAO Resolve**  
- Checa o *Identity Map*: "Já tenho o produto ID 10 na memória dessa requisição?" Se sim, devolve sem ir ao MySQL.
- Se não, utiliza Reflection, cria `new Product()`, injeta cada coluna nos métodos `set()`, localiza os `#[ManyToOne]` (como Lojas e Fabricantes) criando os Proxies adormecidos e retorna.

**5. Exibição**  
O controlador joga a Entidade limpa na tela ou gera o JSON da API. Fim do fluxo!