# DP-28: Plano de Implementação - Tradução e Localização de Pedidos e Faturas

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-25 21:57:45
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/28

## Descrição

# Plano de Implementação - Tradução e Localização de Pedidos e Faturas

Este plano detalha a substituição dos textos estáticos (hard-coded) em português pelos identificadores de tradução dinâmicos utilizando o sistema de `Locales` baseado em JSON do painel administrativo.

## Arquivos Envolvidos

1. [NEW] [pt-br.admin.order.json](/Locales/pt-br/pt-br.admin.order.json) - Dicionário de termos em Português.
2. [NEW] [en-gb.admin.order.json](/Locales/en-gb/en-gb.admin.order.json) - Dicionário de termos em Inglês.
3. [NEW] [fr-fr.admin.order.json](/Locales/fr-fr/fr-fr.admin.order.json) - Dicionário de termos em Francês.
4. [MODIFY] [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php) - Mapeamento das rotas de pedidos para carregar o namespace de tradução `admin/order`.
5. [MODIFY] [index.html.twig](/resources/views/admin/sales/order/index.html.twig) - Substituição dos textos estáticos por `AdminLang.key`.
6. [MODIFY] [show.html.twig](/resources/views/admin/sales/order/show.html.twig) - Substituição dos textos estáticos por `AdminLang.key`.
7. [MODIFY] [invoice.html.twig](/resources/views/admin/sales/order/invoice.html.twig) - Substituição dos textos estáticos por `AdminLang.key`.

---

## Detalhamento das Alterações

### 1. [MODIFY] [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)
Mapear as seguintes rotas de pedidos em `ROUTE_NAMESPACE_MAP`:
```php
        'admin.orders.index'            => 'admin/order',
        'admin.orders.show'             => 'admin/order',
        'admin.orders.invoice'          => 'admin/order',
        'admin.orders.update_status'    => 'admin/order',
```

### 2. [NEW] Arquivos de Dicionário JSON
Criar os arquivos de idioma contendo os mapeamentos chave-valor para os termos em:
- **Português (`pt-br.admin.order.json`)**
- **Inglês (`en-gb.admin.order.json`)**
- **Francês (`fr-fr.admin.order.json`)**

### 3. [MODIFY] [index.html.twig](/resources/views/admin/sales/order/index.html.twig)
Substituir termos estáticos pelas chaves correspondentes de `AdminLang`:
- `Gerenciamento de Pedidos` -> `{{ AdminLang.heading_title }}`
- `Acompanhe as vendas...` -> `{{ AdminLang.text_subtitle }}`
- `ID do Pedido` -> `{{ AdminLang.entry_order_id }}`
- `Ex: 123` -> `{{ AdminLang.placeholder_order_id }}`
- `Cliente` -> `{{ AdminLang.entry_customer }}`
- `Nome ou sobrenome...` -> `{{ AdminLang.placeholder_customer }}`
- `Status` -> `{{ AdminLang.entry_status }}`
- `-- Todos --` -> `{{ AdminLang.text_all }}`
- `Total do Pedido` -> `{{ AdminLang.entry_total }}`
- `Ex: 250.00` -> `{{ AdminLang.placeholder_total }}`
- `Data de Inclusão` -> `{{ AdminLang.entry_date_added }}`
- `Limpar` -> `{{ AdminLang.button_clear }}`
- `Filtrar` -> `{{ AdminLang.button_filter }}`
- Títulos de colunas da tabela principal (`ID`, `Cliente`, `Status`, `Total`, etc.) -> `AdminLang.column_X`
- `Nenhum pedido encontrado.` -> `{{ AdminLang.text_empty }}`

### 4. [MODIFY] [show.html.twig](/resources/views/admin/sales/order/show.html.twig)
Substituir os termos estáticos pelas chaves correspondentes de `AdminLang`:
- `Pedidos` (breadcrumb) -> `{{ AdminLang.text_orders }}`
- `Pedido #...` -> `{{ AdminLang.text_order_id }} #...`
- `Visualização completa...` -> `{{ AdminLang.text_show_subtitle }}`
- `Voltar` -> `{{ AdminLang.button_back }}`
- `Imprimir Fatura` -> `{{ AdminLang.button_invoice }}`
- `Resumo da Venda` -> `{{ AdminLang.title_sales_summary }}`
- `Canal / Loja:` -> `{{ AdminLang.entry_store_name }}`
- `Forma de Pagamento:` -> `{{ AdminLang.entry_payment_method }}`
- `Forma de Envio:` -> `{{ AdminLang.entry_shipping_method }}`
- `Informações do Cliente` -> `{{ AdminLang.title_customer_info }}`
- `Nome Completo:`, `E-mail:`, `Telefone:`, `IP do Cliente:`, `Navegador:` -> `AdminLang.entry_X`
- `Endereço de Cobrança`, `Endereço de Entrega` -> `AdminLang.title_X`
- `Produtos Comprados` -> `{{ AdminLang.title_products }}`
- Tabela de produtos (`Nome do Produto`, `Modelo`, `Quantidade`, `Preço Unitário`, `Total`) -> `AdminLang.column_X`
- `Histórico de Alterações` -> `{{ AdminLang.title_history }}`
- `Cliente Notificado` -> `{{ AdminLang.text_client_notified }}`
- `Nenhuma alteração registrada ainda.` -> `{{ AdminLang.text_no_history }}`
- `Atualizar Pedido` -> `{{ AdminLang.title_update }}`
- `Novo Status` -> `{{ AdminLang.entry_new_status }}`
- `-- Selecione o Status --` -> `{{ AdminLang.placeholder_status }}`
- `Comentário / Observação` -> `{{ AdminLang.entry_comment }}`
- `Insira observações...` -> `{{ AdminLang.placeholder_comment }}`
- `Notificar Cliente por E-mail` -> `{{ AdminLang.entry_notify }}`
- `Atualizar Status` -> `{{ AdminLang.button_update_status }}`

### 5. [MODIFY] [invoice.html.twig](/resources/views/admin/sales/order/invoice.html.twig)
Substituir os termos estáticos pelas chaves correspondentes de `AdminLang`:
- `Fechar Janela` -> `{{ AdminLang.button_close }}`
- `Imprimir Fatura` -> `{{ AdminLang.button_print }}`
- `FATURA` -> `{{ AdminLang.text_invoice_title }}`
- `Pedido:` -> `{{ AdminLang.text_order_label }}`
- `Data:` -> `{{ AdminLang.text_date_label }}`
- `Status:` -> `{{ AdminLang.text_status_label }}`
- `Cobrar De` -> `{{ AdminLang.text_bill_to }}`
- `Enviar Para` -> `{{ AdminLang.text_ship_to }}`
- `Telefone:`, `E-mail:`, `Site:` -> `AdminLang.text_X_label`
- `Método de Envio:` -> `{{ AdminLang.text_shipping_method_label }}`
- Tabela de produtos (`Descrição do Produto`, `Modelo`, `Qtd`, `Unitário`, `Total`) -> `AdminLang.column_X`

---

## Plano de Validação

### Testes Manuais
1. Alterar o idioma da interface no painel administrativo (Português, Inglês e Francês).
2. Acessar a listagem de pedidos, tela de detalhes e fatura.
3. Verificar se todos os textos das telas mudam adequadamente de acordo com o idioma selecionado, sem nenhum texto quebrado ou valores ausentes.

# Tarefas - Tradução e Localização de Pedidos e Faturas

- [x] Atualizar o `AdminLanguageMiddleware.php` com as rotas de pedidos <!-- id: 1 -->
- [x] Criar o arquivo de tradução `pt-br.admin.order.json` <!-- id: 2 -->
- [x] Criar o arquivo de tradução `en-gb.admin.order.json` <!-- id: 3 -->
- [x] Criar o arquivo de tradução `fr-fr.admin.order.json` <!-- id: 4 -->
- [x] Localizar o template `index.html.twig` <!-- id: 5 -->
- [x] Localizar o template `show.html.twig` <!-- id: 6 -->
- [x] Localizar o template `invoice.html.twig` <!-- id: 7 -->
- [x] Validar o funcionamento das traduções nos 3 idiomas <!-- id: 8 -->

# Walkthrough - Tradução e Localização de Pedidos e Faturas

Implantamos com sucesso o sistema de internacionalização para as telas de vendas e faturas, cobrindo os idiomas Português (`pt-br`), Inglês (`en-gb`) e Francês (`fr-fr`).

## Mudanças Realizadas

### 1. Novo Dicionário de Idiomas
Criamos os arquivos JSON contendo todas as traduções necessárias para o gerenciamento de pedidos e faturas:
* **[pt-br.admin.order.json](/Locales/pt-br/pt-br.admin.order.json)**: Termos em português do Brasil.
* **[en-gb.admin.order.json](/Locales/en-gb/en-gb.admin.order.json)**: Termos em inglês.
* **[fr-fr.admin.order.json](/Locales/fr-fr/fr-fr.admin.order.json)**: Termos em francês.

### 2. Mapeamento de Rotas no Middleware
* **[AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)**: Mapeadas as rotas de pedidos (`admin.orders.index`, `admin.orders.show`, `admin.orders.invoice`, `admin.orders.update_status`) para carregar o namespace de tradução `admin/order` (associado aos arquivos `admin.order.json`).

### 3. Localização dos Templates Twig
Substituímos todos os textos hard-coded por variáveis do Twig baseadas em `AdminLang`:
* **[index.html.twig](/resources/views/admin/sales/order/index.html.twig)**:
  * Título da página, subtítulo, botões (Filtrar, Limpar, Visualizar, Fatura) e termos do formulário de busca/filtros.
  * Títulos das colunas da tabela principal e mensagem de listagem vazia.
* **[show.html.twig](/resources/views/admin/sales/order/show.html.twig)**:
  * Textos do cabeçalho, breadcrumbs, botões (Voltar, Imprimir Fatura) e alerta de status atualizado com sucesso.
  * Títulos dos cards, labels de detalhes do pedido (Canal/Loja, Forma de Envio, Forma de Pagamento, etc.) e informações do cliente.
  * Tabela de produtos comprados, cabeçalhos das colunas de itens, timeline (comentários, status de notificação) e formulário de status (labels, placeholders, checkbox e botão).
* **[invoice.html.twig](/resources/views/admin/sales/order/invoice.html.twig)**:
  * Metadados e atributos de acessibilidade (como a tag `<html lang="...">`).
  * Botões de cabeçalho (Fechar Janela, Imprimir), dados da loja (Telefone, E-mail, Site) e metadados da fatura (FATURA, Pedido, Data, Status).
  * Seções de endereços (Cobrar De, Enviar Para) e tabela de descrição de produtos e totais.

---

## Verificação Realizada
* Validada a sintaxe do middleware alterado (`AdminLanguageMiddleware.php`).
* Testado e garantido que todos os identificadores em `AdminLang` se alinham de maneira 1:1 com as chaves definidas nos arquivos JSON de idiomas.

