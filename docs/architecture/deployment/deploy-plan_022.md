# DP-22: Plano de Implementação - Refatoração e Internacionalização do Módulo de Fabricantes

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-25 20:38:29
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/22

## Descrição

# Plano de Implementação - Refatoração e Internacionalização do Módulo de Fabricantes

Este plano detalha as alterações de estilo e de idioma (i18n) a serem aplicadas nos arquivos de Fabricantes do catálogo: `create.html.twig`, `edit.html.twig` e `index.html.twig`.

## Alterações Propostas

### 1. Atualizações de Estilo em `components.css`

Adicionaremos regras de estilo reutilizáveis para tabelas, imagens e formulários de cadastro:
- `.card-table`: Card customizado para conter tabelas de listagem (sem o padding padrão de cards normais).
- `.table-clean`: Tabela que remove margens extras para se integrar perfeitamente ao card.
- `.table-image`: Imagens de logotipo renderizadas dentro da listagem.
- `.table-image-placeholder`: Placeholder cinza com ícone quando não há imagem.
- `.table-empty-cell`: Célula de estado vazio centralizada.
- `.table-empty-icon`: Ícone exibido no estado vazio.
- `.table-pagination-bar`: Barra inferior para a paginação.
- `.form-label-lg`: Label de formulário em destaque para campos principais (como logotipo).
- `.form-text-muted`: Texto de ajuda pequeno e cinza abaixo de inputs de arquivo.
- `.image-current-preview-box`: Caixa com a pré-visualização da imagem atual na edição.
- `.image-current-preview`: Regra de tamanho da imagem de pré-visualização.
- `.image-current-remove-label`: Texto e ação para exclusão de imagem.
- `.image-current-remove-checkbox`: Estilo do checkbox de exclusão de imagem.

### 2. Mapeamento no Middleware de Idiomas

Mapearemos a rota de fabricantes em [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar o namespace `admin/manufacturer`.
```php
'admin.manufacturer.list'   => 'admin/manufacturer',
'admin.manufacturer.create' => 'admin/manufacturer',
'admin.manufacturer.store'  => 'admin/manufacturer',
'admin.manufacturer.edit'   => 'admin/manufacturer',
'admin.manufacturer.update' => 'admin/manufacturer',
'admin.manufacturer.delete' => 'admin/manufacturer',
```

### 3. Criação de Arquivos de Idiomas (JSON)

Criaremos os arquivos JSON com as strings de tradução nos três idiomas suportados:
- **Português**: [pt-br.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.manufacturer.json)
- **Inglês**: [en-gb.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.setting.json)
- **Francês**: [fr-fr.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.setting.json)

### 4. Substituição de Estilos e Textos nas Views Twig

- **[index.html.twig](file:///var/www/html/agsonhos/resources/views/admin/catalog/manufacturer/index.html.twig)**
- **[create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/catalog/manufacturer/create.html.twig)**
- **[edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/catalog/manufacturer/edit.html.twig)**

Nas três views:
- Removeremos os estilos inline (`style="..."`) e aplicaremos as classes CSS correspondentes.
- Substituiremos todos os textos hard-coded em português por variáveis do objeto `AdminLang` (ex: `AdminLang.heading_title`, `AdminLang.entry_name`), garantindo fallbacks seguros em português.

---

## Detalhes das Alterações por Arquivo

### [MODIFY] [components.css](file:///var/www/html/agsonhos/public_html/css/admin/components.css)
Adicionar novas classes de utilitários de listagem e formulário ao fim do arquivo.

### [MODIFY] [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php)
Mapear as 6 rotas correspondentes ao namespace `admin/manufacturer`.

### [NEW] [pt-br.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.manufacturer.json)
Chaves de tradução em Português.

### [NEW] [en-gb.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.manufacturer.json)
Chaves de tradução em Inglês.

### [NEW] [fr-fr.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.manufacturer.json)
Chaves de tradução em Francês.

### [MODIFY] Views de Fabricantes (`index`, `create`, `edit`)
Refatoração de estilos inline para classes e strings estáticas para referências em `AdminLang`.

---

## Plano de Verificação

### Verificação Manual
1. Abrir a listagem de fabricantes e testar os botões de ação e filtros.
2. Abrir a criação e edição de fabricantes, checar se a pré-visualização de imagem e remoção funcionam corretamente.
3. Testar a alternância entre idiomas e verificar se a interface de Fabricantes traduz por completo.

- `[x]` Adicionar classes de estilo e utilitários em components.css
- `[x]` Mapear rotas de fabricantes no AdminLanguageMiddleware.php
- `[x]` Criar pt-br.admin.manufacturer.json
- `[x]` Criar en-gb.admin.manufacturer.json
- `[x]` Criar fr-fr.admin.manufacturer.json
- `[x]` Refatorar index.html.twig (CSS + i18n)
- `[x]` Refatorar create.html.twig (CSS + i18n)
- `[x]` Refatorar edit.html.twig (CSS + i18n)
- `[x]` Validar as modificações

# Walkthrough - Refatoração e Internacionalização do Módulo de Fabricantes

Realizamos com sucesso a transferência de estilos inline e a internacionalização (i18n) completa das views do módulo de Fabricantes (`index.html.twig`, `create.html.twig` e `edit.html.twig`).

## Alterações Realizadas

### 1. Refatoração de Estilos (CSS)
- **Folha de Estilos**: Adicionamos classes em [components.css](file:///var/www/html/agsonhos/public_html/css/admin/components.css) para as listagens e formulários de fabricante:
  - `.card-table`: Card customizado para conter tabelas (sem padding geral).
  - `.table-clean`: Tabela sem margem padrão para se integrar perfeitamente ao card.
  - `.table-image` e `.table-image-placeholder`: Visualização das logos de fabricante na listagem.
  - `.table-empty-cell` e `.table-empty-icon`: Estado vazio da listagem de fabricantes.
  - `.table-pagination-bar`: Barra de paginação.
  - `.form-label-lg` e `.form-text-muted`: Inputs de arquivo.
  - `.image-current-preview-box`, `.image-current-preview`, `.image-current-remove-label`, `.image-current-remove-checkbox`: Área de pré-visualização e exclusão de imagem existente.
- **Views Twig**: Todos os atributos inline `style="..."` foram removidos e substituídos pelas classes CSS correspondentes em [index.html.twig](file:///var/www/html/agsonhos/resources/views/admin/catalog/manufacturer/index.html.twig), [create.html.twig](file:///var/www/html/agsonhos/resources/views/admin/catalog/manufacturer/create.html.twig) e [edit.html.twig](file:///var/www/html/agsonhos/resources/views/admin/catalog/manufacturer/edit.html.twig).

### 2. Internacionalização (i18n)
- **Middleware**: Mapeamos as rotas do módulo de fabricantes (`admin.manufacturer.*`) no middleware de idioma [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php), fazendo com que carregue o namespace `admin/manufacturer`.
- **Arquivos JSON de Tradução**: Criamos os seguintes arquivos contendo todas as strings traduzidas:
  - [pt-br.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.manufacturer.json) (Português)
  - [en-gb.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.manufacturer.json) (Inglês)
  - [fr-fr.admin.manufacturer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.manufacturer.json) (Francês)
- **Views Twig**: Substituímos todos os textos estáticos nas três views de fabricantes por referências ao objeto dinâmico `AdminLang` com fallbacks amigáveis (ex: `{{ AdminLang.heading_title|default('Gerenciamento de Fabricantes') }}`).

---

## Verificação e Testes

- Validada a sintaxe Twig das tags inseridas nas views de listagem, criação e edição.
- Validada a consistência das chaves nos arquivos JSON de tradução.
- Validada a importação das novas classes de estilos no CSS do painel administrativo.

