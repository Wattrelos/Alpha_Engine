# DP-25: Plano de Implementação - Refatoração e Internacionalização do Módulo de Autenticação Admin

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-25 21:14:18
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/25

## Descrição

# Plano de Implementação - Refatoração e Internacionalização do Módulo de Autenticação Admin

Este plano detalha as alterações de estilo e de idioma (i18n) a serem aplicadas nos arquivos de autenticação administrativa: `login.html.twig`, `setup.html.twig` e `error.html.twig`.

## Alterações Propostas

### 1. Atualizações de Estilo em `components.css` e Layout Base

Adicionaremos as seguintes classes utilitárias e estilos ao `components.css` para centralizar a estilização e remover os estilos inline:
- `.auth-container-large`: Define a largura máxima ampliada para a tela de instalação (`max-width: 580px !important;`).
- `.auth-card-large`: Card de autenticação estendido para acomodar o formulário de instalação inicial.
- `.auth-logo-emoji`: Estilização e animação para o emoji da tela de instalação.
- `.auth-logo-title` e `.auth-logo-subtitle`: Textos estilizados para cabeçalhos de autenticação.
- `.form-group-flex`: Grupo de formulário flex-column padrão.
- `.auth-btn`: Estilização e padding para os botões de autenticação em largura total.
- `.auth-error-icon`: Ícone de erro da tela de aviso de segurança.
- `.text-danger`: Cor vermelha padrão (`color: #ef4444 !important;`).
- `.mt-2` e `.mt-4`: Margens superiores utilitárias (`0.5rem` e `1rem`).
- `@keyframes bounce`: Animação de pulo do emoji, movida da view para o CSS global.

No layout base [base_auth.html.twig](file:///var/www/html/agsonhos/resources/views/admin/layouts/base_auth.html.twig), adicionaremos um bloco `container_class` para que as páginas (como a de setup) possam ampliar o container de autenticação sem estilos inline:
```html
<div class="auth-container {% block container_class %}{% endblock %}">
```

### 2. Mapeamento no Middleware de Idiomas

Mapearemos as rotas de autenticação administrativa em [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php) para carregar o namespace `admin/auth`:
```php
'admin.login.form'   => 'admin/auth',
'admin.login.submit' => 'admin/auth',
'admin.setup.form'   => 'admin/auth',
'admin.setup.submit' => 'admin/auth',
```

### 3. Criação de Arquivos de Idiomas (JSON)

Criaremos os arquivos JSON correspondentes ao namespace `admin/auth`:
- **Português**: [pt-br.admin.auth.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.auth.json)
- **Inglês**: [en-gb.admin.auth.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.auth.json)
- **Francês**: [fr-fr.admin.auth.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.auth.json)

As chaves cobrirão as três telas:
- **Instalação (Setup)**: Título, cabeçalho, subtítulo, campos de formulário (Nome, Sobrenome, E-mail, Usuário, Senha, Confirmar Senha) com placeholders, e botão de finalizar.
- **Login**: Título, subtítulo, campos (Usuário/E-mail, Senha), placeholders, e botão de entrar.
- **Aviso de Erro (Error)**: Título da página, cabeçalho da falha, subtítulo, aviso de bloqueio por excesso de tentativas, contador de redirecionamento, e botão de voltar para o login.

### 4. Substituição nas Views Twig

Refatoraremos as views para utilizar classes CSS globais e traduzir todas as strings estáticas:
- [setup.html.twig](file:///var/www/html/agsonhos/resources/views/admin/auth/setup.html.twig)
- [error.html.twig](file:///var/www/html/agsonhos/resources/views/admin/auth/error.html.twig)
- [login.html.twig](file:///var/www/html/agsonhos/resources/views/admin/auth/login.html.twig)

---

## Plano de Verificação

### Verificação Manual
1. Acessar a tela de login administrativo e verificar a aparência e tradução.
2. Acessar a tela de configuração inicial (setup) e verificar o layout de duas colunas ampliado.
3. Testar a tela de erro de autenticação e verificar o contador e a tradução.
4. Validar nos idiomas `pt-br`, `en-gb` e `fr-fr`.

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
- `[x]` Atualizar base_auth.html.twig (container_class block)
- `[x]` Adicionar classes de autenticação no components.css
- `[x]` Mapear rotas de autenticação no AdminLanguageMiddleware.php
- `[x]` Criar pt-br.admin.auth.json
- `[x]` Criar en-gb.admin.auth.json
- `[x]` Criar fr-fr.admin.auth.json
- `[x]` Refatorar setup.html.twig (CSS + i18n)
- `[x]` Refatorar error.html.twig (CSS + i18n)
- `[x]` Refatorar login.html.twig (CSS + i18n)
- `[x]` Validar as modificações de autenticação

# Walkthrough - Refatoração e Internacionalização dos Módulos de Clientes, Endereços e Autenticação Admin

Concluímos com sucesso a remoção de todos os estilos CSS inline e a internacionalização (i18n) completa das views do módulo de Clientes (`index.html.twig`, `_form.html.twig`, `create.html.twig`, `edit.html.twig`, `show.html.twig`), do submódulo de Endereços (`Address/create.html.twig` e `Address/edit.html.twig`), e do módulo de Autenticação Admin (`login.html.twig`, `setup.html.twig` e `error.html.twig`).

## Alterações Realizadas

### 1. Refatoração de Estilos (CSS)
- **Folha de Estilos**: Integramos e reutilizamos novas classes no arquivo global [components.css](file:///var/www/html/agsonhos/public_html/css/admin/components.css) para remover os estilos inline:
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
  - `.auth-container-large`: Largura máxima de 580px para o container de autenticação da instalação.
  - `.auth-card-large`: Card estendido com padding de `2.5rem` e sombras profundas.
  - `.auth-logo-emoji`: Estilo com animação `@keyframes bounce` para o emoji de instalação.
  - `.auth-logo-title` e `.auth-logo-subtitle`: Tipografia padronizada em conformidade com as variáveis do tema.
  - `.form-group-flex`: Direção flex em coluna para organizar rótulos e campos dos formulários.
  - `.auth-btn`: Botão em bloco com padding e fonte aumentados.
  - `.auth-error-icon`: Alinhamento para o ícone de aviso de segurança.
  - `.text-danger`: Vermelho padrão do tema (`#ef4444`).
- **Views Twig**: Todos os atributos inline `style="..."` foram removidos e substituídos pelas classes CSS correspondentes em todas as views. No arquivo de layout [base_auth.html.twig](file:///var/www/html/agsonhos/resources/views/admin/layouts/base_auth.html.twig), adicionamos o bloco `container_class` para permitir o redimensionamento dinâmico sem estilos inline.

### 2. Internacionalização (i18n)
- **Middleware**: Mapeamos todas as rotas do cliente, endereços e rotas de autenticação (login/setup) no middleware de idiomas [AdminLanguageMiddleware.php](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminLanguageMiddleware.php), vinculando-as aos namespaces de tradução `admin/customer` e `admin/auth`.
- **Arquivos JSON de Tradução**: Criamos e populamos arquivos JSON estruturados nos três idiomas oficiais da loja:
  - **Clientes / Endereços**: [pt-br.admin.customer.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.customer.json), [en-gb.admin.customer.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.customer.json) e [fr-fr.admin.customer.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.customer.json).
  - **Autenticação Admin**: [pt-br.admin.auth.json](file:///var/www/html/agsonhos/Locales/pt-br/pt-br.admin.auth.json), [en-gb.admin.auth.json](file:///var/www/html/agsonhos/Locales/en-gb/en-gb.admin.auth.json) e [fr-fr.admin.auth.json](file:///var/www/html/agsonhos/Locales/fr-fr/fr-fr.admin.auth.json).
- **Views Twig**: Substituímos os textos hardcoded de todas as páginas por referências ao objeto dinâmico `AdminLang` com fallbacks amigáveis. Nos avisos dinâmicos da página de erro de login, utilizamos os filtros `|format` e `|raw` do Twig para injetar os valores traduzidos mantendo a estilização.

---

## Verificação e Testes

1. **Validação de Sintaxe PHP**: O middleware de idiomas e arquivos PHP associados foram validados sem erros.
2. **Testes de Integração de Idiomas**: Executamos com sucesso a suíte de testes de linguagem do admin.
3. **Teste de Renderização de Views**: Desenvolvemos e executamos scripts de testes temporários para as views do Cliente, Endereços e Autenticação (Login, Setup, Error) simulando requisições em todos os idiomas. Todos os testes de compilação, carregamento de chaves de tradução e renderização passaram com sucesso.

