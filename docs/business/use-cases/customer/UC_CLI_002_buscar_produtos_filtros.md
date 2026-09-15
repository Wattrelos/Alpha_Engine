# UC_CLI_002 - Buscar Produtos com Filtros

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_002` |
| **Nome** | Buscar Produtos com Filtros |
| **Módulo** | Loja Virtual - Catálogo, Busca & Mídia |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Mecanismo de Busca MySQL Full-Text / Redis |
| **Tipo** | Condução / Pesquisa |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF011](/docs/requirements/functional/functional_requirements.yaml) (Busca e filtros avançados)<br>**RN:** [RN003](/docs/requirements/business_rules/business_rules.yaml) (Especificações técnicas por categoria)<br>**RNF:** [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Tempo de resposta < 500ms) |

---

## 1. 🎯 Descrição Sumária
Permite ao usuário pesquisar produtos digitando palavras-chave, nomes técnicos, códigos SKU ou termos comerciais na barra de busca, refinando os resultados através de múltiplos filtros facetados simultâneos (faixa de preço, marca/fabricante, voltagem, cor, dimensões e categoria).

---

## 2. ⚡ Pré-Condições
- O usuário possui acesso à interface da loja.
- O índice de busca Full-Text de produtos está sincronizado na base de dados.

---

## 3. ✅ Pós-Condições
- O sistema apresenta os produtos correspondentes que atendem estritamente aos critérios de busca textual e filtros selecionados.

---

## 4. 🚀 Gatilho (Trigger)
O usuário digita um termo na caixa de busca e pressiona `Enter` ou clica no ícone de lupa.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Digita um termo de busca no campo de pesquisa do cabeçalho (ex: *"Porcelanato 60x60"* ou *"Furadeira de Impacto"*).
2. **Sistema:** Oferece sugestões de autocompletar em tempo real (*autocomplete/instant search*) conforme o usuário digita.
3. **Ator:** Submete a busca pressionando `Enter` ou clicando na sugestão.
4. **Sistema:** Executa a consulta indexada Full-Text (`MATCH...AGAINST`) combinada com filtros de produtos ativos e em estoque.
5. **Sistema:** Renderiza a página de resultados (`/search?q=...`) contendo:
   - Contador de resultados encontrados (ex: *"24 produtos encontrados para 'Porcelanato'"*);
   - Coluna de filtros facetados: Categorias, Faixa de Preço (slider), Marcas, Voltagem, Acabamento;
   - Grade de produtos correspondentes.
6. **Ator:** Seleciona um filtro facetado (ex: Marca: *"Portobello"* e Preço: *"R$ 50 a R$ 100"*).
7. **Sistema:** Aplica os filtros via AJAX, atualiza a grade de resultados e ajusta os contadores laterais.
8. **Ator:** Visualiza os itens filtrados e seleciona o produto desejado.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Busca por SKU ou Código de Barras:**
  1. O ator digita diretamente o código SKU ou EAN do produto.
  2. O sistema reconhece a correspondência exata e redireciona diretamente para a Página de Detalhes do Produto (PDP - `UC_CLI_003`).

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Nenhum Resultado Encontrado (Zero Hits):**
  1. A consulta não retorna nenhum produto.
  2. O sistema exibe: *"Nenhum produto encontrado para '[termo]'"*.
  3. O sistema exibe dicas de busca ("Verifique a ortografia", "Tente termos mais genéricos") e recomenda os produtos mais vendidos da loja.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN003:** Filtros técnicos específicos (como Voltagem 110V/220V ou Rendimento de Tinta) são exibidos dinamicamente conforme os produtos retornados na busca.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `search_query` (string): Termo digitado.
- `filters` (array): `brand_id`, `min_price`, `max_price`, `attributes`.
- `sort` (string): Ordenação.

### Saídas:
- Grade com cards de produtos encontrados.
- Filtros dinâmicos laterais com contadores numéricos por faceta.
