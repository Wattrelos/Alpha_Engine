# DP-14: Refatoração do Carrinho de Compras para Tratar Variações de Produtos

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-22 16:34:44
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/14

## Descrição

# Refatoração do Carrinho de Compras para Tratar Variações de Produtos

Este plano descreve as alterações necessárias no `CartRepository` para que o carrinho de compras lide perfeitamente com variações de produtos (produtos filhos com `master_id > 0`).

## User Review Required

Nenhuma alteração é necessária na tabela do carrinho de compras do banco de dados, pois o carrinho salva o ID do produto em `product_id` (que para variações, armazena o ID da variação filho).

> [!NOTE]
> As variações herdarão as seguintes informações do produto pai quando não tiverem valores específicos próprios configurados:
> - **Preço base:** se a variação tiver preço `0.00`, herdará o preço do pai.
> - **Preço promocional (Special/Discount):** se a variação herdar o preço base do pai, também herdará promoções/descontos progressivos ativos do pai.
> - **Classe de Imposto (Tax Class):** herda a tributação do pai.
> - **Peso & Classe de Peso:** herda as informações de peso do pai para cálculo correto de frete.
> - **Imagem:** herda a imagem principal do pai caso a variação não possua foto específica.
> - **Pontos de recompensa (Reward/Points):** herda os pontos associados ao produto pai.

## Proposed Changes

### Domain Logic (Repository)

#### [MODIFY] [CartRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php)
- Atualizar o método `getProducts()` para suportar o carregamento em lote dos produtos pai das variações presentes no carrinho:
  1. Identificar se algum produto carregado no carrinho possui `master_id > 0` (indicando que é uma variação).
  2. Coletar os IDs de todos os produtos pai (`master_id`) e carregá-los em lote (`getProductsByIds`) em uma única consulta, prevenindo o problema de queries N+1.
  3. Fazer com que as variações herdem os valores de fallback do pai (Preço, Promoção, Peso, Classe de Imposto, Pontos e Imagem) caso os seus próprios campos estejam vazios ou definidos como padrão (`0`/`0.00`/`''`).
- Validar se o restante dos métodos (como `validateAddition()`, `getWeight()`, `getTaxes()`) continuam funcionando corretamente ao usarem os dados enriquecidos da variação.

## Verification Plan

### Automated/Manual Verification
- **Testes de Integração:**
  1. Executar um teste mockando a adição de uma variação com preço herdado (`0.00`) ao carrinho, e validar se o preço final exibido no carrinho é o preço do produto pai.
  2. Validar que o peso da variação herda o peso do pai para o cálculo de frete.
  3. Validar se uma variação com imagem personalizada exibe a foto específica, enquanto uma sem imagem herda a foto do pai.
  4. Executar os testes automatizados existentes (`TestCreateProduct.php`) para garantir a estabilidade do sistema.

- [x] Refactor product loading in [CartRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php)
  - [x] Identify variations (`master_id > 0`) in the cart items list
  - [x] Batch-load parent products to avoid N+1 query loops
  - [x] Implement fallbacks for variation attributes (price, special, tax_class_id, weight, image, reward points, subtract, minimum, shipping)
- [x] Verify that the refactored code works correctly
  - [x] Check PHP syntax (`php -l`) on modified files
  - [x] Run automated tests to check application status
  - [x] Build and execute a scratch integration test showing successful parent inheritance
- [x] Create walkthrough documenting changes

# Walkthrough - Suporte a Variações de Produtos (Imagens e Carrinho de Compras)

Neste ciclo de desenvolvimento, implementamos suporte completo a variações de produtos (produtos filhos com `master_id > 0`) na loja, abrangendo tanto a interface administrativa de imagens quanto o tratamento de regras de negócios no carrinho de compras.

---

## Parte 1: Edição de Imagem em Variações de Produto

### 1. Interface Administrativa
- **Arquivo modificado:** [edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/edit.html.twig)
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
- **Arquivo modificado:** [UpdateProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)
- **Modificações:**
  - Captura dinâmica dos arquivos de imagem associados aos índices das variações através de `$request->getUploadedFiles()`.
  - Tratamento de exclusão: caso a flag `remove_image` esteja presente, o caminho da imagem é limpo.
  - Tratamento de novo arquivo: uploads são movidos para o diretório de mídia `image/product/` utilizando nomes aleatórios seguros e únicos.
  - Salvamento do caminho de imagem no banco de dados para as variações criadas ou atualizadas, eliminando o comportamento antigo que sobrescrevia as imagens das variações com a imagem do produto pai.

---

## Parte 2: Refatoração do Carrinho de Compras

### 1. Lógica do Carrinho (Domínio)
- **Arquivo modificado:** [CartRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php)
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

