# DP-63: Aperfeiçoamento do mecanismo de busca por produtos: Busca Full-Text de Produtos (MySQL MATCH/AGAINST)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-02 12:26:14
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/63

## Descrição

# Plano de Implementação - Busca Full-Text de Produtos (MySQL MATCH/AGAINST)

Substituição da busca textual por `LIKE '%...%'` na tabela de produtos pelo recurso nativo de **MySQL Full-Text Search** com `MATCH()` e `AGAINST()` em modo booleano (`IN BOOLEAN MODE`), otimizando o desempenho de consultas textuais (eliminação de *Full Table Scans*) e a relevância dos resultados.

---

## Avaliação da Proposta do Usuário

A proposta apresentada é **excelente e totalmente alinhada com as melhores práticas de banco de dados e e-commerce**.

### Vantagens da Mudança
1. **Performance O(log N)**: O `LIKE '%termo%'` obriga o MySQL a realizar varredura completa da tabela (*Full Table Scan*). O índice `FULLTEXT` gera uma estrutura invertida de palavras, permitindo buscas instantâneas mesmo em catálogos com milhares ou milhões de produtos.
2. **Relevância por Score**: O MySQL calcula a pontuação de relevância de cada produto baseado na ocorrência dos termos.
3. **Flexibilidade Booleana**: Operadores como `+` (obrigatório), `-` (excluir) e `*` (busca por prefixo/palavra parcial) permitem uma experiência de busca rica.

### Adaptações e Melhorias Propostas (Engenharia de Software & E-Commerce)
Para tornar a solução robusta em produção, propomos as seguintes adaptações:

> [!NOTE]
> 1. **Tratamento de Prefixos/Palavras Incompletas (`+smart* +tv*`)**:
>    Se o usuário digitar "smart tv" ou "smar tv", a busca normal sem `*` só encontraria a palavra exata "smart". Sanitizaremos os termos e aplicaremos o caractere curinga `*` a cada palavra para permitir autocomplete e correspondência parcial.
>
> 2. **Sanitização de Caracteres Especiais de Sintaxe Booleana**:
>    Caracteres reservamos do MySQL Boolean Mode (`+`, `-`, `>`, `<`, `(`, `)`, `~`, `*`, `"`) digitados por usuários em buscas normais podem quebrar a sintaxe SQL se não forem sanitizados antes da montagem do `AGAINST()`.
>
> 3. **Estratégia Híbrida de Fallback (Busca por SKU/Modelo/Palavras Curtas)**:
>    O mecanismo de Full-Text do InnoDB ignora por padrão palavras menores que 3 caracteres (`innodb_ft_min_token_size = 3`). Para buscas por códigos (ex: "TV", "P", "M", "G", ou SKU exact match no campo `p.model`), manteremos um fallback condicional `(MATCH(...) AGAINST(...) OR p.model = ?)` ou busca `LIKE` para termos curtos.
>
> 4. **Ordenação Inteligente por Relevância (Score)**:
>    Quando a ordenação padrão não for alterada pelo usuário (`sort=p.sort_order`), utilizaremos o score da relevância `MATCH(...) AGAINST(...)` como critério primário de ordenação.

---

## Open Questions

> [!NOTE]
> Nenhuma questão impeditiva no momento. O plano já contempla fallbacks para palavras curtas e modelos.

---

## Proposed Changes

### Database Layer

#### [NEW] [add_fulltext_index_product_description.sql](file:///var/www/html/agsonhos/tests/newTables/add_fulltext_index_product_description.sql)
Criar script de alteração de tabela para adicionar o índice `FULLTEXT` na tabela de descrições de produtos.

```sql
ALTER TABLE `agsc_product_description` 
ADD FULLTEXT INDEX `idx_ft_product_search` (`name`, `description`, `tag`);
```

---

### Persistence Layer (Data Mapper)

#### [MODIFY] [ProductMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ProductMapper.php)

- Implementar helper `prepareFullTextSearchQuery(string $searchTerm): string` para higienizar a entrada do usuário e adicionar operadores booleanos (ex: `+termo1* +termo2*`).
- Atualizar o método `getProducts()`:
  - Substituir o trecho `(pd.name LIKE ? OR p.model = ?)` pela cláusula `MATCH(pd.name, pd.description, pd.tag) AGAINST(? IN BOOLEAN MODE) OR p.model LIKE ?`.
  - Se o termo for menor que 3 caracteres, aplicar fallback para busca estruturada por `LIKE`.
  - Adicionar o score do `MATCH()` nos selects e utilizar na ordenação quando `sort` for o padrão.
- Atualizar o método `getTotalProducts()` com a mesma lógica de contagem condicional para manter a contagem exata da paginação.

---

### Diagramas & Documentação

#### [MODIFY] [product_search.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/product_search.puml)

- Atualizar as anotações do diagrama de sequência para refletir com exatidão a chamada de `MATCH(name, description, tag) AGAINST(:term IN BOOLEAN MODE)` no QueryBuilder e Mapper.

---

## Verification Plan

### Automated Tests / Test Scripts
- Criar e executar um script PHP de teste (`tests/test_fulltext_search.php`) simulando diferentes cenários de busca:
  1. Busca por palavra única (ex: `"smart"` -> `+smart*`).
  2. Busca por múltiplas palavras (ex: `"cama casal"` -> `+cama* +casal*`).
  3. Busca com caracteres especiais (ex: `"smart + tv"` -> sanitizado para `+smart* +tv*`).
  4. Busca por palavra curta/modelo (ex: `"TV"` ou modelo numérico).
  5. Validação da contagem de resultados (`getTotalProducts`) combinando paginação e filtros facetados (categoria, preço, marca).

### Manual Verification
- Acessar a rota de busca no navegador (`/pt-br/busca?busca=termo`) e validar os resultados retornados no Twig e no console de logs.

# Tarefas: Busca Full-Text de Produtos (MySQL MATCH/AGAINST)

- [x] Criar script SQL de migração `tests/newTables/add_fulltext_index_product_description.sql` <!-- id: 0 -->
- [x] Aplicar/verificar o índice FULLTEXT na tabela `agsc_product_description` <!-- id: 1 -->
- [x] Atualizar `ProductMapper.php` com suporte a Full-Text Search, sanitização de sintaxe booleana, ordenação por score e fallback para termos curtos/modelo <!-- id: 2 -->
- [x] Atualizar o diagrama de sequência `docs/workflows/sequence_diagrams/product_search.puml` <!-- id: 3 -->
- [x] Criar script de testes automatizados `tests/test_fulltext_search.php` e validar buscas textuais, fallbacks e filtros <!-- id: 4 -->
- [x] Executar testes e verificar a integridade da busca no e-commerce <!-- id: 5 -->

# Walkthrough - Implementação da Busca Full-Text de Produtos (MySQL MATCH/AGAINST)

Substituição com sucesso da busca por `LIKE '%...%'` pelo recurso nativo de **MySQL Full-Text Search** utilizando `MATCH()` e `AGAINST()` em `BOOLEAN MODE`, com sanitização de termos, busca por prefixo, ordenação por relevância e fallback de segurança para modelos e termos curtos.

---

## Alterações Realizadas

### 1. Migração do Banco de Dados
- **Script SQL**: [add_fulltext_index_product_description.sql](file:///var/www/html/agsonhos/tests/newTables/add_fulltext_index_product_description.sql)
- Criado o índice `FULLTEXT` composto `idx_ft_product_search` nas colunas `(`name`, `description`, `tag`)` da tabela `agsc_product_description`.

### 2. Camada de Persistência (Data Mapper)
- **Arquivo**: [ProductMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ProductMapper.php)
- Implementado o método helper `prepareFullTextQuery(string $searchTerm)` para remover caracteres de sintaxe booleana reservadores e formatar a string com operadores `+` e wildcard `*` (ex: `"smart tv"` $\rightarrow$ `"+smart* +tv*"`).
- Atualizado `getProducts()` e `getTotalProducts()` para aplicar a cláusula:
  ```sql
  MATCH(pd.name, pd.description, pd.tag) AGAINST(? IN BOOLEAN MODE) OR p.model LIKE ?
  ```
- Implementado fallback automático para `pd.name LIKE ? OR pd.tag LIKE ? OR p.model LIKE ?` caso o termo de busca possua menos de 3 caracteres.

### 3. Documentação & Diagrama de Sequência
- **Arquivo**: [product_search.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/product_search.puml)
- Atualizado o diagrama de sequência com os métodos `getProducts` e `prepareFullTextQuery` refletindo o fluxo exato de consulta ao MySQL.

---

## Resultados da Verificação Automatizada

- **Script de Testes**: [test_fulltext_search.php](file:///var/www/html/agsonhos/tests/test_fulltext_search.php)

Resultados da execução do teste em ambiente real:
```text
=== INICIANDO SUÍTE DE TESTES: FULL-TEXT SEARCH ===
[TEST 1] Sanitização e Formatação de Termos
  Input: 'smart tv' => Output: '+smart* +tv*'
  Input: 'cama  casal' => Output: '+cama* +casal*'
  Input: 'smart + tv - red' => Output: '+smart* +tv* +red*'
  Input: 'travesseiro' => Output: '+travesseiro*'
  [PASS] Sanitização de termos funcionou como esperado.

[TEST 2] Execução da Busca Full-Text no Banco de Dados com Dados Reais
  Busca por 'Adaptador': 10 produtos retornados na página. Total no catálogo: 40
  [PASS] Execução de query FULLTEXT retornou resultados reais do banco de dados.

[TEST 3] Fallback para Termo Curto (ex: 'TV')
  Busca por 'TV': 0 produtos retornados. Total: 0
  [PASS] Fallback de termo curto funcionou sem erros.

[TEST 4] Teste de Integração no ProductRepository::getSearchData()
  Produtos encontrados via Repository: 5
  Total no DTO: 55
  [PASS] ProductRepository respondeu corretamente.

=== TODOS OS TESTES PASSARAM COM SUCESSO! ===
```

