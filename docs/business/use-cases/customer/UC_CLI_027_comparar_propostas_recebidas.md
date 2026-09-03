# UC_CLI_027 - Comparar Propostas Recebidas

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_027` |
| **Nome** | Comparar Propostas Recebidas |
| **Módulo** | Loja Virtual - Cotações & Projetos (RFQ / BoQ) |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Extensão de `UC_CLI_026` (`<<extend>>`) |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF004](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Unidades/Itens), [RF010](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Frete)<br>**RN:** [RN015](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Descontos por volume)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Quadro comparativo lado a lado) |

---

## 1. 🎯 Descrição Sumária
Estende o gerenciamento de projetos (`UC_CLI_026`) permitindo ao cliente comparar detalhadamente as propostas comerciais e técnicas enviadas por diferentes fornecedores/vendedores para a sua solicitação de orçamento (`/propostas`), visualizando uma matriz comparativa lado a lado com preços unitários, prazo de entrega global, custo de frete com descarregamento e condições de pagamento oferecidas.

---

## 2. ⚡ Pré-Condições
- Projeto RFQ possuindo ao menos uma proposta comercial cadastrada no sistema.

---

## 3. ✅ Pós-Condições
- Matriz comparativa de propostas exibida em tela com destaque para o menor preço e melhor prazo.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Ver / Comparar Propostas" no card de um projeto com lances.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Clica em "Comparar Propostas" no projeto `RFQ-2026-089`.
2. **Sistema:** Carrega todas as propostas comerciais ativas vinculadas ao projeto na tabela `tbkk_rfq_bid`.
3. **Sistema:** Renderiza a matriz comparativa em colunas lado a lado:
   - Fornecedor / Parceiro Comercial;
   - Valor Total dos Materiais (R$);
   - Valor do Frete / Descarregamento no Canteiro de Obras (R$);
   - Prazo Estimado de Entrega (dias úteis);
   - Condições de Pagamento (ex: *"Boleto Faturado 30/60 dias"* ou *"5% desconto no PIX"*);
   - Discriminação item a item da lista de materiais cotada.
4. **Ator:** Analisa os diferenciais de preço, prazo e reputação de cada proposta.
5. **Ator:** Seleciona a proposta mais vantajosa e clica no botão "Aceitar Proposta" (`<<extend>> UC_CLI_028`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Solicitação de Revisão / Contraproposta:**
  1. O cliente clica em "Negociar Proposta", insere uma mensagem com ajuste de quantidade ou solicitação de desconto extra e submete de volta ao vendedor.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Proposta Expirada:**
  1. A data de validade da proposta comercial foi ultrapassada antes do aceite.
  2. O sistema sinaliza a coluna como *"Proposta Expirada"* e desabilita o botão de aceite direto, sugerindo revalidação.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN015 & RN017:** Transparência nos preços por atacado praticados nas propostas.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `project_id`.

### Saídas:
- Tabela comparativa dinâmica (Grid) com filtros por menor preço, prazo mais curto e melhor avaliação.
