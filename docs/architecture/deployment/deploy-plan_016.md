# DP-16: Adicionar opção de categoria aos produtos no painel de administração

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-22 18:44:01
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/16

## Descrição

# Adicionar opção de categoria aos produtos no painel de administração

Este plano descreve como adicionaremos a seleção de categoria aos produtos no painel de administração, permitindo que os administradores associem produtos a uma ou mais categorias ao criá-los ou editá-los.

## Revisão do usuário necessária

> [!NOTE]
> Implementaremos a seleção de categoria como uma grade de opções de caixa de seleção em um painel rolável. Isso permite selecionar várias categorias facilmente e combina com o design limpo e sofisticado do painel de administração, sem depender de bibliotecas JavaScript externas complexas.

## Perguntas em aberto

Nenhuma nesta etapa, pois o layout do banco de dados já oferece suporte à associação de várias categorias por meio da tabela `product_to_category`, e o painel de administração já utiliza consultas SQL diretas para operações de produto.

# ## Alterações Propostas

### Controladores e Operações de Banco de Dados

#### [MODIFICAR] [CreateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)
- Buscar todas as categorias de `agsc_category_description` e passá-las para a view Twig.

- No manipulador POST: analisar `product_category` do corpo da requisição.

- Na transação: inserir associações para o novo ID do produto em `agsc_product_to_category`.

#### [MODIFICAR] [EditProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php)
- Buscar todas as categorias de `agsc_category_description` e passá-las para a view Twig.

- Buscar as categorias atuais associadas ao produto de `agsc_product_to_category` e passá-las para a view como um array de IDs.

#### [MODIFICAR] [UpdateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)
- No manipulador POST: analisar `product_category` do corpo da requisição.
- Exclua as categorias existentes do produto na transação: `DELETE FROM agsc_product_to_category WHERE product_id = ?`.

- Insira novas associações em `agsc_product_to_category`.

- *Observação:* O código de sincronização existente nesta ação copiará automaticamente as categorias atualizadas para todas as variações de produto filhas.

---

### Modelos de Interface do Usuário

#### [MODIFY] [create.html.twig](/resources/views/admin/pages/products/create.html.twig)
- Adicione uma grade responsiva e rolável de caixas de seleção para seleção de categoria (usando CSS personalizado para combinar com o tema premium).

#### [MODIFICAR] [edit.html.twig](/resources/views/admin/pages/products/edit.html.twig)
- Adicionar a mesma grade de caixas de seleção na aba Geral, pré-selecionando as categorias atualmente associadas ao produto.

---

### Conjunto de Testes

#### [MODIFICAR] [TestCreateProduct.php](/tests/TestCreateProduct.php)
- Adicionar um campo de seleção de categoria aos dados POST simulados na requisição de teste.

- Verificar se a categoria de teste é inserida na tabela `product_to_category`.

- Garantir a limpeza adequada da associação de categorias na fase de finalização do teste.

## Plano de Verificação

### Testes Automatizados
- Executar `php tests/TestCreateProduct.php` para verificar a renderização GET e a inserção POST.

### Verificação Manual
- Acesse as páginas de criação e edição de produtos no painel de controle.

- Adicione um produto, selecione uma ou mais categorias e salve. Verifique se a seleção está correta.

- [x] Carregar a lista de categorias em `CreateProductAction.php` e renderizá-la
- [x] Salvar as categorias de produto na criação em `CreateProductAction.php`
- [x] Carregar a lista de categorias e as categorias de produto atuais em `EditProductAction.php`
- [x] Salvar as categorias de produto na atualização em `UpdateProductAction.php`
- [x] Adicionar o campo de seleção de categoria na interface do usuário em `create.html.twig`
- [x] Adicionar o campo de seleção de categoria na interface do usuário em `edit.html.twig`
- [x] Atualizar o script de teste `tests/TestCreateProduct.php`
- [x] Executar o conjunto de testes para verificar

# Integração de Categorias no Painel de Administração de Produtos

Integramos com sucesso a atribuição de categorias aos fluxos de trabalho de criação e edição de produtos no painel de administração.

## Alterações Implementadas

### Controladores
- **[CreateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)**: Adicionada lógica para consultar todas as categorias disponíveis e passá-las para o template no método GET. Adicionada análise do corpo da requisição para IDs de categoria e lógica para armazená-los em `product_to_category` dentro da transação no método POST.

- **[EditProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php)**: Consultadas todas as categorias e as categorias atuais associadas ao produto para passar para a view Twig.

- **[UpdateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)**: Adicionada análise de IDs de categoria a partir de requisições POST. Integrada lógica de transação para excluir relações de categoria antigas e inserir as atualizadas. A sincronização de categorias de subprodutos existente propaga essas seleções para as variantes filhas automaticamente.

### Templates
- **[create.html.twig](/resources/views/admin/pages/products/create.html.twig)**: Adicionada uma interface de usuário premium com caixas de seleção em formato de grade e rolagem para selecionar categorias de produtos.

- **[edit.html.twig](/resources/views/admin/pages/products/edit.html.twig)**: Adicionada a mesma interface de usuário com caixas de seleção em formato de grade na aba Geral, com categorias pré-selecionadas para registros de produtos existentes.

### Testes
- **[TestCreateProduct.php](/tests/TestCreateProduct.php)**: Aprimoramos o payload simulado para incluir categorias, verificamos a persistência adequada do banco de dados e a limpeza de relações.

## Resultados da Verificação

### Testes Automatizados
A execução do conjunto de testes foi bem-sucedida:
```bash
php tests/TestCreateProduct.php
```

Saída:
```
=== 1. Testando Requisição GET (Renderizando Formulário) ===
Status da Resposta GET: 200
Comprimento do Corpo: 59427
Afirmação APROVADA: A requisição GET retornou o HTML do formulário correto.

=== 2. Testando Requisição POST com Dados Inválidos ===
Status da Resposta POST Inválida: 400
Corpo da Resposta POST Inválida: O nome do produto é obrigatório.

Asserção APROVADA: Validação de nome vazio rejeitada.

=== 3. Testando a Requisição POST com Dados Válidos ===
Status da Resposta POST Válida: 302
ID do Produto Inserido: 81703
Nome do Produto Inserido: Test Product Action 1782153563
Modelo do Produto Inserido: TEST-MODEL-1782153563
Asserção APROVADA: Produto inserido e associado com sucesso na loja e categoria.

=== 4. Limpeza ===
Produto de teste e categorias fictícias limpas com sucesso.

=== TODOS OS TESTES APROVADOS COM SUCESSO! ===
```

