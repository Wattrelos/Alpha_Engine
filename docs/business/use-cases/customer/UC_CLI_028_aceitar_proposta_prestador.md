# UC_CLI_028 - Aceitar Proposta de Prestador

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_028` |
| **Nome** | Aceitar Proposta de Prestador |
| **Módulo** | Loja Virtual - Cotações & Projetos (RFQ / BoQ) |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Fornecedor / Parceiro Vencedor, Sistema Alpha Engine |
| **Tipo** | Extensão de `UC_CLI_027` (`<<extend>>`) |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF018](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Checkout), [RF020](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Faturamento)<br>**RN:** [RN005](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Estoque), [RN015](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Preços negociados)<br>**RNF:** [RNF003](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Auditoria contratual) |

---

## 1. 🎯 Descrição Sumária
Estende a comparação de propostas (`UC_CLI_027`) permitindo ao cliente homologar e aceitar formalmente a proposta comercial escolhida (`/aceitar`), encerrando a concorrência entre fornecedores para aquela cotação, gerando o contrato de fornecimento preliminar e liberando a lista de materiais aprovada para compra direta ou faturamento corporativo.

---

## 2. ⚡ Pré-Condições
- Proposta comercial com status ativa e dentro do prazo de validade.

---

## 3. ✅ Pós-Condições
- Status da proposta alterado para `Aceita / Vencedora`.
- Status das demais propostas concorrentes alterado para `Não Selecionada`.
- Notificação de encerramento enviada ao fornecedor vencedor.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Aceitar Esta Proposta" na visualização detalhada ou comparador.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Clica em "Aceitar Esta Proposta" no card do fornecedor selecionado.
2. **Sistema:** Abre janela modal de confirmação exibindo o resumo financeiro dos materiais cotados, valor do frete e condições aceitas.
3. **Ator:** Confirma o aceite clicando em "Confirmar Contratação".
4. **Sistema:** Abre transação no banco de dados:
   - Altera o status da proposta na tabela `tbkk_rfq_bid` para `Aceita`;
   - Atualiza o projeto RFQ para o status `Homologado / Aguardando Checkout`;
   - Envia e-mail de congratulações e notificação interna para o vendedor responsável.
5. **Sistema:** Oferece imediatamente as opções:
   - `[ Enviar Materiais para o Carrinho de Compras ]` (`<<extend>> UC_CLI_029`);
   - `[ Faturar a Prazo no CNPJ / Boleto Corporativo ]`.

---

## 6. 🔀 Fluxos Alternativos

- N/A.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Proposta Cancelada pelo Fornecedor durante a Análise:**
  1. O fornecedor revogou a oferta por falta de insumos de fábrica antes da confirmação do cliente.
  2. O sistema bloqueia a operação e alerta: *"Esta proposta foi revogada pelo fornecedor. Por favor, avalie as outras propostas disponíveis."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN017:** Garantia da manutenção dos preços acordados na proposta até o faturamento final.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `bid_id`, `confirmation` (boolean).

### Saídas:
- Termo de homologação do projeto e liberação para geração do pedido de compra.
