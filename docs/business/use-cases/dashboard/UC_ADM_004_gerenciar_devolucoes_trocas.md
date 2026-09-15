# UC_ADM_004 - Gerenciar Devoluções & Trocas (RMA Admin)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_004` |
| **Nome** | Gerenciar Devoluções, Trocas & Logística Reversa |
| **Módulo** | Painel Administrativo - Operações de Negócio |
| **Atores Primários** | Operador do Painel (*Operator*), Administrador Geral (*Admin*) |
| **Atores Secundários** | Centro de Distribuição / Equipe de Vistoria, Gateway de Pagamento, Sistema Alpha Engine |
| **Tipo** | Condução / Pós-Venda e RMA |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF020](/docs/requirements/functional/functional_requirements.yaml) (NF-e de entrada de devolução), [RF023](/docs/requirements/functional/functional_requirements.yaml) (Gestão administrativa)<br>**RN:** [RN009](/docs/requirements/business_rules/business_rules.yaml) (Política de devolução), [RN010](/docs/requirements/business_rules/business_rules.yaml) (Critérios de troca), [RN011](/docs/requirements/business_rules/business_rules.yaml) (Arrependimento 7 dias CDC), [RN012](/docs/requirements/business_rules/business_rules.yaml) (Documentação fiscal)<br>**RNF:** [RNF006](/docs/requirements/non_functional/non_functional_requirements.yaml) (Lock otimista em triagem de RMA) |

---

## 1. 🎯 Descrição Sumária
Permite aos analistas de atendimento e gerentes de SAC conduzir a triagem dos chamados de devolução e troca abertos pelos clientes (`UC_CLI_023`), emitir autorizações de postagem reversa (Correios/transportadora), registrar o laudo de inspeção física do Centro de Distribuição, emitir a Nota Fiscal de Entrada de devolução e homologar o estorno financeiro ou a concessão de crédito/vale-compras.

---

## 2. ⚡ Pré-Condições
- Solicitação de RMA cadastrada no sistema com status `Pendente de Análise`.

---

## 3. ✅ Pós-Condições
- Chamado de RMA homologado e finalizado (`tbkk_product_return`).
- NF-e de entrada emitida, estorno financeiro executado no gateway ou crédito concedido ao cliente (`tbkk_customer_transaction`) e itens repostos no estoque físico.

---

## 4. 🚀 Gatilho (Trigger)
O operador acessa "Vendas > Devoluções / RMA" no painel administrativo.

---

## 5. 🔄 Fluxo Principal (Homologação de Devolução com Estorno)

1. **Ator:** Acessa a fila de chamados de RMA e abre o protocolo `RMA-2026-0045`.
2. **Sistema:** Exibe as fotos anexadas pelo cliente, número da NF-e original, motivo declarado e histórico do pedido.
3. **Ator:** Valida a conformidade legal (dentro do prazo de 7 dias do CDC para compras entregues - RN011) e clica em "Aprovar Triagem".
4. **Sistema:** Gera o código de logística reversa e despacha por e-mail ao cliente.
5. **Ator:** Após o recebimento do pacote no CD e aprovação do laudo de embalagem intacta, clica em "Homologar Recebimento Físico".
6. **Ator:** Seleciona a modalidade de liquidação "Processar Estorno Financeiro".
7. **Sistema:** Dispara a chamada de estorno via API no gateway de pagamento (PIX ou Cartão), emite a Nota Fiscal de Entrada de Devolução (RN012), retorna as mercadorias ao estoque ativo (RN005) e conclui o protocolo de RMA.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Emissão de Cupom de Vale-Compras:**
  1. O cliente optou por vale-compras em vez de estorno no cartão.
  2. O operador clica em "Emitir Vale-Compras".
  3. O sistema gera um código de cupom de uso único no valor exato da devolução e credita na conta do cliente.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Recusa por Violação / Mercadoria Danificada pelo Usuário:**
  1. A equipe do CD detecta que o piso foi assentado com argamassa ou a embalagem foi violada (RN010/RN011).
  2. O operador anexa o laudo de recusa com fotografias e clica em "Recusar Devolução".
  3. O sistema altera o status para `Recusado` e emite comunicado formal com justificativa ao cliente.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN009, RN010, RN011, RN012:** Garantia do cumprimento das regras de devolução e exigências fiscais.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Parecer de triagem, laudo de vistoria, escolha de estorno ou vale-compras.

### Saídas:
- Protocolo de logística reversa, NF-e de entrada e comprovante de reembolso.
