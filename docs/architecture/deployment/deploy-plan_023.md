# DP-23: Plano de Implementação - Refatoração e Internacionalização do Módulo de Clientes

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-25 20:54:39
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/23

## Descrição

# Plano de Implementação - Refatoração e Internacionalização do Módulo de Clientes

Este plano detalha as alterações de estilo e de idioma (i18n) a serem aplicadas nos arquivos de Clientes do catálogo: `create.html.twig`, `edit.html.twig`, `index.html.twig`, `show.html.twig` e o formulário parcial `_form.html.twig`.

## Alterações Propostas

### 1. Atualizações de Estilo em `components.css`

Adicionaremos as seguintes classes e utilitários para remover estilos inline nas listagens, fichas e formulários de clientes:
- `.is-invalid`: Borda vermelha e brilho ao focar para campos com erro.
- `.align-start`: Alinhamento flex-start (`align-items: flex-start !important`).
- `.flex-column`: Direção flex em coluna.
- `.gap-3`: Espaçamento de `1rem`.
- `.font-medium`: Peso de fonte `500`.
- `.text-success`: Cor verde para textos de sucesso.
- `.address-box` e `.address-box.address-default`: Caixa de endereço em páginas de detalhes com destaque para endereço padrão.
- `.badge-success` e `.badge-primary`: Emblemas (badges) de posicionamento absoluto para destacar endereços padrão.
- `.empty-box`: Caixa com bordas tracejadas para áreas sem cadastros.
- `.status-info`: Pílula de status azulada.
- `.section-header`: Contêiner flexível de títulos de seção com ações laterais.
- `.grid-cards`: Grid responsivo para cartões de endereços.
- `.address-card` e `.address-card.address-default`: Cartão completo de endereço com ações internas.
- `.address-card-actions`: Rodapé do cartão de endereço.
- `.icon-primary`, `.icon-success`, `.icon-indigo`: Cores de ícones no tema.
- `.icon-mr-2`: Margem direita de ícones (`margin-right: 8px !important`).
- `.mb-1` e `.mb-2`: Margens inferiores utilitárias.
- `.text-sm` e `.text-xs`: Tamanhos de fonte utilitários.

### 2. Mapeamento no Middleware de Idiomas

Mapearemos a rota de clientes em [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar o namespace `admin/customer`.
```php
'admin.customer.list'   => 'admin/customer',
'admin.customer.create' => 'admin/customer',
'admin.customer.edit'   => 'admin/customer',
'admin.customer.show'   => 'admin/customer',
```

### 3. Criação de Arquivos de Idiomas (JSON)

Criaremos os arquivos JSON com as strings de tradução nos três idiomas suportados:
- **Português**: [pt-br.admin.customer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.customer.json)
- **Inglês**: [en-gb.admin.customer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.customer.json)
- **Francês**: [fr-fr.admin.customer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.customer.json)

### 4. Substituição de Estilos e Textos nas Views Twig

Substituiremos os estilos inline pelas classes criadas e utilizaremos variáveis `AdminLang` para todas as strings de exibição (com fallbacks) nos seguintes arquivos:
- [index.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/customer/index.html.twig)
- [create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/customer/create.html.twig)
- [edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/customer/edit.html.twig)
- [show.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/customer/show.html.twig)
- [_form.html.twig](file:///var/www/html/agsonhos/resources/views/admin/customer/customer/_form.html.twig)

---

## Detalhes das Alterações por Arquivo

### [MODIFY] [components.css](file:///var/www/html/agsonhos/public_html/css/admin/components.css)
Adicionar as novas regras de estilo e utilitários.

### [MODIFY] [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php)
Mapear as 4 rotas de clientes para o namespace `admin/customer`.

### [NEW] [pt-br.admin.customer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.customer.json)
Traduções em Português do módulo de clientes.

### [NEW] [en-gb.admin.customer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.customer.json)
Traduções em Inglês do módulo de clientes.

### [NEW] [fr-fr.admin.customer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.customer.json)
Traduções em Francês do módulo de clientes.

### [MODIFY] Views de Clientes (`index`, `create`, `edit`, `show`, `_form`)
Refatoração de estilos inline para classes CSS e substituição de strings por `AdminLang`.

---

## Plano de Verificação

### Verificação Manual
1. Abrir a listagem de clientes e testar a exibição da tabela.
2. Acessar a ficha do cliente (`show.html.twig`) e validar o layout de duas colunas, cartões de endereço e histórico de pedidos.
3. Acessar o formulário de criação/edição e conferir a remoção de estilos inline nos inputs e validação visual de erro (outline vermelho).
4. Mudar idioma e validar a tradução completa.

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

# Walkthrough - Refatoração e Internacionalização do Módulo de Clientes

Concluímos com sucesso a remoção de todos os estilos CSS inline e a internacionalização (i18n) completa das views do módulo de Clientes (`index.html.twig`, `_form.html.twig`, `create.html.twig`, `edit.html.twig` e `show.html.twig`).

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
- **Views Twig**: Todos os atributos inline `style="..."` foram removidos e substituídos pelas classes CSS utilitárias correspondentes em todas as views do cliente. No template `edit.html.twig`, removemos tags `</div>` sobressalentes que quebravam o fechamento correto do HTML.

### 2. Internacionalização (i18n)
- **Middleware**: Mapeamos a rota `admin.customer.*` em [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar o namespace de tradução `admin/customer`.
- **Arquivos JSON de Tradução**: Criamos e populamos chaves de internacionalização nos três arquivos de idiomas oficiais da loja:
  - [pt-br.admin.customer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.customer.json) (Português)
  - [en-gb.admin.customer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.customer.json) (Inglês)
  - [fr-fr.admin.customer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.customer.json) (Francês)
  Adicionamos também as chaves `"button_edit_profile"`, `"text_active"` e `"text_inactive"` para contemplar as ações da ficha do cliente e a listagem.
- **Views Twig**: Substituímos os textos hardcoded de todas as páginas por referências ao objeto dinâmico `AdminLang` com fallbacks amigáveis (ex: `{{ AdminLang.text_show_title|default('Ficha do Cliente') }}`).

---

## Verificação e Testes

1. **Validação de Sintaxe PHP**: O middleware de idiomas foi verificado via PHP Linter (`php -l`), com sucesso.
2. **Testes de Integração de Idiomas**: Rodamos com sucesso o script `tests/TestAdminLanguage.php` e `tests/test_translation_loading.php`, confirmando a correta injeção dos cookies de linguagem.
3. **Teste de Renderização de Views do Cliente**: Criamos e executamos um script de testes temporário (`tests/test_customer_views_rendering.php`) que gerou requests simulados para todas as views refatoradas (`index`, `edit`, `show`), validando que a renderização ocorre sem erros de Twig e que os textos mudam dinamicamente dependendo da linguagem (`pt-br`, `en-gb` e `fr-fr`).

