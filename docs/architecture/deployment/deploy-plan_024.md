# DP-24: Plano de Implementação - Refatoração e Internacionalização do Módulo de Endereços do Cliente

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-25 21:06:28
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/24

## Descrição

# Plano de Implementação - Refatoração e Internacionalização do Módulo de Endereços do Cliente

Este plano detalha as alterações de estilo e de idioma (i18n) a serem aplicadas nos arquivos de Endereços do Cliente: `create.html.twig` e `edit.html.twig`.

## Alterações Propostas

### 1. Atualizações de Estilo em `components.css`

Adicionaremos as seguintes classes utilitárias para substituir os estilos inline restantes:
- `.max-w-50`: Define largura máxima de `50%` (`max-width: 50% !important;`).
- `.form-checkbox`: Estilização padrão para o input checkbox de endereço padrão (`width: 18px !important; height: 18px !important; accent-color: var(--color-primary); cursor: pointer;`).
- `.form-checkbox-label`: Rótulo estilizado para o checkbox (`font-weight: 600; color: var(--color-text-muted); font-size: 0.95rem; cursor: pointer; user-select: none;`).

### 2. Mapeamento no Middleware de Idiomas

Mapearemos as rotas de endereços de clientes em [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar o namespace `admin/customer`.
```php
'admin.customer.address.create' => 'admin/customer',
'admin.customer.address.edit'   => 'admin/customer',
'admin.customer.address.delete' => 'admin/customer',
```

### 3. Extensão dos Arquivos de Idiomas (JSON)

Adicionaremos as seguintes chaves de tradução nos três arquivos de idiomas existentes para suportar o formulário de endereços:
- **Português**: [pt-br.admin.customer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.customer.json)
- **Inglês**: [en-gb.admin.customer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.customer.json)
- **Francês**: [fr-fr.admin.customer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.customer.json)

Novas chaves:
- `text_address_edit_title`: Título do formulário de edição ("Editar Endereço" / "Edit Address" / "Modifier l'Adresse").
- `text_address_create_subtitle`: Subtítulo da criação ("Cadastre um novo endereço para o cliente:" / "Register a new address for client:" / "Enregistrez une nouvelle adresse pour le client:").
- `text_address_edit_subtitle`: Subtítulo da edição ("Modifique os dados do endereço do cliente:" / "Modify the address details for client:" / "Modifiez les détails de l'adresse du client:").
- `entry_postcode`: Rótulo de CEP.
- `entry_street`: Rótulo de Rua.
- `entry_number`: Rótulo de Número.
- `entry_complement`: Rótulo de Complemento.
- `entry_neighborhood`: Rótulo de Bairro.
- `entry_city`: Rótulo de Cidade.
- `entry_country`: Rótulo de País.
- `entry_zone`: Rótulo de Estado (UF).
- `entry_default_address`: Rótulo de Endereço Padrão.
- `text_select`: Rótulo "Selecione...".
- `button_save_address`: Texto do botão de salvar.
- `button_update_address`: Texto do botão de atualizar.

### 4. Substituição de Estilos e Textos nas Views Twig

Refatoraremos os seguintes arquivos substituindo os estilos inline pelas classes globais do `components.css` e os textos hardcoded pelas variáveis do `AdminLang`:
- [create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/Address/create.html.twig)
- [edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/Address/edit.html.twig)

---

## Detalhes das Alterações por Arquivo

### [MODIFY] [components.css](file:///var/www/html/agsonhos/public_html/css/admin/components.css)
Adicionar `.max-w-50`, `.form-checkbox` e `.form-checkbox-label`.

### [MODIFY] [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php)
Mapear as rotas de endereços de cliente para o namespace `admin/customer`.

### [MODIFY] JSON Locales de Clientes
Adicionar as chaves de tradução do formulário de endereço.

### [MODIFY] [create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/Address/create.html.twig)
Substituir estilos inline por classes utilitárias e textos estáticos por `AdminLang` com fallbacks.

### [MODIFY] [edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/Address/edit.html.twig)
Substituir estilos inline por classes utilitárias e textos estáticos por `AdminLang` com fallbacks.

---

## Plano de Verificação

### Verificação Manual
1. Tentar adicionar e editar endereços na ficha de um cliente.
2. Validar o alinhamento e design dos formulários sem qualquer estilo inline.
3. Testar a tradução alternando o idioma entre pt-br, en-gb e fr-fr.

- `[x]` Adicionar classes de estilo e utilitários em components.css
- `[x]` Mapear rotas de clientes no AdminLanguageMiddleware.php
- `[x]` Criar pt-br.admin.customer.json
- `[x]` Criar en-gb.admin.customer.json
- `[x]` Criar fr-fr.admin.customer.json
- `[x]` Refatorar index.html.twig (CSS + i18n)
- `[x]` Refatorar _form.html.twig (CSS + i18n)
- `[x]` Refatorar create.html.twig (CSS + i18n)
- `[x]` Refatorar edit.html.twig (CSS + i18n)
- `[x]` Refatorar show.html.twig (CSS + i18n)
- `[x]` Validar as modificações
- `[x]` Adicionar classes adicionais em components.css (checkbox/w-50)
- `[x]` Mapear rotas de endereços de clientes no AdminLanguageMiddleware.php
- `[x]` Estender traduções do cliente com chaves de endereço (pt-br, en-gb, fr-fr)
- `[x]` Refatorar Address/create.html.twig (CSS + i18n)
- `[x]` Refatorar Address/edit.html.twig (CSS + i18n)
- `[x]` Validar as novas modificações de endereços

# Walkthrough - Refatoração e Internacionalização do Módulo de Clientes e Endereços

Concluímos com sucesso a remoção de todos os estilos CSS inline e a internacionalização (i18n) completa das views do módulo de Clientes (`index.html.twig`, `_form.html.twig`, `create.html.twig`, `edit.html.twig`, `show.html.twig`) e do submódulo de Endereços (`Address/create.html.twig` e `Address/edit.html.twig`).

## Alterações Realizadas

### 1. Refatoração de Estilos (CSS)
- **Folha de Estilos**: Reutilizamos e integramos novas classes no arquivo global [components.css](file:///var/www/html/agsonhos/public_html/css/admin/components.css) para remover completamente qualquer estilo inline:
  - `.is-invalid`: Borda de erro nos inputs.
  - `.align-start`: Alinhamento flex-start.
  - `.flex-column` e `.gap-3`: Layouts flex organizados.
  - `.font-medium` e `.text-success`: Cores e pesos de fontes padronizados.
  - `.address-box` e `.address-box.address-default`: Layouts específicos para o Livro de Endereços do cliente.
  - `.badge-success` e `.badge-primary`: Emblemas indicando endereço padrão.
  - `.empty-box`: Layout tracejado para estados vazios.
  - `.status-info`: Pílula com o status de pedidos no histórico do cliente.
  - `.max-w-50`: Largura máxima de 50% para campos no formulário de endereço.
  - `.form-checkbox` e `.form-checkbox-label`: Estilização padronizada para checkbox de endereço padrão e seus rótulos.
- **Views Twig**: Todos os atributos inline `style="..."` foram removidos e substituídos pelas classes CSS correspondentes em todas as views. Múltiplos formulários e seções agora usam o sistema de grid e flex do CSS global.

### 2. Internacionalização (i18n)
- **Middleware**: Mapeamos as rotas de clientes e também as rotas de endereços de clientes (`admin.customer.*` e `admin.customer.address.*`) em [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar o namespace de tradução `admin/customer`.
- **Arquivos JSON de Tradução**: Criamos e populamos chaves de internacionalização nos três arquivos de idiomas oficiais da loja:
  - [pt-br.admin.customer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.customer.json) (Português)
  - [en-gb.admin.customer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.customer.json) (Inglês)
  - [fr-fr.admin.customer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.customer.json) (Francês)
  
  Adicionamos chaves específicas para a ficha do cliente, listagem de clientes e todos os campos dos formulários de criação/edição de endereços (ex: `entry_postcode`, `entry_street`, `entry_number`, `entry_default_address`, `button_save_address`, `button_update_address`, etc.).
- **Views Twig**: Substituímos os textos hardcoded de todas as páginas por referências ao objeto dinâmico `AdminLang` com fallbacks amigáveis (ex: `{{ AdminLang.text_address_create|default('Adicionar Endereço') }}`).

---

## Verificação e Testes

1. **Validação de Sintaxe PHP**: O middleware de idiomas foi verificado via PHP Linter (`php -l`), com sucesso.
2. **Testes de Integração de Idiomas**: Rodamos com sucesso o script `tests/TestAdminLanguage.php` e `tests/test_translation_loading.php`, confirmando a correta injeção dos cookies de linguagem.
3. **Teste de Renderização de Views do Cliente e de Endereços**: Criamos e executamos scripts de testes temporários (`tests/test_customer_views_rendering.php` e `tests/test_address_views_rendering.php`) que geraram requests simulados para todas as views refatoradas, validando que a renderização ocorre sem erros de Twig e que os textos mudam dinamicamente dependendo da linguagem selecionada (`pt-br`, `en-gb` e `fr-fr`).

