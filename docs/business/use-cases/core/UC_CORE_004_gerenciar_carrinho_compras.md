# UC_CORE_004 - Gerenciar Carrinho de Compras

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_004` |
| **Nome** | Gerenciar Carrinho de Compras Persistente & Conversor de Metragem |
| **Módulo** | Núcleo Central (Core) - Pedidos & Carrinho |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine, Banco Redis (Sessões) |
| **Tipo** | Condução / Interação Comercial & Validação de Estoque |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF004](/docs/requirements/functional/functional_requirements.yaml) (Venda múltiplas unidades/conversor), [RF009](/docs/requirements/functional/functional_requirements.yaml) (Carrinho de compras persistente)<br>**RN:** [RN001](/docs/requirements/business_rules/business_rules.yaml) (Venda de caixas fechadas de piso), [RN005](/docs/requirements/business_rules/business_rules.yaml) (Controle rigoroso de estoque pós-adição)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Feedback imediato/Drawer lateral), [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Latência < 200ms) |

---

## 1. 🎯 Descrição Sumária
Permite aos usuários adicionar, alterar quantidades, aplicar cupons e remover mercadorias do carrinho de compras. O carrinho possui suporte especializado a materiais de construção: converte automaticamente a metragem quadrada informada pelo cliente para caixas fechadas indivisíveis, impede a inclusão de itens com quantidade superior ao saldo em estoque físico e implementa persistência híbrida (sessão em Redis para visitantes, mesclando-se automaticamente ao banco de dados no momento do login).

---

## 2. ⚡ Pré-Condições
1. O usuário acessa a plataforma e navega em um produto com status ativo.
2. Deve haver estoque positivo cadastrado para o SKU desejado.

---

## 3. ✅ Pós-Condições
- Itens e quantidades persistidos na estrutura de carrinho (`cart` em Redis para visitantes ou tabela `tbkk_cart` para clientes logados).
- Subtotal recalculado e mini-cart lateral atualizado na interface.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica no botão "Adicionar ao Carrinho" na PDP ou na listagem de produtos.

---

## 5. 🔄 Fluxo Principal (Adicionar Porcelanato em Caixas Fechadas)

1. **Ator:** Na página do produto (Porcelanato Retificado 80x80), informa a quantidade desejada em metros quadrados (ex: `45 m²`) ou número de caixas.
2. **Sistema:** Recupera a regra de conversão cadastrada no SKU: cada caixa cobre exatamente `1.92 m²` (RN001).
3. **Sistema:** Calcula o arredondamento obrigatório para cima: `Math.ceil(45 / 1.92) = 24 caixas` (totalizando `46.08 m²` entregues).
4. **Ator:** Clica em "Adicionar ao Carrinho".
5. **Sistema:** Consulta o saldo disponível em estoque físico em tempo real: saldo atual = 120 caixas (RN005).
6. **Sistema:** Grava o item no carrinho da sessão:
   - `product_id`: 405
   - `quantity`: 24 (caixas)
   - `unit_measure`: `CX`
   - `coverage_total`: 46.08 m²
   - `price_per_box`: R$ 143,80
7. **Sistema:** Abre o painel lateral retrátil (*Cart Drawer*) exibindo:
   - Miniatura da peça, descrição e especificação de caixas/m²;
   - Subtotal calculado;
   - Campo para estimativa de frete por CEP;
   - Botões "Continuar Comprando" e "Ir para o Checkout".
8. O caso de uso encerra com o carrinho pronto para avanço ao checkout.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Mesclagem de Carrinho ao Efetuar Login (Merge Session Cart):**
  1. O usuário navegou como visitante anônimo e adicionou 5 sacos de cimento ao carrinho temporário.
  2. Em seguida, clica em "Login" e autentica-se com sua conta de cliente (`UC_CORE_003`).
  3. O sistema detecta a existência do carrinho temporário em Redis e do carrinho salvo no banco da conta do usuário.
  4. O sistema executa a união (*merge*) inteligente das comandas, somando quantidades e atualizando o banco de dados `tbkk_cart`.
- **FA02 - Alteração de Quantidade no Carrinho:**
  1. No carrinho, o ator clica nos botões `+` ou `-` para alterar a quantidade de caixas.
  2. O sistema revalida o estoque e recalcula instantaneamente os totais sem recarregar a página.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Quantidade Solicitada Superior ao Estoque Físico:**
  1. No passo 5, o usuário solicita 50 caixas, porém há apenas 18 caixas disponíveis no depósito.
  2. O sistema não insere a quantidade excedente e emite um alerta explícito: *"Desculpe! Dispomos de apenas 18 caixas deste porcelanato em nosso estoque para pronta entrega. Ajustamos o carrinho para a quantidade máxima disponível."*.
- **FE02 - Expiração de Item por Inatividade de Sessão Anônima:**
  1. Um visitante anônimo abandona o carrinho por mais de 72 horas.
  2. O Redis purga a chave de sessão temporária expirada, liberando a reserva preliminar para outros compradores.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Variações de Unidades - Pisos e Azulejos):** Impossibilidade de faturar frações de caixas de revestimentos cerâmicos.
- **RN005 (Controle Rigoroso de Inventário):** Validação de disponibilidade de saldo físico a cada adição ou alteração no carrinho.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `product_id`, `quantity`, `area_m2_input` (opcional), `action` (`add`, `update`, `remove`).

### Saídas:
- Objeto do carrinho (`cart_items[]`, `total_weight_kg`, `total_cubage_m3`, `subtotal_amount`).
- Renderização visual atualizada no Drawer de compras.
