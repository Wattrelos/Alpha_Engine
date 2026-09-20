# UC_CORE_009 - Gestão de Devoluções no Painel

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_009` |
| **Nome** | Gestão de Devoluções, Vistoria e Homologação de RMA no Painel |
| **Módulo** | Núcleo Central (Core) - Backoffice & Pós-Venda (SAC) |
| **Atores Primários** | Operador do Painel (SAC / Atendimento), Analista de Vistoria do CD |
| **Atores Secundários** | Gateways de Pagamento (Estorno Automático), Sistema Alpha Engine |
| **Tipo** | Condução / Governança Operacional & Fiscal |
| **Frequência de Uso** | Diária / Média |
| **Rastreabilidade** | **RF:** [RF020](/docs/requirements/functional/functional_requirements.yaml) (Emissão de NF-e de entrada), [RF022](/docs/requirements/functional/functional_requirements.yaml) (Logística reversa last-mile), [RF025](/docs/requirements/functional/functional_requirements.yaml) (Relatórios de pós-venda)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Reintegração ao estoque), [RN009](/docs/requirements/business_rules/business_rules.yaml) (Política de devolução), [RN010](/docs/requirements/business_rules/business_rules.yaml) (Vistoria de acabamentos e tintas), [RN011](/docs/requirements/business_rules/business_rules.yaml) (Regras CDC), [RN012](/docs/requirements/business_rules/business_rules.yaml) (NF-e de devolução)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Auditoria de estorno financeiro), [RNF006](/docs/requirements/non_functional/non_functional_requirements.yaml) (Sincronização com ERP) |

---

## 1. 🎯 Descrição Sumária
Permite aos operadores de atendimento (SAC) e analistas do Centro de Distribuição (CD) gerenciar a esteira de triagem, inspeção física e liquidação dos processos de devolução e troca (RMA) abertos por clientes (`UC_CORE_008`). O fluxo engloba a emissão do código de postagem reversa, o laudo pericial das mercadorias recebidas (verificação de quebras, lacres e avarias), a emissão da Nota Fiscal de Entrada de Devolução (CFOP 1.202/2.202), a reintegração automática ao estoque e o acionamento do estorno financeiro no gateway ou crédito em conta.

---

## 2. ⚡ Pré-Condições
1. Chamado de RMA registrado na base de dados com status `Pendente de Análise` ou `Mercadoria em Trânsito Reverso`.
2. Operador autenticado com permissão no módulo `sale/return`.

---

## 3. ✅ Pós-Condições
- Protocolo de RMA com status finalizado (`Aprovado / Estornado` ou `Recusado`).
- Nota Fiscal de Entrada emitida e autorizada pela SEFAZ.
- Saldo do produto recomposto no estoque físico (se o laudo for aprovado).
- Estorno financeiro registrado no gateway de pagamento.

---

## 4. 🚀 Gatilho (Trigger)
O operador do SAC acessa o menu "Vendas > Devoluções / RMA" para realizar a triagem ou o CD confirma o recebimento físico da mercadoria no armazém.

---

## 5. 🔄 Fluxo Principal (Vistoria Aprovada e Estorno Financeiro)

1. **Ator (SAC):** Acessa a fila de triagem e abre o protocolo `#RMA-2026-0045`.
2. **Sistema:** Apresenta a documentação anexada pelo cliente: fotos das caixas de pisos fechadas, número da NF-e original, motivo declarado e comprovante de data de entrega.
3. **Ator (SAC):** Valida a elegibilidade da solicitação e clica em "Autorizar Envio Reverso".
4. **Sistema:** Gera a etiqueta de postagem dos Correios ou aciona a coleta rodoviária com a transportadora, despachando o código rastreador para o e-mail e SMS do cliente.
5. **Sistema:** O pacote é entregue na doca do Centro de Distribuição e o status migra para `Recebido no CD - Aguardando Vistoria`.
6. **Ator (Analista CD):** Inspeciona fisicamente o volume:
   - Confere se as 4 caixas correspondem exatamente ao lote e tonalidade da nota;
   - Verifica se as embalagens estão íntegras e sem peças trincadas.
7. **Ator (CD):** Registra o laudo: *"Vistoria 100% aprovada. Mercadorias lacradas e sem avarias técnicas."*.
8. **Ator (SAC):** Clica em "Homologar Devolução & Liquidar".
9. **Sistema:** Dispara as rotinas automáticas de encerramento em transação segura:
   - Transmite o XML da **Nota Fiscal de Entrada de Devolução** para a SEFAZ (RN012);
   - Executa chamada de estorno na API do Gateway (reembolsando o valor integral de R$ 575,20 na chave PIX ou fatura do cartão);
   - Reintegra as 4 caixas ao saldo disponível do inventário (`quantity = quantity + 4` - RN005);
   - Dispara e-mail de conclusão com a carta de estorno para o cliente.
10. O caso de uso encerra com o protocolo arquivado com sucesso.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Liquidação via Vale-Compras (Crédito na Loja):**
  1. No passo 8, a preferência do cliente registrada no protocolo era por crédito em loja.
  2. O operador seleciona "Emitir Vale-Compras".
  3. O sistema gera um cupom digital único vinculado ao CPF/CNPJ do cliente, com validade de 180 dias, e adiciona o saldo no painel "Minha Conta".

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Mercadoria Reprovada na Vistoria Técnica (Avaria por Mau Uso / Embalagem Violada):**
  1. No passo 6, o analista do CD identifica que latas de tinta personalizada foram abertas ou que caixas de pisos foram danificadas por umidade após a entrega (RN010, RN011).
  2. O analista tira fotos da avaria e registra o laudo de reprovação: *"Recusado: Produto com sinais de uso e violação de embalagem original."*.
  3. O operador do SAC clica em "Recusar Devolução".
  4. O sistema bloqueia qualquer estorno financeiro, emite laudo fotográfico em PDF e despacha o produto de volta para o endereço do cliente acompanhado da justificativa formal.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Controle de Estoque):** Apenas itens com laudo técnico de vistoria aprovado são devolvidos ao inventário ativo.
- **RN010 (Clareza em Trocas de Acabamentos e Cores):** Tintas preparadas no sistema tintométrico sob encomenda e itens cortados sob medida não possuem direito de devolução por arrependimento.
- **RN011 (Critérios de Aceite CDC):** Exigência de embalagem original e manuais sem avaria.
- **RN012 (Documentação Obrigatória):** Emissão compulsória de NF-e de entrada para legalizar o retorno físico da mercadoria no almoxarifado.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `return_id`, `inspection_status` (`approved`, `rejected`), `inspection_notes`.
- Fotos da vistoria física no armazém (`inspection_photos[]`).
- `action_settlement` (`gateway_refund`, `store_credit`, `return_to_customer`).

### Saídas:
- Chave de acesso da NF-e de entrada emitida, comprovante de estorno do gateway, notificação formal ao cliente.
