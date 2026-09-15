# Especificações BDD: Casos de Uso & Jornada do Cliente (UML / BDD)

Este diretório contém os arquivos de especificação executável em **Gherkin (.feature)** divididos modularmente por grupos de Casos de Uso da **Alpha Engine**, derivados diretamente do diagrama PlantUML [`UseCaseDiagramCustomer.puml`](/docs/business/use-cases/UseCaseDiagramCustomer.puml).

---

## 🗺️ Matriz de Casos de Uso Especializados

| Arquivo Feature | Casos de Uso Cobertos | Tipo de Relação UML | Requisitos Rastreáveis |
| :--- | :--- | :--- | :--- |
| [uc01_uc02_catalogo_busca.feature](/features/use_cases/uc01_uc02_catalogo_busca.feature) | **UC01** (Navegar Catálogo) & **UC02** (Buscar Produtos) | Principal (Visitante / Cliente) | `RF002`, `RF011`, `RF012` |
| [uc03_uc04_adicionar_carrinho_variantes.feature](/features/use_cases/uc03_uc04_adicionar_carrinho_variantes.feature) | **UC03** (Adicionar Carrinho) & **UC04** (Selecionar Variantes) | `<<include>>` Obrigatório | `RF004`, `RF006`, `RF009`, `RN003` |
| [uc05_uc06_login_mesclagem_carrinho.feature](/features/use_cases/uc05_uc06_login_mesclagem_carrinho.feature) | **UC05** (Login / Cadastro) & **UC06** (Mesclar Carrinho) | `<<include>>` Obrigatório | `RF014`, `RF015` |
| [uc07_uc08_uc09_checkout_cupons_guest.feature](/features/use_cases/uc07_uc08_uc09_checkout_cupons_guest.feature) | **UC07** (Checkout), **UC08** (Cupons) & **UC09** (Compra Guest) | `<<extend>>` Opcional | `RF010`, `RF017`, `RF018`, `RN018` |
| [uc12_processamento_pagamentos_gateway.feature](/features/use_cases/uc12_processamento_pagamentos_gateway.feature) | **UC12** (Processamento de Pagamento & Gateway) | `<<include>>` (Sistema / Gateway) | `RF018`, `RF019`, `RF020` |
| [uc10_uc11_pos_venda_pedidos_devolucoes.feature](/features/use_cases/uc10_uc11_pos_venda_pedidos_devolucoes.feature) | **UC10** (Acompanhar Pedidos) & **UC11** (Logística Reversa CDC) | Exclusivo Cliente Logado | `RF016`, `RF022`, `RN009`, `RN011` |

---

## 📚 Documentação Analítica

- [UseCaseDiagramCustomer.md](/features/use_cases/UseCaseDiagramCustomer.md): Especificação textual formal de atores, pré-condições e fluxos principais/alternativos.

---

## 🚀 Como Executar

```bash
# Execução direta via Composer
composer test:behat:use_cases

# Execução direta via Behat CLI
./vendor/bin/behat features/use_cases/ --no-snippets
```
