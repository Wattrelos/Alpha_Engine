# UC_CORE_008 - Solicitar Devolução (RMA)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_008` |
| **Nome** | Solicitar Devolução / Troca (RMA) pelo Cliente |
| **Módulo** | Núcleo Central (Core) - Pós-Venda & Portal do Cliente |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine, Serviço Postal / Transportadora |
| **Tipo** | Condução / Logística Reversa & Atendimento ao Consumidor |
| **Frequência de Uso** | Baixa / Moderada |
| **Rastreabilidade** | **RF:** [RF016](/docs/requirements/functional/functional_requirements.yaml) (Histórico de pedidos), [RF022](/docs/requirements/functional/functional_requirements.yaml) (Rastreamento logístico reverso)<br>**RN:** [RN009](/docs/requirements/business_rules/business_rules.yaml) (Política de devolução), [RN010](/docs/requirements/business_rules/business_rules.yaml) (Clareza na troca de acabamentos), [RN011](/docs/requirements/business_rules/business_rules.yaml) (Direito de arrependimento 7 dias CDC), [RN012](/docs/requirements/business_rules/business_rules.yaml) (Documentação fiscal)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Fluxo de autoatendimento guiado), [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Proteção de dados e fotos) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente autenticado abrir solicitações formais de devolução total/parcial de mercadorias ou troca de produtos (Return Merchandise Authorization - RMA) diretamente em sua conta no portal. O sistema valida automaticamente o cumprimento dos prazos legais do Código de Defesa do Consumidor (7 dias para compras entregues à distância - CDC Art. 49), exige a especificação do motivo e o upload de fotos das mercadorias/embalagens originais (essencial para tintas manipuladas, pisos e louças) e gera a etiqueta preliminar de logística reversa.

---

## 2. ⚡ Pré-Condições
1. O cliente deve estar autenticado em sua conta.
2. O pedido original deve constar com status `Entregue` e ter sido faturado há menos de 7 dias corridos (ou apresentar vício de fabricação dentro do prazo de garantia).

---

## 3. ✅ Pós-Condições
- Protocolo de RMA cadastrado com status inicial `Pendente de Análise Técnica` (`tbkk_product_return`).
- E-mail de confirmação enviado ao cliente contendo o número do protocolo e instruções para acondicionamento da carga.

---

## 4. 🚀 Gatilho (Trigger)
O cliente acessa "Minha Conta > Meus Pedidos", visualiza um pedido entregue e clica no botão "Solicitar Devolução / Troca".

---

## 5. 🔄 Fluxo Principal (Caminho Feliz - Devolução de Caixas de Piso por Arrependimento)

1. **Ator:** Acessa o histórico de pedidos e clica em "Devolução" ao lado do pedido `#10842`.
2. **Sistema:** Verifica a data de entrega informada pela transportadora: realizada há 3 dias (em conformidade com o prazo legal de 7 dias da RN011).
3. **Sistema:** Apresenta a tela de abertura de RMA com a lista de itens do pedido:
   - Seleção dos produtos a devolver e quantidade exata (ex: 4 caixas de porcelanato);
   - Menu suspenso de motivos: *Arrependimento / Sobra de obra*, *Avaria no transporte*, *Defeito de fabricação*, *Produto divergente*;
   - Campo para descrição detalhada do motivo;
   - Seletor da modalidade de compensação desejada: *Estorno financeiro na mesma forma de pagamento* ou *Vale-Compras na loja*.
4. **Ator:** Seleciona "Arrependimento da compra", marca 4 caixas, opta por "Estorno financeiro" e digita detalhes.
5. **Sistema:** Apresenta o aviso das exigências de embalagem intacta para materiais de acabamento (RN010, RN011): caixas lacradas, sem argamassa e sem quebras.
6. **Ator:** Faz upload de 3 fotos das caixas íntegras e da Nota Fiscal de compra anexada (RN012).
7. **Ator:** Clica em "Enviar Solicitação de Devolução".
8. **Sistema:** Registra o chamado no banco de dados (`return_id = 45`), armazena as fotos no diretório seguro de pós-venda e despacha notificação com protocolo para a equipe de SAC/Triagem (`UC_CORE_009`).
9. **Sistema:** Exibe tela com o protocolo `#RMA-2026-0045` e informa que a triagem ocorrerá em até 24 horas úteis.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Abertura de RMA por Defeito Técnico de Fabricação (Garantia Legal 90 Dias):**
  1. A compra ocorreu há mais de 7 dias, mas dentro dos 90 dias previstos pelo CDC para bens duráveis (ferramenta elétrica com motor inoperante).
  2. O cliente seleciona o motivo "Defeito de fabricação" e anexa vídeo demonstrativo.
  3. O sistema encaminha o chamado diretamente para o fluxo de garantia técnica do fabricante.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Pedido Fora do Prazo Legal de Arrependimento (CDC > 7 Dias):**
  1. No passo 2, a entrega ocorreu há 12 dias corridos.
  2. O cliente seleciona a opção "Arrependimento de compra".
  3. O sistema impede a continuidade com a mensagem: *"O prazo legal para desistência/arrependimento de compra (7 dias corridos conforme Art. 49 do CDC) expirou para este pedido. Em caso de defeito ou garantia técnica, selecione 'Defeito de Fabricação'."* (RN011).
- **FE02 - Exceção de Compra com Retirada em Loja (BOPIS):**
  1. No passo 2, o pedido original foi retirado presencialmente no balcão da loja física (BOPIS).
  2. O sistema informa a regra expressa da RN011: *"Mercadorias inspecionadas e retiradas presencialmente em loja física não possuem o direito de arrependimento do e-commerce. Para trocas presenciais, dirija-se ao balcão de atendimento da loja com o cupom fiscal."*.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN009 e RN010 (Política de Devolução e Clareza em Acabamentos):** Normas estritas sobre conservação de tintas preparadas e cerâmicas.
- **RN011 (Direito de Arrependimento CDC):** Validação matemática estrita do prazo de 7 dias entre a data do comprovante de entrega e a data da solicitação.
- **RN012 (Documentação Obrigatória):** Exigência de anexo ou vínculo com a NF-e/NFC-e de faturamento original.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `order_id`, `product_id`, `quantity_return`, `return_reason_id`.
- `compensation_type` (`refund` ou `store_credit`), `comment`.
- Upload de imagens e notas (`evidence_files[]`).

### Saídas:
- Número do protocolo de devolução (`return_id`), comprovante em PDF e status de acompanhamento no painel do cliente.
