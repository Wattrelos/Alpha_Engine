# UC_CORE_006 - Pré-Venda no PDV Balcão

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_006` |
| **Nome** | Pré-Venda no PDV Balcão & Reserva de Lote |
| **Módulo** | Núcleo Central (Core) - Ponto de Venda (POS) & Atendimento Balcão |
| **Atores Primários** | Vendedor de Balcão (*Sales Representative*), Cliente Presencial (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine POS, Impressora Térmica Não Fiscal |
| **Tipo** | Condução / Operação Presencial de Balcão |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF004](/docs/requirements/functional/functional_requirements.yaml) (Múltiplas unidades e m²), [RF006](/docs/requirements/functional/functional_requirements.yaml) (Gestão automática de inventário), [RF021](/docs/requirements/functional/functional_requirements.yaml) (Modalidades de entrega/retirada)<br>**RN:** [RN001](/docs/requirements/business_rules/business_rules.yaml) (Pisos em caixas fechadas), [RN005](/docs/requirements/business_rules/business_rules.yaml) (Controle rigoroso de estoque), [RN008](/docs/requirements/business_rules/business_rules.yaml) (Retirada balcão), [RN015](/docs/requirements/business_rules/business_rules.yaml) (Descontos por volume progressivo)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Operação via atalhos de teclado F1-F12), [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Tempo de resposta < 100ms) |

---

## 1. 🎯 Descrição Sumária
Permite aos vendedores de balcão da loja física realizar o atendimento consultivo e técnico a clientes e empreiteiros, utilizando ferramentas de conversão de materiais em tempo real (como a calculadora de caixas de pisos e sacos de argamassa), aplicando tabelas progressivas de desconto por volume e criando comandas de pré-venda com reserva temporária de estoque. Ao concluir o atendimento, o sistema gera e imprime um ticket com código de barras, direcionando o cliente para liquidação nos caixas.

---

## 2. ⚡ Pré-Condições
1. O vendedor deve estar logado no terminal de balcão do PDV com seu código de operador/comissão.
2. A impressora térmica de tickets de balcão deve estar comunicável.

---

## 3. ✅ Pós-Condições
- Pré-venda registrada na base com status `Aguardando Pagamento no Caixa`.
- Saldo de estoque físico reservado temporariamente por 60 minutos (evitando venda duplicada).
- Ticket impresso emitido com código de barras legível por scanner óptico.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor pressiona a tecla de atalho `[F1] Nova Pré-Venda` no balcão da loja.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Pressiona `[F1]` no terminal de balcão para iniciar o atendimento.
2. **Sistema:** Instancia a comanda vazia em tela, registrando o ID do vendedor para apuração de comissão.
3. **Ator:** Solicita o CPF/CNPJ do cliente para identificação ou pesquisa por nome/telefone.
4. **Sistema:** Localiza o cadastro do cliente e exibe seu perfil: cliente PJ (Empreiteira) vinculado à tabela com condições especiais de faturamento (RN017).
5. **Ator:** Utiliza a busca rápida pelo leitor de código de barras ou digita o código do produto (ex: Cimento CP-II 50kg).
6. **Ator:** Insere a quantidade de 80 sacos.
7. **Sistema:** Identifica a aplicação automática da regra de desconto por volume progressivo para cimento (`quantity >= 50` aplica 7% de abatimento unitário - RN015).
8. **Ator:** Adiciona 30 caixas de argamassa AC-III e seleciona a modalidade de entrega: "Carga pesada a ser entregue no canteiro de obras via caminhão da loja".
9. **Sistema:** Exibe o valor do frete calculado com base no peso total acumulado (4.600 kg) e na distância do CEP da obra.
10. **Ator:** Pressiona `[F10] Finalizar Pré-Venda`.
11. **Sistema:** Cria o registro de pré-venda (`tbkk_pos_order`), define o status como `Pendente`, cria o bloqueio de reserva de estoque no Redis com TTL de 60 minutos e comanda a impressão térmica do ticket de balcão com código Code-128.
12. **Ator:** Destaca o ticket da impressora, entrega-o ao cliente e diz: *"Sr. Marcos, seu pedido está pronto! Por favor, dirija-se a um dos caixas com este ticket para o pagamento e liberação da entrega."*.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Venda de Pisos com Conversão em Metragem:**
  1. No passo 5, o cliente informa que a sala possui 65 m² de área útil.
  2. O vendedor digita `65` no campo m² da interface de balcão.
  3. O sistema calcula automaticamente 34 caixas fechadas (`65.28 m²`) com a margem técnica de quebra de 10% já incorporada (RN001).
- **FA02 - Consulta Rápida de Estoque em Filiais:**
  1. O produto possui saldo zerado no depósito da loja atual.
  2. O vendedor pressiona `[F4] Consulta Multi-Loja`.
  3. O sistema exibe em tela o saldo em tempo real no Centro de Distribuição Central e nas demais filiais da rede.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Saldo Físico Insuficiente para Atendimento:**
  1. No passo 6, o vendedor digita uma quantidade superior ao estoque livre disponível (considerando outras pré-vendas ativas).
  2. O sistema emite alerta sonoro no terminal e bloqueia a adição: *"Estoque insuficiente. Saldo livre: 14 unidades. 20 unidades encontram-se reservadas em outras pré-vendas no caixa."*.
- **FE02 - Falha de Comunicação com a Impressora de Tickets:**
  1. No passo 11, a impressora térmica está sem bobina de papel ou desconectada.
  2. O sistema exibe o número da pré-venda em fonte gigante na tela e envia cópia em PDF com código de barras diretamente para o WhatsApp do cliente.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Variações de Unidades):** Conversão compulsória de m² em caixas completas no balcão.
- **RN005 (Controle Rigoroso de Estoque):** Reserva temporária com expiração automática (*time-to-live*) para evitar que mercadorias fiquem retidas indefinidamente.
- **RN015 (Descontos por Volume):** Regra progressiva para produtos pesados de construção básica (cimento, areia, blocos).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Atalhos de teclado (`F1` a `F12`), `customer_document` (CPF/CNPJ).
- `barcode` / `sku`, `quantity`, `area_m2`.
- Modalidade de entrega (`retirada_balcao` ou `entrega_caminhao`).

### Saídas:
- Número da comanda (`ticket_id`), ticket impresso na impressora térmica não fiscal com código de barras, espelho da pré-venda em tela.
