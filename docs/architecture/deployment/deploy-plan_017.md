# DP-17: Ocultar Produtos e Variações Fora de Estoque

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-22 19:18:31
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/17

## Descrição

# Ocultar Produtos e Variações Fora de Estoque (stock_status_id = 5)

Este plano descreve como atualizaremos os filtros de consulta da loja virtual para ocultar qualquer produto ou variação cuja quantidade seja <= 0 E cujo status de estoque seja "esgotado" (ID = 5).

## Revisão do Usuário Necessária

> [!IMPORTANTE]
> - Essas alterações de filtragem serão aplicadas somente à loja virtual pública (site voltado para o cliente).

> - Produtos e variações com estoque <= 0 e status de estoque = 5 permanecerão visíveis e gerenciáveis ​​no painel de administração.

> - Produtos com estoque <= 0, mas com outros status de estoque (por exemplo, "Sob Encomenda") ainda serão exibidos na loja, permitindo que os clientes os visualizem ou façam pedidos, se permitido pela configuração do sistema.

## Alterações Propostas

### Mapeadores de Produtos da Loja Virtual

#### [MODIFICAR] [ProductMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ProductMapper.php)
- Substituir todas as ocorrências de `p.quantity > 0` por `NOT (p.quantity <= 0 AND p.stock_status_id = 5)`.

- Atualizar as subconsultas de cálculo de variantes (para `min_variant_price`, `max_variant_price`, etc.) para adicionar a condição `AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)` para que as variantes fora de estoque com status 5 sejam excluídas das atualizações de preços e catálogo.

#### [MODIFICAR] [ManufacturerMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ManufacturerMapper.php)
- Atualizar `getManufacturersByCategory` para substituir `p.quantity > 0` por `NOT (p.quantity <= 0 AND p.stock_status_id = 5)`.

---

### Controladores da Loja Virtual

#### [MODIFICAR] [ShowProductAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Product/ShowProductAction.php)
- No loop de renderização de variações (por volta da linha 235), filtre qualquer variação cuja quantidade seja <= 0 e stock_status_id seja 5:

```php

if ((int)$variant['quantity'] <= 0 && (int)$variant['stock_status_id'] === 5) {

continue;

}

```

## Plano de Verificação

### Testes Automatizados
- Execute `php tests/TestCreateProduct.php` para verificar o fluxo de criação existente.
- Criaremos um novo arquivo de teste `tests/TestStockStatusHiding.php` para verificar se:

- Produtos ativos com quantidade <= 0 e stock_status_id != 5 são buscados/visíveis.
- Produtos ativos com quantidade <= 0 e stock_status_id = 5 NÃO são buscados/visíveis.
- Variantes ativas com quantidade <= 0 e stock_status_id = 5 NÃO são retornadas como variantes.

### Verificação Manual
- Visualize um produto na loja com estoque = 0 e status de estoque = 5. Verifique se ele retorna uma página 404.
- Visualize um produto na loja com estoque = 0 e status de estoque = 7 (ou qualquer outro ID). Verifique se ele é renderizado corretamente.

- [x] Carregar a lista de categorias em `CreateProductAction.php` e renderizá-la
- [x] Salvar as categorias de produto na criação em `CreateProductAction.php`
- [x] Carregar a lista de categorias e as categorias de produto atuais em `EditProductAction.php`
- [x] Salvar as categorias de produto na atualização em `UpdateProductAction.php`
- [x] Adicionar o campo de seleção de categoria na interface do usuário em `create.html.twig`
- [x] Adicionar o campo de seleção de categoria na interface do usuário em `edit.html.twig`
- [x] Atualizar o script de teste `tests/TestCreateProduct.php`
- [x] Executar o conjunto de testes para verificar
- [x] Atualizar as consultas em `ProductMapper.php` (getProduct, getProducts, getProductsByIds, getTotalProducts, getRelated e subconsultas)
- [x] Atualizar a consulta em `ManufacturerMapper.php` (getManufacturersByCategory)
- [x] Atualizar `ShowProductAction.php` (loop de renderização de variantes)
- [x] Escrever teste de integração `tests/TestStockStatusHiding.php`
- [x] Executar o conjunto de testes para verificar tudo

# Demonstração da Implementação de Ocultação de Categorias e Estoque

Integramos com sucesso a atribuição de categorias aos fluxos de trabalho de criação/edição de produtos e implementamos a filtragem por status de estoque para ocultar produtos e variações fora de estoque com o ID de status 5 (Esgotado).

---

## 1. Integração de Categorias no Painel de Administração de Produtos

### Alterações Implementadas

#### Controladores
- **[CreateProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)**: Adicionada lógica para consultar todas as categorias disponíveis e passá-las para o template no método GET. Adicionada análise do corpo da requisição para IDs de categoria e lógica para armazená-los em `product_to_category` dentro da transação no método POST.

- **[EditProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php)**: Consulta todas as categorias e as categorias atuais associadas ao produto para passar para a visualização Twig.

- **[UpdateProductAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)**: Adicionada análise do ID da categoria a partir do POST. Integrada lógica de transação para excluir relações de categoria antigas e inserir as atualizadas.

#### Templates
- **[create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/create.html.twig)**: Adicionada uma interface de usuário premium com grade de seleção rolável para selecionar categorias de produtos.
- **[edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/pages/products/edit.html.twig)**: Adicionada a mesma interface de caixa de seleção em grade na aba Geral, com categorias pré-selecionadas para registros de produtos existentes.

#### Testes
- **[TestCreateProduct.php](file:///var/www/html/agsonhos/tests/TestCreateProduct.php)**: Aprimorado o payload simulado para incluir categorias, assegurada a persistência adequada no banco de dados e a limpeza de relações.

---

## 2. Ocultar Produtos e Variações Fora de Estoque (stock_status_id = 5)

### Alterações Implementadas

#### Mapeadores da Loja Virtual
- **[ProductMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ProductMapper.php)**:

- Substituídas as verificações fixas `p.quantity > 0` por `NOT (p.quantity <= 0 AND p.stock_status_id = 5)` em todas as consultas da loja virtual (`getProduct`, `getProducts`, `getProductsByIds`, `getTotalProducts`, `getRelated`).

- Adicionada a condição `AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)` às subconsultas que calculam o preço mínimo/máximo, o nome e a imagem da variante, para que as variantes fora de estoque com ID de status 5 sejam ignoradas corretamente. **[ManufacturerMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ManufacturerMapper.php)**: Atualizou `getManufacturersByCategory` para substituir a condição `p.quantity > 0` por `NOT (p.quantity <= 0 AND p.stock_status_id = 5)`.

#### Controladores da Loja Virtual
- **[ShowProductAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Product/ShowProductAction.php)**: Filtraram todas as variações cuja quantidade é <= 0 e stock_status_id é 5 no loop de variações para renderização da loja virtual.

#### Testes
- **[TestStockStatusHiding.php](file:///var/www/html/agsonhos/tests/TestStockStatusHiding.php)**: Criado um novo script de teste de integração para verificar se os produtos são ocultados/visíveis dependendo do seu ID de status de estoque e quantidade.

---

## 3. Resultados da Verificação

### Testes Automatizados
Ambas as suítes de teste foram executadas com sucesso:

1. **Teste de Criação de Produto**:

``bash

php tests/TestCreateProduct.php

```

*Saída*:

```
=== 1. Testando a Requisição GET (Renderizando o Formulário) ===

Status da Resposta GET: 200
Comprimento do Corpo: 59427
Asserção APROVADA: A requisição GET retornou o HTML do formulário correto.

=== 2. Testando Requisição POST com Dados Inválidos ===
Status da Resposta POST Inválida: 400
Corpo da Resposta POST Inválida: O nome do produto é obrigatório.

Teste APROVADO: Validação de nome vazio rejeitada.

=== 3. Testando Requisição POST com Dados Válidos ===
Status da Resposta POST Válida: 302
ID do Produto Inserido: 81710
Nome do Produto Inserido: Test Product Action 1782155684
Modelo do Produto Inserido: TEST-MODEL-1782155684
Teste APROVADO: Produto inserido e associado com sucesso na loja e categoria.

=== 4. Limpeza ===
Produto de teste e categorias fictícias limpas com sucesso.

=== TODOS OS TESTES APROVADOS COM SUCESSO! ===

```

2. **Teste de Ocultação de Estoque**:

``bash

php tests/TestStockStatusHiding.php

```

*Saída*:

```
=== 1. Configurando Produtos de Teste ===

IDs dos Produtos de Teste inseridos: A: 81707, B: 81708, C: 81709

=== 2. Testando a visibilidade individual do getProduct ===
Asserção APROVADA: O Produto A está visível.

Asserção APROVADA: O Produto B está visível.

Asserção APROVADA: O Produto C está oculto.

=== 3. Testando a filtragem da lista getProducts ===

IDs dos produtos encontrados: 81707, 81708

Asserção APROVADA: A lista getProducts filtra corretamente.

=== 4. Limpeza ===

Limpeza concluída com sucesso.

=== TODOS OS TESTES DE OCULTAÇÃO DO STATUS DO ESTOQUE FORAM APROVADOS COM SUCESSO! ===
```

