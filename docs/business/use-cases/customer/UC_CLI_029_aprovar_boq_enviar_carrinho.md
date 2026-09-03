# UC_CLI_029 - Aprovar BoQ & Enviar ao Carrinho

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_029` |
| **Nome** | Aprovar BoQ & Enviar ao Carrinho |
| **Módulo** | Loja Virtual - Cotações & Projetos (RFQ / BoQ) |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Extensão de `UC_CLI_026` (`<<extend>>`) / Inclui `UC_CLI_006` (`<<include>>`) |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF004](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Venda de múltiplas unidades), [RF009](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Carrinho de compras), [RF018](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Checkout)<br>**RN:** [RN001](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Unidades fracionadas), [RN005](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Estoque), [RN015](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Preços negociados do BoQ)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Agilidade de transição BoQ -> Checkout) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente que teve sua lista de materiais de construção (BoQ - *Bill of Quantities*) cotada e aprovada (`/boq`) transferir instantaneamente todos os itens, quantidades, embalagens calculadas e preços especiais acordados diretamente para o carrinho de compras (`<<include>> UC_CLI_006`), aplicando as condições comerciais negociadas e permitindo prosseguir de imediato para o checkout (`UC_CLI_009`).

---

## 2. ⚡ Pré-Condições
- Projeto RFQ com proposta aceita e lista de materiais BoQ vinculada a SKUs válidos do catálogo.

---

## 3. ✅ Pós-Condições
- Todos os itens da lista BoQ inseridos no carrinho do cliente com preços especiais travados pela cotação.
- Redirecionamento para a tela de checkout.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica no botão "Aprovar BoQ e Comprar Tudo" ou "Enviar Lista ao Carrinho" na página do projeto.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Na página do projeto BoQ (`/boq`), confere a listagem final de materiais (ex: 50 sacos de cimento, 120m² de porcelanato, 15 barras de ferro 3/8").
2. **Ator:** Clica no botão "Aprovar BoQ e Enviar ao Carrinho".
3. **Sistema:** Valida o saldo de estoque físico em tempo real para todos os itens do BoQ (RN005).
4. **Sistema:** Itera sobre a lista de materiais, executando `<<include>> UC_CLI_006 (Adicionar ao Carrinho)` para cada item, aplicando os preços unitários acordados na proposta aceita.
5. **Sistema:** Atualiza a estrutura do carrinho na sessão Redis e na tabela `tbkk_cart`.
6. **Sistema:** Exibe mensagem de sucesso: *"Todos os 3 materiais do seu projeto foram adicionados ao carrinho com suas condições especiais aplicadas!"*.
7. **Sistema:** Redireciona o cliente automaticamente para a tela de Checkout (`UC_CLI_009`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Ajuste Parcial antes de Enviar ao Carrinho:**
  1. O cliente desmarca 1 dos itens da lista que prefere adquirir posteriormente.
  2. O sistema envia apenas os itens remanescentes marcados para o carrinho.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Item da Lista BoQ com Ruptura de Estoque Físico:**
  1. Um dos produtos da cotação teve seu estoque zerado por outra venda no canteiro.
  2. O sistema adiciona todos os itens disponíveis e alerta: *"O item [Nome] está temporariamente indisponível no saldo físico e não foi adicionado. Um vendedor entrará em contato para reposição prioritária."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Variações de Unidades):** Conversão automática de m² para caixas completas no envio ao carrinho.
- **RN005 (Estoque):** Verificação rigorosa no momento da transferência para o carrinho.
- **RN015 & RN017:** Garantia da aplicação da tabela de preços contratada na cotação.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `project_id`, `selected_items` (array de SKUs selecionados).

### Saídas:
- Carrinho abastecido com todos os itens do projeto e redirecionamento para o checkout.
