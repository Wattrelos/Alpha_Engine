# DP-90: Correção dos Atalhos da Área "Minha Conta" Redirecionando para Login

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-24 22:45:48
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/90

## Descrição

# Plano de Correção: Atalhos da Área "Minha Conta" Redirecionando para Login

## Descrição do Problema

Quando um cliente está autenticado na loja, os seguintes 6 atalhos da página "Minha Conta" (`/pt-br/account`) redirecionam o usuário de volta para a tela de login (`/pt-br/login`):
1. `http://localhost/pt-br/account/edit` (Editar Conta)
2. `http://localhost/pt-br/account/resetar-senha` (Alterar Senha)
3. `http://localhost/pt-br/account/addresses` (Meus Endereços)
4. `http://localhost/pt-br/account/wishlist` (Lista de Desejos)
5. `http://localhost/pt-br/account/orders` (Meus Pedidos)
6. `http://localhost/pt-br/account/transaction` (Minhas Transações)

### Causa Raiz Identificada

1. **Dessincronização entre Redis e `Alpha\Support\Customer`**:
   - A autenticação moderna utiliza Redis para armazenar a sessão do cliente sob a chave `sessao:<session_id>`.
   - `LanguageMiddleware` e `SessionMiddleware` validam a sessão com sucesso no Redis, permitindo que a home da conta (`/pt-br/account`) carregue.
   - Porém, as Actions filhas (`UpdateAction`, `ResetPasswordAction`, `ShowAddressesAction`, `WishlistAction`, `OrdersAction`, `TransactionAction`) invocam o helper `$customer->isLogged()` / `$customerHelper->getId()`.
   - A classe `Alpha\Support\Customer` consultava **apenas** a variável global `$_SESSION` (que não era populada a partir do Redis nos middlewares) e não possuía mecanismo de consulta ao Redis via cookie de sessão.
   - Como resultado, `$customer->isLogged()` retornava `false` e cada Action executava um redirecionamento interno de segurança para `/$lang/login`.

2. **Ajuste no `ResetPasswordAction`**:
   - Para usuários logados sem token/código de recuperação (`?code=...`), a action deve permitir alteração direta de senha via `/account/resetar-senha`, exibindo os breadcrumbs e rota de ação adequados (`Minha Conta` › `Alterar Senha`).

3. **Aliases e Redirecionamentos de Rotas Legadas**:
   - Suporte adicional para rotas no singular (`/account/address`, `/account/password`, `/account/order`) redirecionando de forma limpa para as rotas canônicas.

---

## Proposta de Modificações

### 1. Núcleo de Suporte e Autenticação

#### [MODIFY] [Customer.php](/backend/core/Support/Customer.php)
- Implementar cache em memória do usuário autenticado (`$user`, `$checked`).
- Adicionar suporte a `setUser(?\stdClass $user)` e `clearUser()`.
- No método `getLoggedUser()`:
  1. Verificar cache local e `$_SESSION`.
  2. Se ausente, consultar o Redis utilizando o cookie `session_id` (`sessao:<session_id>`).
  3. Ao encontrar no Redis, sincronizar `$_SESSION` (`logged_user`, `customer_id`, `customer_group_id`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_telephone`) para garantir compatibilidade com toda a aplicação legada e moderna.

#### [MODIFY] [SessionMiddleware.php](/backend/core/Auth/Middleware/SessionMiddleware.php)
- Injetar opcionalmente o `ContainerInterface`.
- Após validar a sessão no Redis ou nativa, sincronizar `$_SESSION` e injetar o usuário no helper `Customer` (`$customerHelper->setUser($user)`).

#### [MODIFY] [LanguageMiddleware.php](/backend/core/Auth/Middleware/LanguageMiddleware.php)
- No método `isUserLogged()`, ao encontrar a sessão no Redis, atualizar o helper `Customer` e sincronizar `$_SESSION`.

#### [MODIFY] [CustomerAuthService.php](/backend/core/Auth/Services/CustomerAuthService.php)
- No método `createSession()`, garantir que `$_SESSION['logged_user']` e todas as chaves flat sejam preenchidas com o `session_id` correto.

---

### 2. Controladores e Rotas

#### [MODIFY] [ResetPasswordAction.php](/backend/core/Controller/Actions/Customer/Auth/ResetPasswordAction.php)
- Ajustar breadcrumbs e action POST quando o usuário estiver autenticado (`account.resetar-senha.logged`).

#### [MODIFY] [Routes.php](/backend/Config/Routes.php)
- Passar o Container para o `SessionMiddleware`: `->add(new SessionMiddleware($app->getContainer()))`.
- Adicionar aliases de compatibilidade para `/account/address`, `/account/password`, `/account/order`, `/account/transactions`.

#### [MODIFY] [LegacyRouteRedirectMiddleware.php](/backend/core/Auth/Middleware/LegacyRouteRedirectMiddleware.php)
- Mapear rotas legadas de `account/*` no parâmetro `?route=`.

---

## Plano de Verificação

### Testes Automatizados
- Executar suite existente do PHPUnit: `./vendor/bin/phpunit`.
- Criar novo teste de integração: `tests/Validation/CustomerAccountShortcutsAuthTest.php`:
  1. Testar acesso anônimo aos 6 atalhos (garantir redirect 302 para `/login`).
  2. Testar acesso autenticado (com sessão Redis e cookie `session_id`) aos 6 atalhos:
     - `/pt-br/account/edit` -> Status 200 (não redireciona para login).
     - `/pt-br/account/resetar-senha` -> Status 200 (não redireciona para login).
     - `/pt-br/account/addresses` -> Status 200 (não redireciona para login).
     - `/pt-br/account/wishlist` -> Status 200 (não redireciona para login).
     - `/pt-br/account/orders` -> Status 200 (não redireciona para login).
     - `/pt-br/account/transaction` -> Status 200 (não redireciona para login).
  3. Testar métodos da classe `Customer` (`isLogged()`, `getId()`, `getFirstName()`, `getEmail()`).


# Relatório de Correções

## 1. Atalhos da Área "Minha Conta" Redirecionando para Login

### Causa Raiz
- A classe [`Customer`](/backend/core/Support/Customer.php) consultava apenas `$_SESSION` e não o Redis para identificar o cliente logado.
- Controladores filhos de `/account/*` validavam `$customer->isLogged()`, resultando em `false` e redirecionando para `/pt-br/login`.

### Solução Aplicada
- Implementado suporte ao Redis no helper `Customer` com cache em memória por requisição e sincronização com `$_SESSION`.
- Injetado o container de dependências no `SessionMiddleware` e sincronizado o estado do cliente em `LanguageMiddleware` e `CustomerAuthService`.
- Suporte contextualmente adaptado no `ResetPasswordAction` para troca direta de senha pelo cliente logado (`/pt-br/account/resetar-senha`).
- Adicionados aliases de rotas para versões no singular (`/account/address`, `/account/password`, `/account/order`, `/account/transactions`).

---

## 2. Refatoração e Correção do Script `clean_cache.php`

### Problemas Anteriores
1. **Cache Core e de Queries Ignorados**: O script limpava apenas a pasta `twig_slim`, deixando mais de 700 arquivos de cache físico do core (`alpha_cache_*.cache`) intocados em `backend/storage/cache/`.
2. **Subdiretórios Incompletos**: Não limpava `twig_setup` nem `alpha_proxies`.
3. **Ausência de Integração com Redis e OPcache**: Não purgueva chaves de cache de aplicação do Redis nem executava `opcache_reset()`.
4. **Falta de Carregamento de Autoload**: Impedia o uso do cliente `Predis\Client` quando executado isoladamente.

### Soluções Aplicadas no [`public_html/clean_cache.php`](/public_html/clean_cache.php)
1. **Expurgo Completo e Seguro de Todas as Camadas de Cache**:
   - **Templates Twig**: `storage/cache/twig_slim/` e `storage/cache/twig_setup/`.
   - **Cache de Dados/Queries do Core**: `storage/cache/*.cache` (`alpha_cache_*.cache`), `storage/cache/*.json` e `storage/cache/*.tmp`.
   - **Proxies de Entidades**: `storage/cache/alpha_proxies/`.
   - **Miniaturas de Imagens**: `public_html/image/cache/`.
   - **OPcache**: Executa `opcache_reset()` quando habilitado no PHP.
   - **Cache Redis**: Purgua chaves de cache de aplicação (`alpha_cache:*`, `cache:*`), preservando com total segurança as sessões ativas (`sessao:*` e `sessao:admin:*`).
2. **Auto-Criação e Permissões**: Garante a recriação de todos os diretórios essenciais com permissão `0777`.
3. **Saída Detalhada e Amigável**: Exibe relatório com a quantidade exata de itens expurgados por categoria.
4. **Sincronização com [`backend/clean_twig_cache.php`](/backend/clean_twig_cache.php)** para execução via CLI.

---

## Verificação e Testes
- Teste executado com `php public_html/clean_cache.php`: 713 arquivos residuais expurgados com sucesso.
- Suite do PHPUnit: **89 testes, 325 asserções (100% de aprovação)**.

