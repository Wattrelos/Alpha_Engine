# DP-12: Plano de Implementação: Variações de Produto no Padrão On-Premise (Pai e Filho)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-21 13:05:11
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/12

## Descrição

# Plano de Implementação: Variações de Produto no Padrão On-Premise (Pai e Filho)

Este documento planeja a implementação e viabilidade de variações de produto no sistema. Mapearemos os relacionamentos pai e filho diretamente na tabela `product` usando a coluna existente `master_id` para garantir a velocidade e escalabilidade de um e-commerce moderno.

## User Review Required

> [!IMPORTANT]
> **Modelo de Dados Unificado (Pai-Filho):** Cada variação de produto será gravada como um registro na própria tabela `product` (com `master_id` igual ao ID do produto pai). Os campos comuns (como categorias, fabricante, descrição) serão sincronizados automaticamente do pai para as variações no momento do salvamento do pai no painel administrativo. Isso evita a necessidade de reescrever as queries complexas de cálculo de impostos, frete e checkout na loja de frontend.

> [!WARNING]
> **Filtro nas Vitrines Públicas:** Adicionaremos o filtro `p.master_id = 0` na listagem de categorias, busca de produtos e admin geral para evitar a duplicação visual de variações nos resultados do catálogo.

## proposed Changes

---

### Backend: Ajustes nas Consultas, Mappers e Repositórios

#### [MODIFY] [ProductMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ProductMapper.php)
- **`getProducts()`**: Adicionar o filtro `p.master_id = 0` para retornar apenas produtos principais no catálogo público.
- **`getTotalProducts()`**: Adicionar o filtro `p.master_id = 0` na contagem.
- **`getProductVariants(int $productId)`**: Criar esse novo método que retorna todos os produtos filhos associados ao produto pai (onde `master_id = $productId` e `status = 1`).

#### [MODIFY] [ProductRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/ProductRepository.php)
- Criar o método `getProductVariants(int $productId)` que delega a consulta para o `ProductMapper` e retorna a lista de variações do produto.

---

### Dashboard Administrativo: Edição e Gravação de Variações em Lote

#### [MODIFY] [ListProductsAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php)
- Filtrar a listagem principal do admin para exibir apenas produtos pai (`p.master_id = 0`).

#### [MODIFY] [EditProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php)
- Buscar as variações cadastradas do produto pai:
```php
$variants = $productRepo->getProductVariants($productId);
```
- Passar a lista `$variants` para o template.

#### [MODIFY] [UpdateProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)
- Processar o envio das variações na aba correspondente do formulário de edição do produto.
- Lógica de gravação das variações (em lote):
  1. Identificar variações enviadas (cada variação contém ID, nome da variação, SKU, preço adicional/diferenciado, quantidade no estoque, imagem específica e status).
  2. Atualizar variações existentes na tabela `product`.
  3. Criar novas variações (inserir na tabela `product` definindo `master_id = $productId`).
  4. Excluir variações selecionadas para remoção.
  5. Sincronizar em lote os dados do produto pai (Nome base, Descrição, Categoria, Fabricante) para todos os filhos na tabela `product_description` e `product_to_category`.

#### [MODIFY] [edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/edit.html.twig)
- Adicionar abas na tela de edição do produto (Aba 1: **Dados Gerais**, Aba 2: **Variações**).
- Na aba **Variações**, construir uma tabela interativa que lista as variações do produto e permite a edição rápida dos campos (SKU, Nome da Variação, Preço, Quantidade no Estoque, Status) diretamente em lote.
- Adicionar um botão de "Adicionar Variação" para inserir dinamicamente linhas na tabela de variação.

---

### Frontend da Loja Pública: Seleção de Variação na Página de Detalhe

#### [MODIFY] [ShowProductAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Product/ShowProductAction.php)
- Carregar as variações filhas do produto:
```php
$variants = $this->productRepository->getProductVariants($productId);
```
- Formatar o preço e imagens de cada variação usando `tax` e `ImagePresenter`.
- Passar as variações no array de variáveis enviado para a view `show.html.twig`.

#### [MODIFY] [show.html.twig](file:///var/www/html/agsonhos/resources/views/pages/product/show.html.twig)
- Adicionar seletores dinâmicos de variações (cores, tamanhos ou seletor de lista genérico) baseados nas variantes filhas.
- Injetar código JavaScript na página para monitorar a seleção da variação. Quando o usuário clica em uma variação:
  1. Atualizar o ID no formulário de compra: `<input type="hidden" name="product_id" value="[ID_DO_FILHO]">`.
  2. Atualizar dinamicamente em tela o preço exibido, estoque de segurança e a imagem principal do produto com as informações específicas da variante selecionada.

---

## Verification Plan

### Manual Verification
1. No Painel Administrativo, editar um produto existente e acessar a nova aba "Variações".
2. Adicionar 2 variações para o produto (ex: "Cor: Azul" com SKU `TEST-AZUL` e "Cor: Vermelho" com SKU `TEST-VERMELHO`). Gravar o produto pai.
3. Verificar na listagem de produtos do admin e no catálogo do site público se apenas o produto pai é listado, evitando a duplicação visual das variantes.
4. Entrar na página de detalhes do produto no frontend, selecionar a variação "Cor: Azul" e verificar se o preço e estoque exibidos correspondem aos valores específicos da variação.
5. Adicionar a variação ao carrinho e validar no checkout se o produto correto (filho) foi adicionado e as informações fiscais e tributárias permanecem precisas.

# Tarefas: Variações de Produtos no Padrão On-Premise (Pai e Filho)

- [x] Ajustar o `ProductMapper.php` para filtrar produtos pai (`master_id = 0`) no catálogo público e criar o método `getProductVariants()`.
- [x] Ajustar o `ListProductsAction.php` do painel administrativo para filtrar apenas produtos pai (`master_id = 0`).
- [x] Atualizar o `EditProductAction.php` do painel administrativo para carregar as variações do produto.
- [x] Atualizar o `UpdateProductAction.php` do painel administrativo para salvar as variações do produto em lote e sincronizar os campos comuns.
- [x] Implementar a aba "Variações" no formulário de edição do produto no admin (`edit.html.twig`).
- [x] Modificar o `ShowProductAction.php` do frontend para buscar e formatar as variações do produto.
- [x] Modificar a visualização do produto no frontend (`show.html.twig`) para renderizar os seletores de variações e atualizar a tela via JavaScript.

# Walkthrough: Variações de Produto no Padrão On-Premise (Pai e Filho)

Implementamos com sucesso a arquitetura e interface de gerenciamento de **Variações de Produto** seguindo o padrão On-Premise. Isso permite que um produto principal ("Pai") possua diversos SKUs variantes ("Filhos") cadastrados na mesma tabela física de produtos, compartilhando atributos em lote e possuindo preços, estoques e SKUs próprios.

## Mudanças Realizadas

### 1. Camada de Dados (Mappers & Repositórios)
- [ProductMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ProductMapper.php):
  - Adicionamos a cláusula `p.master_id = 0` nos métodos `getProducts()` e `getTotalProducts()` para evitar que variações apareçam repetidas nas listagens gerais e buscas do catálogo de frontend.
  - Criamos o método `getProductVariants(int $productId)` que retorna todos os SKUs filhos daquele produto pai.
- [ProductRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/ProductRepository.php):
  - Criamos o método `getProductVariants(int $productId)` para expor a lista de variações para as Actions.

### 2. Painel Administrativo (Dashboard)
- [ListProductsAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php):
  - Filtramos a listagem de produtos do admin (`master_id = 0`) para exibir apenas os produtos principais na lista.
- [EditProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php):
  - Carregamos as variações do produto pai e as enviamos para a visualização Twig.
- [UpdateProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php):
  - Desenvolvemos a lógica de gravação em lote: cria, atualiza ou exclui variações filhas de acordo com o formulário enviado.
  - Sincronizamos em lote os campos comuns (como categorias, fabricante e descrição) do produto pai para os filhos a cada atualização do pai.
- [edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/edit.html.twig):
  - Criamos abas modernas no formulário de edição (Aba **Geral** e Aba **Variações**).
  - Na aba **Variações**, implementamos a tabela interativa para gerenciar variações em lote (SKU, preço diferenciado, quantidade em estoque, status), com adição dinâmica por JavaScript e remoção visual que envia o status de exclusão para o backend.

### 3. Loja Pública (Frontend)
- [ShowProductAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Product/ShowProductAction.php):
  - Carregamos e formatamos os preços e miniaturas de imagem de cada variação (aplicando as regras fiscais do e-commerce) e enviamos no contexto Twig.
- [show.html.twig](file:///var/www/html/agsonhos/resources/views/pages/product/show.html.twig):
  - Adicionamos os botões de seleção para as variações logo acima das opções tradicionais.
  - Criamos um script que altera dinamicamente no DOM o preço, estoque, imagem principal e o ID do produto a ser enviado ao carrinho (`product_id`) com base na variação ativa clicada pelo cliente.

## Como Validar as Alterações

1. **Gestão Administrativa (Admin):**
   - Acesse o painel de produtos, clique em **Editar** em um produto.
   - Acesse a nova aba **Variações (Padrão On-Premise)**.
   - Adicione variações (ex: "Voltagem: 110v", "Voltagem: 220v").
   - Atribua estoques e preços diferentes, preencha o SKU e clique em **Salvar Alterações**.
   - Os registros filhos serão salvos no banco de dados com `master_id` apontando para o produto editado.

2. **Compra Pública (Frontend):**
   - Acesse a página do produto que você acabou de editar.
   - Note que um grupo de seletores de variação aparecerá acima do botão de compra.
   - Alterne entre as variações: o preço e a imagem principal mudarão dinamicamente.
   - Clique em **Adicionar ao Carrinho**. O ID do SKU filho correspondente será incluído no carrinho de compras com o estoque correto.

