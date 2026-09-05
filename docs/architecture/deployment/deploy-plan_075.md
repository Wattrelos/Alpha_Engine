# DP-75: Adaptação de Cache para Hospedagens sem Redis (Hostinger)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-12 20:33:59
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/75

## Descrição

# Walkthrough: Adaptação de Cache para Hospedagens sem Redis (Hostinger)

Documentamos a arquitetura de degradação graciosa e otimização de performance para ambientes onde o Redis não está disponível, tirando máximo proveito das tecnologias de hospedagem em nuvem da Hostinger (OPCache + Filesystem).

---

## 1. Como o Projeto Trata o Cache e Sessões sem Redis

O projeto **AgSonhos (Alpha Engine)** já possui uma arquitetura desacoplada e resistente a falhas (GoF Strategy + Graceful Degradation):

1. **Cache de Dados e Consultas (`FilesystemCacheStrategy`):**
   - Todo o cache de catálogo (categorias, produtos, configurações de loja, URLs amigáveis SEO) é salvo em disco em `backend/storage/cache/`.
   - **Integração com OPCache da Hostinger:** Ao ativar o OPCache no painel do Hostinger, o compilador do PHP mantém esses arquivos de cache e os templates Twig pré-compilados diretamente na memória RAM do servidor Web. O resultado é velocidade extrema sem depender de Redis!

2. **Gerenciamento de Sessão e Rate Limiting:**
   - **Sem Redis:** O sistema utiliza nativamente o driver de sessão local do PHP (`$_SESSION`) e cache de requisições por IP em arquivo JSON em `backend/storage/cache/rate_limit/`.

---

## 2. Otimização Aplicada (Latência Zero)

Para evitar que a aplicação tentasse conectar ao servidor Redis e aguardasse o tempo de *timeout* (1 segundo) em cada requisição na Hostinger, implementamos a flag inteligente `REDIS_ENABLED`:

- **Arquivos atualizados:**
  - `backend/.env.example`
  - `backend/core/Auth/Services/AbstractAuthService.php`
  - `backend/core/Auth/Middleware/AdminSessionMiddleware.php`
  - `backend/core/Auth/Middleware/SessionMiddleware.php`
  - `backend/core/Auth/Middleware/RateLimitMiddleware.php`
  - `backend/core/Auth/Middleware/LanguageMiddleware.php`

- **Comportamento:**
  Quando `REDIS_ENABLED=false` ou `REDIS_HOST` estiver em branco no `.env`, a aplicação ignora a tentativa de conexão via socket e assume o modo nativo (OPCache + `$_SESSION`) instantaneamente (com **0ms de atraso**).

---

## 3. Como Configurar no `.env` do Hostinger

No seu arquivo `.env` de produção na Hostinger, basta definir:

```env
# Desabilita tentativas de conexão com o Redis
REDIS_ENABLED=false
REDIS_HOST=
REDIS_PORT=6379
REDIS_PASSWORD=
```

---

## 4. Validação

- **PHPUnit:** 60/60 testes aprovados com **0 falhas**.
- **PHPStan:** `[OK] No errors`.

