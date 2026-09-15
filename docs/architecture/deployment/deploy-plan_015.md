# DP-15: Exibição de Intervalos de Preços ("A partir de") para Variações

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-22 17:34:12
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/15

## Descrição

# Exibição de Intervalos de Preços ("A partir de") para Variações

Este plano descreve como exibir o preço de entrada ("A partir de R$ X,XX") nos cards de produto do site (Frontend) e o intervalo de preços ("R$ X,XX - R$ Y,YY") na listagem de produtos do painel administrativo (Admin), quando o produto contiver variações ativas.

## User Review Required

As alterações propostas utilizarão subconsultas otimizadas diretamente nas consultas do banco de dados (MySQL) para extrair o preço mínimo e máximo das variações ativas de cada produto. 

> [!NOTE]
> - **No Frontend (Site):** Caso o produto tenha variações com preços distintos do preço do pai, exibiremos **"A partir de R$ X,XX"** (onde R$ X,XX é o preço mínimo entre todas as variações e o pai).
> - **No Admin (Painel):** Exibiremos o intervalo completo de preço das variações na coluna Preço, facilitando o gerenciamento do estoque e dos preços de entrada/saída (ex: **"R$ 90,00 - R$ 120,00"**).

## Proposed Changes

### Component: Database Mapper & Controllers (Backend)

#### [MODIFY] [ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php)
- Adicionar subconsultas SQL no SELECT das funções `getProducts()`, `getProductsByIds()` e `getRelated()` para recuperar os valores `min_variant_price` e `max_variant_price` (preço mínimo e máximo entre as variações ativas do produto).
- Fórmula da subconsulta SQL:
  `MIN(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END)` (garantindo que variações que herdam o preço base do pai herdem corretamente o valor do pai no cálculo).

#### [MODIFY] [ShowCategoryAction.php](/core/Controller/Actions/Category/ShowCategoryAction.php)
- Processar os novos campos `min_variant_price` e `max_variant_price` no loop de formatação de produtos.
- Se houver variações com preços distintos, calcular os impostos e formatar os campos de exibição como `price_min_formatted` e `price_max_formatted`, e definir a flag `has_variants = true`.

#### [MODIFY] [SearchAction.php](/core/Controller/Actions/Product/SearchAction.php)
- Aplicar o mesmo processamento, formatação de impostos e moeda para `price_min_formatted` / `price_max_formatted` no loop de produtos da busca.

#### [MODIFY] [ShowProductAction.php](/core/Controller/Actions/Product/ShowProductAction.php)
- Aplicar a mesma formatação e flags para os **produtos relacionados** exibidos na página de detalhes do produto.

#### [MODIFY] [ListProductsAction.php](/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php)
- Atualizar a consulta SQL da listagem do admin para selecionar `min_variant_price` e `max_variant_price`.
- No loop de formatação, se `min_variant_price` for diferente de `max_variant_price`, definir o campo `price` exibido na tabela como o intervalo `"R$ X,XX - R$ Y,YY"`.

---

### Component: Twig Views (Frontend & Admin)

#### [MODIFY] [product-card.html.twig](/resources/views/pages/product/product-card.html.twig)
- Ajustar o bloco de preço do card para renderizar o texto **"A partir de"** seguido de `price_min_formatted` caso o produto possua variações ativas com preços distintos.

#### [MODIFY] [list.html.twig](/resources/views/admin/pages/products/list.html.twig)
- Exibir o preço formatado (que conterá o intervalo ou o preço único) na tabela do painel administrativo. (Nenhuma alteração é necessária na View além de garantir que a coluna renderize o valor vindo do controller).

## Verification Plan

### Automated & Manual Verification
- **Testes Manuais no Site (Frontend):**
  1. Acessar a página de categoria ou de busca contendo produtos com variações de preços diferentes.
  2. Verificar se o card de produto exibe o texto **"A partir de R$ X,XX"** com o preço correspondente ao menor valor configurado.
- **Testes Manuais no Painel (Admin):**
  1. Acessar a listagem de produtos no admin.
  2. Verificar se produtos com variações exibem o intervalo de preços na tabela (ex: "R$ 90,00 - R$ 120,00").
- **Testes de Regressão:**
  1. Executar os testes automatizados (`tests/TestCreateProduct.php`) para assegurar que não há quebras colaterais.

- [x] Modify database queries to fetch variant price ranges in [ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php)
  - [x] Add subqueries for `min_variant_price` and `max_variant_price` in `getProducts()`
  - [x] Add subqueries for `min_variant_price` and `max_variant_price` in `getProductsByIds()`
  - [x] Add subqueries for `min_variant_price` and `max_variant_price` in `getRelated()`
- [x] Format variation prices in Frontend Controllers
  - [x] Format price range in [ShowCategoryAction.php](/core/Controller/Actions/Category/ShowCategoryAction.php)
  - [x] Format price range in [SearchAction.php](/core/Controller/Actions/Product/SearchAction.php)
  - [x] Format price range in [ShowProductAction.php](/core/Controller/Actions/Product/ShowProductAction.php) (for related products)
- [x] Modify Admin product list query and pricing in [ListProductsAction.php](/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php)
- [x] Update frontend Twig card in [product-card.html.twig](/resources/views/pages/product/product-card.html.twig) to render "A partir de"
- [x] Verify that all modifications work correctly
  - [x] Check PHP syntax (`php -l`) on modified files
  - [x] Run automated tests to check application status
  - [x] Create and run a scratch integration test showing price ranges and "A partir de" formatting
- [x] Create walkthrough documenting changes

# Walkthrough - Suporte a Variações de Produtos (Imagens e Carrinho de Compras)

Neste ciclo de desenvolvimento, implementamos suporte completo a variações de produtos (produtos filhos com `master_id > 0`) na loja, abrangendo tanto a interface administrativa de imagens quanto o tratamento de regras de negócios no carrinho de compras.

---

## Parte 1: Edição de Imagem em Variações de Produto

### 1. Interface Administrativa
- **Arquivo modificado:** [edit.html.twig](/resources/views/admin/pages/products/edit.html.twig)
- **Modificações:**
  - Adicionada a coluna **Imagem** na tabela de variações.
  - Exibição de miniatura arredondada (`50x50px`) da imagem atual da variação (com ícone padrão se não houver).
  - Adicionado botão personalizado de **Upload/Alterar** integrado à linha, ocultando o input de arquivo padrão do browser para um visual mais limpo e premium.
  - Adicionado botão checkbox de **Remover** para apagar imagens de variações existentes.
- **JavaScript & Efeitos (Micro-interações):**
  - Leitura do arquivo selecionado via `FileReader` para exibir um **preview instantâneo** da imagem antes de salvar (destacando o container com borda azul).
  - Escurecimento e filtro de escala de cinza (`grayscale(1)`) aplicados à miniatura atual quando o checkbox **Remover** é marcado, com borda vermelha indicando a exclusão.
  - Suporte completo a novas linhas criadas dinamicamente ao clicar em "Adicionar Variação".

### 2. Lógica de Upload (Servidor)
- **Arquivo modificado:** [UpdateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)
- **Modificações:**
  - Captura dinâmica dos arquivos de imagem associados aos índices das variações através de `$request->getUploadedFiles()`.
  - Tratamento de exclusão: caso a flag `remove_image` esteja presente, o caminho da imagem é limpo.
  - Tratamento de novo arquivo: uploads são movidos para o diretório de mídia `image/product/` utilizando nomes aleatórios seguros e únicos.
  - Salvamento do caminho de imagem no banco de dados para as variações criadas ou atualizadas, eliminando o comportamento antigo que sobrescrevia as imagens das variações com a imagem do produto pai.

---

## Parte 2: Refatoração do Carrinho de Compras

### 1. Lógica do Carrinho (Domínio)
- **Arquivo modificado:** [CartRepository.php](/core/Model/Domain/Repositories/CartRepository.php)
- **Modificações:**
  - Refatorado o método central `getProducts()` do carrinho para que, ao carregar os itens, identifique se algum item inserido trata-se de uma variação (`master_id > 0`).
  - Implementado **carregamento em lote (Batch Loading)** dos produtos pai das variações correspondentes em uma única consulta, respeitando as regras estritas da Alpha Engine de evitar consultas N+1 no banco de dados.
  - Adicionada a **inteligência de herança (fallback)** para variações de produtos. Se a variação tiver valores padrão/vazios no banco de dados, ela herda dinamicamente do pai:
    - **Preço Base:** se o preço da variação for `0.00`, assume o preço do pai.
    - **Promoções/Descontos:** herda promoções (Special) e descontos progressivos do pai caso o preço base seja herdado.
    - **Tributação (Tax Class ID):** herda a classe de imposto para cálculo correto de taxas.
    - **Peso & Unidade de Peso:** herda as características físicas do pai para cálculo preciso de frete na sidebar do carrinho.
    - **Imagem Principal:** herda a imagem principal do pai caso a variação não possua imagem customizada própria.
    - **Pontos de Recompensa & Estoque Subtraível:** herda os pontos do pai.

---

## Verificação e Testes Executados

### 1. Testes Automatizados (Sanity Check)
- Executada a suíte de testes de cadastro de produto da aplicação (`tests/TestCreateProduct.php`) para validar que os fluxos básicos não foram alterados:
  ```bash
  php tests/TestCreateProduct.php
  ```
  **Resultado:** `=== ALL TESTS PASSED SUCCESSFULLY! ===`

### 2. Testes de Integração de Imagens de Variação
- Executado o script de upload mockado de variação ([test_variants_image.php](file:///be475b0f-44a7-4497-983a-43d8d2c26f36/scratch/test_variants_image.php)):
  ```bash
  php /be475b0f-44a7-4497-983a-43d8d2c26f36/scratch/test_variants_image.php
  ```
  **Resultado:** `=== ALL TESTS PASSED! ===` (Mover arquivo física e logicamente + rollback de transação DB funcionou).

### 3. Testes de Integração do Carrinho (Herança de Variação)
- Criado e executado o script de integração do carrinho ([test_cart_inheritance.php](file:///be475b0f-44a7-4497-983a-43d8d2c26f36/scratch/test_cart_inheritance.php)) simulando a adição de uma variação filho com valores zerados:
  ```bash
  php /be475b0f-44a7-4497-983a-43d8d2c26f36/scratch/test_cart_inheritance.php
  ```
  **Resultado:**
  ```text
  === 1. Setup Test Product and Variation ===
  Parent ID: 81679
  Variation ID: 81680

  === 2. Mocking Session and Cart Context ===
  Adding variation to cart...
  Fetching cart products...
  === 3. Verification Assertions ===
  Product Name in Cart: Parent Name - Cor: Vermelho
  Product Model in Cart: PARENT-MODEL-VAR1
  Product Quantity: 1
  Product Base Price in Cart: R$ 150.00 (Expected: R$ 150.00)
  Product Weight in Cart: 2.5 (Expected: 2.50)
  Product Tax Class ID in Cart: 999 (Expected: 999)
  Product Image in Cart: image/parent-image.png (Expected: image/parent-image.png)
  Product Reward Points in Cart: 10 (Expected: 10)

  Assertion PASSED: All variation attributes correctly inherited parent fallbacks!
  Database transaction rolled back successfully.
  === ALL TESTS PASSED! ===
  ```

---

## Parte 3: Exibição de Intervalos de Preços ("A partir de") para Variações

Nesta última parte do desenvolvimento de suporte a variações, implementamos a exibição dinâmica do preço inicial e do intervalo de preços em variações com preços distintos.

### 1. Consultas SQL Otimizadas (Subqueries)
- **Arquivo modificado:** [ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php)
- **Modificações:**
  - Adicionadas subconsultas otimizadas nas buscas de `getProducts()`, `getProductsByIds()` e `getRelated()` para recuperar os valores `min_variant_price` e `max_variant_price` em uma única query.
  - As subconsultas realizam o fallback para o preço base do pai caso o preço da variação seja `0.00` via:
    `MIN(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END)`
    `MAX(CASE WHEN pv.price > 0 THEN pv.price ELSE p.price END)`

### 2. Formatação no Backend e Renderização no Twig
- **Arquivos modificados:**
  - [ShowCategoryAction.php](/core/Controller/Actions/Category/ShowCategoryAction.php)
  - [SearchAction.php](/core/Controller/Actions/Product/SearchAction.php)
  - [ShowProductAction.php](/core/Controller/Actions/Product/ShowProductAction.php)
  - [ListProductsAction.php](/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php)
  - [product-card.html.twig](/resources/views/pages/product/product-card.html.twig)
- **Detalhes:**
  - No site (Frontend), se um produto contiver variações com preços distintos, a flag `has_variants` é definida como `true`, e o card exibe o texto **"A partir de"** com o menor preço calculado (`price_min_formatted`).
  - No painel administrativo (Admin), a tabela de produtos exibe o intervalo de preço completo como `"R$ X,XX - R$ Y,YY"` se houver variações com preços diferentes (ou o preço padrão se os preços forem idênticos ou não houver variações).

---

## Verificação e Testes Executados (Preço de Variações)

### 1. Testes de Integração de Intervalo de Preços
- Criado e executado o script de integração ([test_price_range.php](file:///be475b0f-44a7-4497-983a-43d8d2c26f36/scratch/test_price_range.php)) simulando a criação de um produto pai (R$ 100,00) e duas variações ativas (R$ 90,00 e R$ 120,00):
  ```bash
  php /be475b0f-44a7-4497-983a-43d8d2c26f36/scratch/test_price_range.php
  ```
  **Resultado:**
  ```text
  === 1. Setup Test Product and Variations ===
  Parent ID: 81694
  Variation 1 ID (R$ 90.00): 81695
  Variation 2 ID (R$ 120.00): 81696

  === 2. Retrieve Product via ProductMapper ===
  Product Name: Parent Product Price Test
  Product Price: R$ 100.00
  Min Variant Price retrieved: 90.0000
  Max Variant Price retrieved: 120.0000
  Assertion PASSED: SQL subqueries computed min/max variation prices correctly.

  === 3. Simulating Controller formatting logic (Frontend) ===
  Has Variants: true
  Min Price Formatted: R$ 90,00
  Max Price Formatted: R$ 120,00
  Assertion PASSED: Frontend formatting variables configured correctly.

  === 4. Simulating ListProductsAction formatting logic (Admin) ===
  Admin Price Display: R$ 90,00 - R$ 120,00
  Assertion PASSED: Admin price column shows exact price interval range.
  Database transaction rolled back successfully.
  === ALL TESTS PASSED! ===
  ```

