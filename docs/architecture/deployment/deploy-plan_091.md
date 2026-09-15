# DP-91: Implementamos o tratamento e geração dinâmica de imagens sob demanda

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-24 23:04:18
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/91

## Descrição

# Implementamos o tratamento e geração dinâmica de imagens **sob demanda (on-demand image caching)**!

---

### 🚀 Como funciona a nova solução

1. **Interceptação Inteligente de URLs de Cache Vazio**:
   - Criada a action [`ImageCacheAction`](/backend/core/Controller/Actions/Common/ImageCacheAction.php) mapeada nas rotas `/image/cache/{path:.+}` e `/{lang}/image/cache/{path:.+}` em [`Routes.php`](/backend/Config/Routes.php).
   - Quando o cache é limpo e um navegador ou atalho solicita uma URL como `/image/cache/logomark/logo-200x200.png` ou `/image/cache/catalog/produto-300x300.webp`, o Apache repassa a requisição ao Slim (pois o arquivo ainda não existe fisicamente).

2. **Geração Instantânea e Escrita em Disco**:
   - O [`ImagePresenter`](/backend/core/Support/Presenters/ImagePresenter.php) analisa o caminho, localiza a imagem de origem (ou fallback `placeholder.png` / `no-image.png`), gera a miniatura redimensionada com GD e salva o arquivo fisicamente na pasta `public_html/image/cache/...`.
   - Entrega a imagem imediatamente para a requisição atual com cabeçalhos de alta performance (`Content-Type`, `Cache-Control: public, max-age=31536000, immutable`, `Last-Modified` e `ETag` com suporte a `304 Not Modified`).

3. **Performance Máxima nos Acessos Seguintes**:
   - Todas as requisições seguintes para essa mesma imagem são servidas **diretamente pelo Apache como arquivo estático** (`RewriteCond %{REQUEST_FILENAME} !-f`), sem nenhum consumo de PHP ou banco de dados.

4. **Tratamento de Imagens Inexistentes (Fallback Automático)**:
   - Se o arquivo de produto/categoria original não existir ou estiver corrompido, o sistema gera dinamicamente a miniatura do placeholder com as dimensões solicitadas, evitando links quebrados.

---

### 🧪 Testes e Validação
- Criada a suíte de testes [ImageCacheOnDemandTest.php](/tests/Validation/ImageCacheOnDemandTest.php).
- Testada a limpeza de cache completa com `php clean_cache.php` seguida de requisições diretas via cURL: as imagens foram regeradas e servidas com status **HTTP 200** instantaneamente.
- PHPUnit: **93 testes e 339 asserções aprovadas (100% de sucesso)**.

# Relatório de Implementações e Correções

## 1. Atalhos da Área "Minha Conta" Redirecionando para Login

- **Causa Raiz**: O helper [`Customer`](/backend/core/Support/Customer.php) consultava apenas `$_SESSION` e não o Redis, fazendo com que `$customer->isLogged()` retornasse `false` nas subpáginas.
- **Solução**: Implementada busca automática de sessão no Redis no helper `Customer` com cache local em memória por requisição e sincronização bidirecional com `$_SESSION`. Injeção do container no `SessionMiddleware` e adaptação do fluxo de `ResetPasswordAction`.

---

## 2. Aprimoramento e Correção do Script `clean_cache.php`

- **Solução**: O script [`public_html/clean_cache.php`](/public_html/clean_cache.php) e [`backend/clean_twig_cache.php`](/backend/clean_twig_cache.php) agora limpam todas as camadas de cache (Twig `twig_slim` e `twig_setup`, arquivos `*.cache` e `*.json` do Core, proxies em `alpha_proxies`, miniaturas em `image/cache`, OPcache do PHP e chaves de cache do Redis preservando sessões).

---

## 3. Redimensionamento e Geração Dinâmica de Imagens On-Demand (`image/cache/...`)

### Problema
Quando o cache de imagens (`public_html/image/cache/`) era esvaziado, URLs diretas ou atalhos de imagens apontavam para arquivos ainda não existentes no disco, resultando em erro 404 até que alguém navegasse em páginas que recalculassem cada imagem.

### Solução Implementada
1. **Nova Action Sob Demanda ([`ImageCacheAction.php`](/backend/core/Controller/Actions/Common/ImageCacheAction.php))**:
   - Intercepta requisições para `/image/cache/{path:.+}` e `/{lang}/image/cache/{path:.+}`.
   - Extrai o nome da imagem original e as dimensões desejadas (ex: `logomark/logo-200x200.png` -> `logomark/logo.png`, `200x200`).
   - Redimensiona e grava fisicamente a nova imagem em `public_html/image/cache/...` com permissões adequadas.
   - Retorna a resposta binária com HTTP 200, MIME type correto, `Cache-Control: public, max-age=31536000, immutable`, `Last-Modified` e `ETag` (com suporte a HTTP 304 Not Modified).
   - As requisições seguintes são servidas diretamente pelo servidor Apache (`RewriteCond %{REQUEST_FILENAME} !-f`) com máxima velocidade e sem overhead de PHP.

2. **Aprimoramento do [`ImagePresenter.php`](/backend/core/Support/Presenters/ImagePresenter.php)**:
   - Adicionados métodos `resolveSourceAndDimensions()`, `processAndGetPath()` e `getFallbackImage()`.
   - Suporte inteligente a múltiplos formatos de fallback (`placeholder.png`, `no-image.png`, `no_image.png`) com auto-criação de placeholder caso não exista nenhum arquivo.
   - Criação e validação automática de diretórios com permissões `0777` e arquivos gerados com `0666`.

---

## Verificação e Testes
- Criada a suíte de testes [ImageCacheOnDemandTest.php](/tests/Validation/ImageCacheOnDemandTest.php).
- Testes automatizados: **93 testes, 339 asserções (100% de sucesso)**.
- Testado o ciclo completo: Limpeza do cache via `clean_cache.php` seguida de requisições diretas via cURL gerando e servindo as imagens estaticamente no primeiro acesso.

