# UC_CLI_020 - Gerenciar Lista de Desejos (Wishlist)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_020` |
| **Nome** | Gerenciar Lista de Desejos |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Favoritos |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF009](/docs/requirements/functional/functional_requirements.yaml) (Carrinho e seleção), [RF012](/docs/requirements/functional/functional_requirements.yaml) (PDP)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Interatividade e feedback instantâneo) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente autenticado salvar produtos favoritos em sua Lista de Desejos (`/account/wishlist`) clicando no ícone de coração na vitrine ou na PDP, consultar os itens favoritados posteriormente, verificar alterações de preço/estoque e mover produtos diretamente para o carrinho de compras.

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- Produto adicionado ou removido da tabela `tbkk_customer_wishlist`.
- Notificação de feedback exibida em tempo real.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica no ícone de coração em um produto ou acessa "Lista de Desejos" no painel.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Na vitrine ou PDP de um produto (ex: *Lustre Pendente Moderno*), clica no botão com ícone de coração "Favoritar".
2. **Sistema:** Valida via AJAX que o usuário está autenticado e insere o `product_id` vinculado ao `customer_id` na tabela `tbkk_customer_wishlist`.
3. **Sistema:** Altera a cor do ícone de coração para preenchido e exibe tooltip *"Produto adicionado à sua Lista de Desejos"*.
4. **Ator:** Posteriormente acessa `/account/wishlist`.
5. **Sistema:** Renderiza a tabela de itens favoritos contendo foto, nome, modelo, disponibilidade de estoque e preço atual.
6. **Ator:** Clica no botão "Adicionar ao Carrinho" em um dos itens da lista de desejos.
7. **Sistema:** Transfere o produto para o carrinho de compras (`UC_CLI_006`) e atualiza o badge.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Remoção de Item da Wishlist:**
  1. O cliente clica no botão "Remover" (ícone de lixeira/coração desmarcado).
  2. O sistema remove o registro e atualiza a listagem.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Visitante Não Logado Clica em Favoritar:**
  1. Um usuário anônimo clica no coração de favoritos.
  2. O sistema exibe um modal amigável: *"Faça login para salvar seus produtos favoritos e acessá-los de qualquer dispositivo."* com botão de login rápido.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Controle de Estoque):** Exibição do status de estoque atualizado dos itens salvos na lista.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `product_id`.

### Saídas:
- Painel de produtos favoritos com botão de ação rápida para o carrinho e indicador de estoque.
