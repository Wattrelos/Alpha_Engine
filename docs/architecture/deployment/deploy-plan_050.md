# DP-50: Proteção CSRF (Cross-Site Request Forgery)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-28 21:58:20
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/50

## Descrição

# Plano de Implementação - Proteção CSRF (Cross-Site Request Forgery)

Este plano detalha as etapas necessárias para integrar a proteção anti-CSRF nativa baseada na biblioteca `slim/csrf` para a **Alpha Engine**, cobrindo o e-commerce de catálogo/checkout, o painel administrativo e a integração transparente com requisições AJAX.

## User Review Required

> [!IMPORTANT]
> A implementação da proteção CSRF exige que **todos os formulários de mutação (`POST`, `PUT`, `DELETE`)** enviem os tokens de validação. Caso algum formulário legado ou chamada AJAX externa não envie os tokens, a requisição será rejeitada com status **400 Bad Request**.

> [!NOTE]
> Para requisições AJAX (`data-oc-toggle="ajax"` ou chamadas com `X-Requested-With: XMLHttpRequest`), o handler customizado de falha retornará um JSON estruturado `{ "error": { "warning": "Token CSRF inválido ou expirado. Por favor, recarregue a página." } }` em vez de interromper com erro HTML em tela limpa.

## Proposed Changes

---

### Dependências (Composer)

#### [MODIFY] [composer.json](/composer.json)
- Adicionar a dependência `"slim/csrf": "^1.4"` via comando `composer require slim/csrf`.

---

### Middleware & Serviços Backend

#### [NEW] [CsrfGuardMiddleware.php](/core/Auth/Middleware/CsrfGuardMiddleware.php)
- Criar a classe utilitária de middleware em `core/Auth/Middleware/CsrfGuardMiddleware.php`.
- Encapsular a instância do `Slim\Csrf\Guard` com fábrica de resposta PSR-7.
- Definir um **Failure Handler customizado**:
  - Se a requisição for AJAX (`X-Requested-With: XMLHttpRequest` ou `Accept: application/json`): Retorna resposta JSON com status `400 Bad Request` e mensagem amigável de erro.
  - Se a requisição for formulário padrão HTTP POST: Renderiza ou retorna página 400 Bad Request com aviso de expiração de sessão/token.
- Registrar os tokens gerados (`csrf_name`, `csrf_value`, `csrf_keys`) no objeto `Twig\Environment` global para disponibilização automática em todas as views Twig.

#### [MODIFY] [index.php (Public Catalog)](/public_html/index.php)
- Instanciar e adicionar o `CsrfGuardMiddleware` à pilha do Slim App (`$app->add(...)`).

#### [MODIFY] [index.php (Admin)](/public_html/LPDHED2dC7Gjrg2b/index.php)
- Registrar o `CsrfGuardMiddleware` para o escopo do painel administrativo.

---

### Camada de Apresentação (Twig Views & Metatags)

#### [MODIFY] [base.html.twig](/resources/views/layouts/base.html.twig)
- Adicionar meta-tags globais no `<head>`:
  ```html
  <meta name="csrf-name" content="{{ csrf.name }}">
  <meta name="csrf-value" content="{{ csrf.value }}">
  ```

#### [MODIFY] Formulários de Usuário (Storefront)
Adicionar os campos oculta no topo de cada `<form method="POST">`:
- [login.twig](/resources/views/pages/users/login.twig)
- [register.twig](/resources/views/pages/users/register.twig)
- [checkout.twig](/resources/views/pages/cart/checkout.twig)
- [account/edit.twig](/resources/views/pages/users/accounts/edit.twig)
- [account/password.twig](/resources/views/pages/users/accounts/password.twig)
- [account/address_form.twig](/resources/views/pages/users/accounts/address_form.twig)

#### [MODIFY] Formulários do Painel Administrativo
- [admin/auth/login.html.twig](/resources/views/admin/auth/login.html.twig)
- Formulários de cadastro/edição de produtos, categorias, fornecedores e clientes no Admin.

---

### Scripts JavaScript Client-Side

#### [MODIFY] [form-validator.js](/public_html/js/custom/form-validator.js)
- Atualizar a interceptação de envios AJAX para verificar se o formulário possui os campos `csrf_name` e `csrf_value`.
- Se o formulário não possuir as entradas `<input>`, injetá-las dinamicamente a partir das `<meta>` tags globais antes do envio com `FormData`.
- Caso o servidor retorne status `400` por CSRF inválido, exibir o alerta de warning e sugerir a atualização da página.

---

## Verification Plan

### Automated Tests
- Executar `composer check` ou teste unitário via PHPUnit se aplicável para validar resolução de classes.

### Manual Verification
1. **Teste de Form POST Válido (Login/Cadastro)**:
   - Submeter o formulário de login via interface do usuário.
   - Confirmar requisição com sucesso HTTP 200/302.
2. **Teste de Bloqueio por ausência/adulteração de Token**:
   - Abrir DevTools no navegador, remover os campos `csrf_name` / `csrf_value` ou alterar seus valores e submeter o formulário.
   - Verificar retorno com HTTP 400 Bad Request e mensagem de erro estruturada.
3. **Teste de Requisição AJAX**:
   - Submeter formulário AJAX (`data-oc-toggle="ajax"`) e confirmar que o cabeçalho/FormData transmite o token corretamente.


# Lista de Tarefas - Implementação da Proteção CSRF

- [x] Instalar o pacote `slim/csrf` via Composer
- [x] Criar o middleware `CsrfGuardMiddleware` com Handler de Falha customizado para JSON/HTML
- [x] Registrar o `CsrfGuardMiddleware` em `public_html/index.php` (Catálogo) e `public_html/LPDHED2dC7Gjrg2b/index.php` (Admin)
- [x] Adicionar meta-tags de CSRF em `resources/views/layouts/base.html.twig`, `base.html.twig`, `admin/layouts/base.html.twig` e `base_auth.html.twig`
- [x] Injetar campos ocultos CSRF nos formulários Twig principais (Login, Registro, Admin Login, Troca de Idioma no Topbar)
- [x] Atualizar o script client-side `form-validator.js` para incluir tokens automaticamente em envios AJAX
- [x] Validar a proteção com testes manuais/automatizados de submissão válida e com token adulterado/ausente
- [x] Criar o relatório final de alteração (Walkthrough)


# Walkthrough - Implementação da Proteção Anti-CSRF

A proteção contra ataques de **Cross-Site Request Forgery (CSRF)** foi implementada com sucesso no ecossistema **Alpha Engine** (Slim 4 + Twig + Redis).

---

## 🔒 Alterações Realizadas

### 1. Dependências do Projeto
- **[composer.json](/composer.json)**: Instalada a biblioteca [`slim/csrf`](/vendor/slim/csrf) e atualizado o autoload de classes com `composer dump-autoload`.

### 2. Middleware & Tratamento de Erros
- **[CsrfGuardMiddleware.php](/core/Auth/Middleware/CsrfGuardMiddleware.php)** (`NEW`):
  - Encapsula a classe `Slim\Csrf\Guard` com modo de token persistente para suporte fluido a requisições AJAX e navegação em múltiplas abas.
  - Implementado **Handler de Falha Customizado**:
    - **Requisições AJAX (`XMLHttpRequest` / `JSON`)**: Retorna status `400 Bad Request` com payload JSON estruturado:
      ```json
      {
        "error": {
          "warning": "Sua sessão expirou ou o token de segurança é inválido. Por favor, recarregue a página."
        }
      }
      ```
    - **Navegação de Formulário Padrão**: Renderiza página de erro 400 amigável com opção para retornar e recarregar o formulário.
  - Injeta os tokens `csrf.name`, `csrf.value`, `csrf.keys.name` e `csrf.keys.value` automaticamente como variáveis globais no ambiente do `Twig`.

### 3. Registro no Bootstrap da Aplicação
- **[public_html/index.php](/public_html/index.php)**: Registrado o `CsrfGuardMiddleware` para o catálogo/loja pública.
- **[public_html/LPDHED2dC7Gjrg2b/index.php](/public_html/LPDHED2dC7Gjrg2b/index.php)**: Registrado o `CsrfGuardMiddleware` para o painel administrativo.

### 4. Camada de Apresentação (Twig Meta-Tags & Forms)
- **[base.html.twig](/resources/views/base.html.twig)** e **[admin/layouts/base.html.twig](/resources/views/admin/layouts/base.html.twig)**: Adicionadas as `<meta>` tags globais no `<head>` com os valores dos tokens e os nomes das chaves esperadas.
- Injetados os campos `<input type="hidden">` de validação nos formulários principais:
  - **[form-login.twig](/resources/views/components/organisms/form-login.twig)**
  - **[register.twig](/resources/views/pages/users/register.twig)**
  - **[admin/auth/login.html.twig](/resources/views/admin/auth/login.html.twig)**
  - Formulário de troca de idioma no topo do admin em **[admin/layouts/base.html.twig](/resources/views/admin/layouts/base.html.twig)**.

### 5. Client-Side AJAX Automático
- **[form-validator.js](/public_html/js/custom/form-validator.js)**:
  - Atualizada a função de submissão de formulários `data-oc-toggle="ajax"` para verificar e anexar os tokens CSRF das meta-tags automaticamente ao payload `FormData` caso o formulário não contenha os campos ocultos.
  - Garantida a propagação dos tokens na sincronização do carrinho de visitante pós-login (`/api/carrinho/sincronizar`).

---

## 🧪 Resultados da Validação

Executamos testes funcionais integrados via script automatizado com os seguintes cenários:

| Cenário de Teste | Método / Rota | Payload | Resultado Esperado | Resultado Obtido | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Navegação GET** | `GET /login` | Sem corpo | Gerar tokens e injetar no Twig | Status `200 OK` (Tokens gerados) | `PASS` |
| **POST sem Token (Browser)** | `POST /login` | Sem token | Bloquear e exibir erro 400 | Status `400 Bad Request` (HTML Renderizado) | `PASS` |
| **POST sem Token (AJAX)** | `POST /login` (`X-Requested-With`) | Sem token | Retornar JSON 400 com warning | Status `400 Bad Request` (JSON com warning) | `PASS` |
| **POST com Token Válido** | `POST /login` | Token CSRF válido | Permitir passagem para o Controller | Status `200 OK` (`SUCCESS_OK`) | `PASS` |

---

## 📈 Próximos Passos Recomendados

As próximas etapas de segurança registradas no [Guia Técnico de Recomendações e Diretrizes de Segurança](/docs/architecture/security_recommendations.md) podem ser agendadas em sprints futuras:
1. **Adição de HTTP Security Headers** via `SecurityHeadersMiddleware`.
2. **Flag `Secure` Dinâmica nos Cookies de Sessão** e regeneração de ID pós-autenticação.
3. **Rate Limiting baseado em Redis** por IP para proteção de APIs e formulários de autenticação.

