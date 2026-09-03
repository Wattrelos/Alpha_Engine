# UC_CLI_023 - Solicitar Devolução / RMA

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_023` |
| **Nome** | Solicitar Devolução / RMA |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Pós-Venda (SAC) |
| **Frequência de Uso** | Baixa |
| **Rastreabilidade** | **RF:** [RF016](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Histórico), [RF020](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (NF-e de devolução)<br>**RN:** [RN009](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Política padronizada), [RN010](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Materiais sensíveis), [RN011](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Direito de arrependimento 7 dias CDC e exceção BOPIS), [RN012](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Documentação fiscal)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Formulário claro com upload de fotos) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente autenticado abrir um chamado formal de devolução ou troca de mercadorias (RMA - *Return Merchandise Authorization*) através da interface do portal (`/account/return`), selecionando os itens do pedido entregue, informando a motivação (arrependimento legal pelo CDC, avaria de transporte ou vício de fabricação), anexando fotos comprobatórias da embalagem lacrada e acompanhando o status da triagem.

---

## 2. ⚡ Pré-Condições
1. O pedido deve estar com status `Entregue`.
2. A solicitação deve cumprir os critérios de elegibilidade legal do Código de Defesa do Consumidor (Art. 49).

---

## 3. ✅ Pós-Condições
- Chamado de RMA registrado na tabela `tbkk_product_return` com número de protocolo gerado e status inicial `Pendente de Análise`.
- Notificação automática despachada para a equipe de SAC e e-mail de confirmação enviado ao cliente.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Solicitar Devolução / Troca" na página de detalhes do pedido (`/account/orders/info`) ou acessa `/account/return/add`.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz - Devolução por Arrependimento)

1. **Ator:** Acessa os detalhes do pedido `#10542` (entregue há 3 dias em domicílio) e clica em "Solicitar Devolução".
2. **Sistema:** Valida que a data de entrega está dentro do prazo legal de **7 dias corridos** do Art. 49 do CDC e que a compra não foi retirada presencialmente na loja (RN011).
3. **Sistema:** Exibe o formulário de RMA com os dados do pedido e a listagem dos produtos entregues.
4. **Ator:** Marca as caixas de seleção dos itens que deseja devolver (ex: 2 caixas de porcelanato sobressalentes), informa a quantidade, seleciona o motivo *"Direito de Arrependimento (Art. 49 CDC)"* e declara se a embalagem original está lacrada e intacta.
5. **Ator:** Anexa fotografias dos produtos e das caixas lacradas.
6. **Ator:** Escolhe a modalidade de ressarcimento preferida: *Estorno Financeiro* (mesmo meio de pagamento) ou *Crédito / Vale-Compras na Loja*.
7. **Ator:** Clica em "Enviar Solicitação de Devolução".
8. **Sistema:** Grava a entidade no banco de dados, gera o número de protocolo (ex: `RMA-2026-0045`), envia notificação de abertura para o cliente e encaminha para a fila de triagem da equipe administrativa (`UC_ADM_004`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Devolução por Avaria de Transporte ou Defeito:**
  1. O cliente seleciona o motivo *"Produto Avariado no Transporte"* ou *"Vício de Fabricação"*.
  2. O sistema solicita fotos detalhadas do dano e da etiqueta de garantia (RN012), estendendo a análise para a garantia legal de 90 dias para bens duráveis.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Prazo Legal de 7 Dias Expirado:**
  1. O cliente tenta abrir devolução por arrependimento após 15 dias da entrega.
  2. O sistema bloqueia a opção de arrependimento e orienta: *"O prazo legal de 7 dias para arrependimento expirou. Para acionar a garantia por defeito de fabricação, selecione a opção correspondente."*
- **FE02 - Compra Retirada na Loja Física (Exceção BOPIS):**
  1. O pedido possui modalidade de entrega presencial (*Retirada na Loja - BOPIS*).
  2. O sistema valida a regra RN011 e alerta: *"Conforme o Código de Defesa do Consumidor, compras com retirada presencial na loja física não se enquadram no direito de arrependimento de 7 dias, pois o produto foi inspecionado no balcão."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN009 & RN010:** Critérios rigorosos de devolução para tintas preparadas sob medida e materiais sensíveis.
- **RN011:** Cumprimento rigoroso do Art. 49 do CDC com validação da exceção de retirada presencial BOPIS.
- **RN012:** Exigência de anexação da Nota Fiscal e selo de garantia.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `order_id`, `product_id`, `quantity`, `return_reason_id`, `opened` (embalagem aberta sim/não), `comment`, `photos` (upload).

### Saídas:
- Protocolo de RMA (`return_id`), comprovante em PDF e tela de acompanhamento.
