# DP-21: Plano de Implementação - Internacionalização das Configurações da Loja

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-25 20:26:21
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/21

## Descrição

# Plano de Implementação - Internacionalização das Configurações da Loja

Este plano descreve a substituição de textos fixos (hard-coded) na view de configurações da loja (`edit.html.twig`) por variáveis dinâmicas de idiomas (`AdminLang`), carregadas a partir de arquivos JSON de tradução.

## Alterações Propostas

### 1. Novo Namespace de Tradução para Configurações

Mapearemos a rota de configurações no middleware de idiomas do painel para carregar o namespace `admin/setting`.

- No arquivo [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php), adicionaremos no `ROUTE_NAMESPACE_MAP`:
  ```php
  'admin.setting.edit'    => 'admin/setting',
  'admin.setting.update'  => 'admin/setting',
  ```

### 2. Criação dos Arquivos de Idiomas (JSON)

Criaremos os seguintes arquivos contendo as chaves de tradução:

- **Português (pt-br)**: [pt-br.admin.setting.json](/Locales/pt-br/pt-br.admin.setting.json)
- **Inglês (en-gb)**: [en-gb.admin.setting.json](/Locales/en-gb/en-gb.admin.setting.json)
- **Francês (fr-fr)**: [fr-fr.admin.setting.json](/Locales/fr-fr/fr-fr.admin.setting.json)

### 3. Substituição de Textos Hard-coded na View

No arquivo [edit.html.twig](/resources/views/admin/setting/store_setting/edit.html.twig), substituiremos os textos fixos por tags Twig que leem do objeto `AdminLang`, com fallbacks seguros em português para o caso de alguma chave não estar carregada.

Exemplos:
- `<h2>Configurações da Loja</h2>` -> `<h2>{{ AdminLang.heading_title|default('Configurações da Loja') }}</h2>`
- `<span>Geral</span>` -> `<span>{{ AdminLang.tab_general|default('Geral') }}</span>`
- `<label for="config_name"...>` -> `<label for="config_name" class="form-label">{{ AdminLang.entry_name|default('Nome da Loja') }} *</label>`

---

## Detalhes das Alterações por Arquivo

### [MODIFY] [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php)
Adicionar mapeamento no array `ROUTE_NAMESPACE_MAP`:
```php
        'admin.setting.edit'    => 'admin/setting',
        'admin.setting.update'  => 'admin/setting',
```

### [NEW] [pt-br.admin.setting.json](/Locales/pt-br/pt-br.admin.setting.json)
Arquivo com traduções em português.

### [NEW] [en-gb.admin.setting.json](/Locales/en-gb/en-gb.admin.setting.json)
Arquivo com traduções em inglês.

### [NEW] [fr-fr.admin.setting.json](/Locales/fr-fr/fr-fr.admin.setting.json)
Arquivo com traduções em francês.

### [MODIFY] [edit.html.twig](/resources/views/admin/setting/store_setting/edit.html.twig)
Substituir todas as strings em português por referências a `AdminLang.key|default('Fallback PT')`.

---

## Plano de Verificação

### Verificação Automatizada
- Testar a renderização da página sem erros de compilação do Twig.

### Verificação Manual
- Trocar o idioma do painel administrativo no topo (se aplicável) e validar se as abas, campos, botões e labels da página de configurações se adaptam ao idioma selecionado.

- `[x]` Adicionar mapeamento de rotas de configurações no AdminLanguageMiddleware.php
- `[x]` Criar arquivo de tradução em Português pt-br.admin.setting.json
- `[x]` Criar arquivo de tradução em Inglês en-gb.admin.setting.json
- `[x]` Criar arquivo de tradução em Francês fr-fr.admin.setting.json
- `[x]` Substituir textos hard-coded por referências AdminLang na view edit.html.twig
- `[x]` Validar as alterações nos arquivos

# Walkthrough - Refatoração e Internacionalização das Configurações da Loja

Realizamos com sucesso a transferência de estilos inline e a internacionalização (i18n) completa da view de configurações da loja (`edit.html.twig`).

## Alterações Realizadas

### 1. Refatoração de Estilos (CSS)
- **Folha de Estilos**: Adicionamos classes em [components.css](/public_html/css/admin/components.css) para abas (`.tab-nav`, `.tab-button.active`), cabeçalhos (`.section-title`), layout de imagens (`.grid-images`, `.image-upload-card`, etc.), input groups de redes sociais (`.input-group`, `.input-group-addon`, `.input-group-control`) e utilitários de margem/exibição.
- **View Twig**: Todos os atributos inline `style="..."` foram removidos em [edit.html.twig](/resources/views/admin/setting/store_setting/edit.html.twig) e as respectivas classes CSS foram aplicadas.
- **JavaScript**: A função JS `switchTab()` agora gerencia os estados dos botões por meio da adição/remoção da classe `.active`, em vez de injetar propriedades CSS via DOM.

### 2. Internacionalização (i18n)
- **Middleware**: Mapeamos a rota das configurações (`admin.setting.edit` e `admin.setting.update`) no middleware de idioma [AdminLanguageMiddleware.php](/core/Auth/Middleware/AdminLanguageMiddleware.php), fazendo com que carregue o namespace `admin/setting`.
- **Arquivos JSON de Tradução**: Criamos os seguintes arquivos contendo todas as strings traduzidas:
  - [pt-br.admin.setting.json](/Locales/pt-br/pt-br.admin.setting.json) (Português)
  - [en-gb.admin.setting.json](/Locales/en-gb/en-gb.admin.setting.json) (Inglês)
  - [fr-fr.admin.setting.json](/Locales/fr-fr/fr-fr.admin.setting.json) (Francês)
- **View Twig**: Substituímos todos os textos estáticos em [edit.html.twig](/resources/views/admin/setting/store_setting/edit.html.twig) por referências ao objeto dinâmico `AdminLang` com fallbacks amigáveis (ex: `{{ AdminLang.heading_title|default('Configurações da Loja') }}`).

---

## Verificação e Testes

- Validada a sintaxe Twig das tags inseridas na view.
- Validada a consistência das chaves nos arquivos JSON de tradução.
- Validado o carregamento e mapeamento das rotas pelo middleware do painel administrativo.

