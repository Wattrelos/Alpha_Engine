# UC_PRV_002 - Submeter Proposta Comercial de Mão de Obra

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_PRV_002` |
| **Nome** | Submeter Proposta Comercial de Mão de Obra (*Bid Submission*) |
| **Módulo** | Portal do Prestador - Cotações & Propostas Comerciais |
| **Atores Primários** | Prestador de Serviços Autenticado (*Service Provider*) |
| **Atores Secundários** | Cliente Solicitante (*Customer*), Sistema Alpha Engine |
| **Tipo** | Condução / Negociação & Submissão de Propostas |
| **Frequência de Uso** | Média a Alta |
| **Rastreabilidade** | **RF:** [RF035](/docs/requirements/functional/products_quotation.yaml) (Submissão e comparação de propostas - Bid Comparison)<br>**RN:** Máximo de 10 propostas por projeto, Validade da proposta (15 dias)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Auditoria e integridade de propostas contratuais) |

---

## 1. 🎯 Descrição Sumária
Permite ao prestador examinar minuciosamente o escopo descritivo e as especificações técnicas de uma obra selecionada e enviar sua proposta comercial de prestação de serviços na rota `/prestador/projetos/{rfq_id}/proposta`, informando o valor da mão de obra, prazo estimado em dias, metodologia de execução e notas técnicas explicativas.

---

## 2. ⚡ Pré-Condições
1. O prestador deve estar qualificado dentro do raio geográfico do projeto (`UC_PRV_001`).
2. O projeto RFQ deve estar com status `'open'`.
3. O projeto não pode ter ultrapassado a cota de 10 propostas concorrentes.
4. O prestador não pode ter submetido proposta anterior ainda ativa para a mesma RFQ.

---

## 3. ✅ Pós-Condições
- Registro da proposta gravado na tabela `agsc_project_bid` com status `'submitted'`.
- Notificação instantânea despachada ao cliente informando o recebimento de uma nova proposta.
- Disponibilização dos dados da proposta no painel analítico de comparação do cliente (`UC_CLI_027`).

---

## 4. 🚀 Gatilho (Trigger)
O prestador clica em "Submeter Proposta / Orçar Obra" no card do projeto do feed de oportunidades.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Clica em "Submeter Proposta" para a RFQ selecionada.
2. **Sistema:** Carrega o formulário de proposta em `/prestador/projetos/{rfq_id}/proposta`, apresentando o resumo do projeto:
   - Descrição detalhada do serviço fornecida pelo cliente;
   - Fotos/plantas anexas enviadas na publicação;
   - Prazo pretendido de entrega informado pelo cliente.
3. **Ator:** Preenche os campos da proposta comercial:
   - **Valor da Mão de Obra (R$)** (`labor_price`);
   - **Prazo Estimado de Execução (dias)** (`estimated_duration_days`);
   - **Memorial Descritivo / Observações da Proposta** (`proposal_notes`): detalhes do método executivo, etapas, equipamentos inclusos e condições.
4. **Ator:** Clica em "Enviar Proposta Comercial".
5. **Sistema:** Valida as regras de entrada (valor monetário positivo, prazo maior que zero, texto do memorial com requisitos mínimos).
6. **Sistema:** Persiste a proposta na tabela `agsc_project_bid` com:
   - `rfq_id` = ID do projeto;
   - `provider_id` = ID do perfil do prestador autenticado;
   - `status` = `'submitted'`;
   - Timestamp de envio (`date_added`).
7. **Sistema:** Dispara evento `ProjectBidSubmittedEvent`, notificando o cliente solicitante por e-mail e push notification.
8. **Sistema:** Apresenta mensagem de sucesso: *"Sua proposta de R$ [Valor] foi enviada com sucesso! O cliente foi notificado e você será avisado assim que sua oferta for analisada."*

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Edição de Proposta Aberta:**
  1. Antes do cliente selecionar um profissional, o prestador decide revisar o valor ou o prazo de sua proposta enviada.
  2. O prestador acessa a proposta, altera as condições e salva a atualização.
  3. O sistema atualiza o registro na tabela `agsc_project_bid` e notifica o cliente da revisão.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Limite de Propostas Atingido Concorrentemente:**
  1. Entre o carregamento da tela e o clique no botão enviar, outro prestador enviou a 10ª proposta do projeto.
  2. O sistema bloqueia a gravação e alerta: *"Esta solicitação de orçamento já atingiu o limite máximo de 10 propostas concorrentes e não está mais recebendo novas ofertas."*
- **FE02 - Projeto Cancelado ou Já Homologado pelo Cliente:**
  1. O cliente encerrou ou contratou outro profissional antes do envio da proposta.
  2. O sistema bloqueia a transação e informa: *"Este projeto já foi homologado ou cancelado pelo cliente."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN-BID-01 (Limite de Concorrência):** Cada RFQ aceita até 10 propostas para evitar leilão predatório e poluição de análise para o consumidor.
- **RN-BID-02 (Validade de Oferta):** A proposta emitida possui validade padrão de 15 dias corridos para aceite formal.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `labor_price` (decimal), `estimated_duration_days` (inteiro), `proposal_notes` (texto).

### Saídas:
- `bid_id` gerado, confirmação de envio e atualização do status para `submitted`.
