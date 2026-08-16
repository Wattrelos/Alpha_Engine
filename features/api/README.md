# Especificações BDD: Camada de Serviços RESTful & Webhooks (/api/*)

Este diretório contém os arquivos de especificação executável em **Gherkin (.feature)** para validar os contratos, formatos JSON, segurança de Webhooks e controle de taxa da API da **Alpha Engine**.

---

## 🌐 Matriz de Endpoints & Especificações da API

| Arquivo Feature | Endpoints Cobertos | Métodos | Mecanismo Backend | Contexto Behat |
| :--- | :--- | :---: | :--- | :--- |
| [carrinho_checkout_api.feature](file:///var/www/html/agsonhos/features/api/carrinho_checkout_api.feature) | `/api/carrinho/dados`<br>`/api/carrinho/salvar-cep`<br>`/api/carrinho/sincronizar` | `POST` | `CalculateVisitorCartAction`, `SaveShippingCepAction`, `SyncCartAction` | `ApiContext` |
| [localizacao_geozonas_api.feature](file:///var/www/html/agsonhos/features/api/localizacao_geozonas_api.feature) | `/api/geo/paises/{id}/estados`<br>`/api/geo/estados/{id}/cidades` | `GET` | `GetGeoZonesAction`, `GetGeoCitiesAction` | `ApiContext` |
| [webhooks_assinatura_hmac.feature](file:///var/www/html/agsonhos/features/api/webhooks_assinatura_hmac.feature) | `/api/webhook/{provider}` | `POST` | `SignatureMiddleware` (HMAC SHA-256) | `ApiContext` |
| [catalogo_busca_autocomplete_api.feature](file:///var/www/html/agsonhos/features/api/catalogo_busca_autocomplete_api.feature) | `/api/busca/autocomplete`<br>`/api/produtos/{id}/variantes/{sku}/estoque` | `GET` | `SearchAction` & `StockChecker` | `ApiContext` |
| [contratos_rest_rate_limit_api.feature](file:///var/www/html/agsonhos/features/api/contratos_rest_rate_limit_api.feature) | Grupo `/api/*` | Todos | `RateLimitMiddleware` (60 req/min) & RFC 7807 | `ApiContext` |

---

## 🚀 Como Executar

```bash
# Execução direta via Composer
composer test:behat:api

# Execução direta via Behat CLI
./vendor/bin/behat features/api/ --no-snippets
```
