# DP-2: # Implementar Exclusão de Produto no Dashboard

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-18 21:17:34
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/2

## Descrição

# Implementar Exclusão de Produto no Dashboard

Este plano detalha a implementação completa da exclusão de produtos no painel administrativo, garantindo integridade referencial no banco de dados e limpeza adequada do cache.

## User Review Required

> [!NOTE]
> A exclusão de produtos é definitiva. Para segurança do usuário, implementamos um diálogo de confirmação nativo antes de submeter a requisição. Os dados históricos de vendas (tabelas de pedidos) são preservados para fins de auditoria e relatórios financeiros.

## Proposed Changes

---

### Roteamento

#### [MODIFY] [Routes.php](/Config/Routes.php)
- Adicionar a rota GET para a exclusão do produto:
  ```php
  $group->get('/produtos/{id:[0-9]+}/excluir', \Alpha\Admin\Controllers\Actions\Catalog\Product\DeleteProductAction::class)->setName('admin.product.delete');
  ```
  Isso segue o mesmo padrão adotado para categorias, fabricantes e fornecedores.

---

### Controllers & Ações

#### [MODIFY] [DeleteProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/DeleteProductAction.php)
- Corrigir a deleção principal para usar a coluna `id` na tabela `product` (em vez do inexistente `product_id`).
- Limpar dados das tabelas ausentes ou incorretas (como `product_special` e `product_to_download` que não existem no banco atual).
- Adicionar a limpeza de dados em novas tabelas identificadas (`product_code`, `product_filter`, `product_option_value`, `product_report`, `product_subscription`, `product_viewed`, `cart`, `customer_wishlist`, `coupon_product`, `subscription_product` e `seo_url`).
- Limpar as chaves de cache relacionadas a este produto específico.
- Retornar um redirecionamento HTTP 302 de volta para a lista de produtos com um parâmetro de query string `success` ou `error`.

#### [MODIFY] [ListProductsAction.php](/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php)
- Receber os parâmetros `success` e `error` da query string e passá-los para a view Twig.

---

### Views

#### [MODIFY] [list.html.twig](/resources/views/admin/pages/products/list.html.twig)
- Exibir os banners de alerta para mensagens de sucesso (`success`) ou erro (`error`).
- Atualizar o link do botão de exclusão de cada produto na tabela para apontar para a nova rota `/LPDHED2dC7Gjrg2b/produtos/{{ product.product_id }}/excluir`.

## Verification Plan

### Manual Verification
- Acessar o dashboard administrativo na rota `/LPDHED2dC7Gjrg2b/produtos`.
- Tentar deletar um produto clicando no botão "Excluir".
- Confirmar no diálogo JavaScript `confirm`.
- Verificar se a página recarrega exibindo a mensagem de sucesso "Produto excluído com sucesso." no topo da lista de produtos.
- Validar no banco de dados se todos os registros do respectivo `product_id` nas tabelas relacionadas foram limpos.

- [x] Configurar nova rota em `Config/Routes.php`
- [x] Ajustar e complementar lógica de exclusão em `DeleteProductAction.php`
- [x] Ajustar `ListProductsAction.php` para receber e repassar mensagens de sucesso/erro
- [x] Atualizar o template Twig `list.html.twig` com banners de alerta e link de exclusão correto
- [x] Validar a implementação no painel administrativo e no banco de dados

# Walkthrough — Implementação da Exclusão de Produto no Dashboard

Implementamos com sucesso a funcionalidade de exclusão de produtos no painel administrativo. 

## Mudanças Realizadas

### 1. Configuração de Rota
- **Arquivo Modificado:** [Routes.php](/Config/Routes.php)
- Mapeamos a rota GET `/produtos/{id:[0-9]+}/excluir` para a classe `DeleteProductAction`. Essa rota agora segue o padrão das outras rotas de exclusão do painel administrativo.

### 2. Ação de Exclusão (Controller)
- **Arquivo Modificado:** [DeleteProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/DeleteProductAction.php)
- Corrigimos a exclusão principal na tabela `product`, ajustando a coluna da cláusula WHERE para `id` (chave primária correta do modelo).
- Removemos queries direcionadas a tabelas inexistentes no banco de dados (`product_special` e `product_to_download`).
- Ampliamos a limpeza do produto a todas as tabelas acessórias associadas (`product_code`, `product_filter`, `product_option_value`, `product_report`, `product_subscription`, `product_viewed`, `cart`, `customer_wishlist`, `coupon_product`, `subscription_product` e `seo_url`).
- Adicionamos invalidação em lote do cache para todas as chaves de idioma, loja e grupos de clientes vinculados a este produto.
- Ajustamos o retorno para redirecionar de volta para a lista com parâmetros de status (`success` ou `error`).

### 3. Listagem de Produtos (Controller)
- **Arquivo Modificado:** [ListProductsAction.php](/core/Admin/Controllers/Actions/Catalog/Product/ListProductsAction.php)
- Capturamos os parâmetros `success` e `error` da query string e injetamos nas variáveis passadas ao renderizador do Twig.

### 4. Template da Listagem
- **Arquivo Modificado:** [list.html.twig](/resources/views/admin/pages/products/list.html.twig)
- Adicionamos blocos condicionais para renderizar banners de alerta caso existam mensagens de sucesso ou erro.
- Atualizamos o atributo `href` do botão "Excluir" para direcionar para `/LPDHED2dC7Gjrg2b/produtos/{{ product.product_id }}/excluir`.

---

## Verificação e Testes

### Script de Teste Automatizado
Criamos e executamos um script de verificação de exclusão ([verify_delete.php](file:///home/kiruma/.gemini/antigravity-ide/brain/8c1fc849-a098-4ae4-b89b-881a1778331f/scratch/verify_delete.php)):
1. Criou um produto fake no banco de dados.
2. Inseriu registros associados em tabelas de relacionamento (`product_description`, `product_to_category`, `product_to_store`, `product_attribute`, `customer_wishlist`, `seo_url`).
3. Instanciou a classe `DeleteProductAction` e disparou a requisição.
4. Validou que a resposta é um redirect 302 com parâmetro `success`.
5. Validou que todos os registros criados nas tabelas associadas foram excluídos completamente.

A saída da execução do teste confirmou o sucesso:
```
Starting product deletion verification script...
Dummy product created with ID: 81666
Invoking DeleteProductAction...
Response Status: 302
Location Header: /LPDHED2dC7Gjrg2b/produtos?success=Produto+exclu%C3%ADdo+com+sucesso.
OK: Cleaned from table agsc_product
OK: Cleaned from table agsc_product_description
OK: Cleaned from table agsc_product_to_category
OK: Cleaned from table agsc_product_to_store
OK: Cleaned from table agsc_product_attribute
OK: Cleaned from table agsc_customer_wishlist
OK: Cleaned from table agsc_seo_url
VERIFICATION SUCCESSFUL: Product deletion works perfectly and cleans up related tables!
```

