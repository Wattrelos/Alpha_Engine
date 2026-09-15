# UC_POS_005 - Criar Pré-Venda (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_005` |
| **Nome** | Criar Pré-Venda no Balcão |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Vendedor de Balcão (*Sales Representative*) |
| **Atores Secundários** | Sistema Alpha Engine POS |
| **Tipo** | Condução / Venda Presencial |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF006](/docs/requirements/functional/functional_requirements.yaml) (Gestão de inventário), [RF009](/docs/requirements/functional/functional_requirements.yaml) (Carrinho/Comanda), [RF021](/docs/requirements/functional/functional_requirements.yaml) (Modalidades de entrega/retirada)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Estoque), [RN008](/docs/requirements/business_rules/business_rules.yaml) (Retirada balcão vs entrega), [RN015](/docs/requirements/business_rules/business_rules.yaml) (Descontos por volume)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Atalhos rápidos de teclado) |

---

## 1. 🎯 Descrição Sumária
Coordena o fluxo de atendimento comercial presencial no balcão da loja, permitindo ao vendedor iniciar uma comanda de pré-venda, identificar o cliente (`UC_POS_004`), adicionar mercadorias (`<<include>> UC_POS_006`), definir a modalidade de entrega (Retirada imediata de balcão ou Entrega agendada de carga pesada na obra) e invocar o salvamento e emissão do ticket impresso (`<<include>> UC_POS_007`).

---

## 2. ⚡ Pré-Condições
- Vendedor autenticado no módulo de vendas do PDV.

---

## 3. ✅ Pós-Condições
- Pré-venda registrada com status `Pendente` e ticket impresso entregue ao cliente para quitação no caixa.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor pressiona `[F1] Nova Pré-Venda` ou atende um cliente no balcão.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Pressiona `[F1]` para abrir uma nova comanda de pré-venda.
2. **Sistema:** Instancia a comanda vazia em memória no POS e gera um número sequencial temporário.
3. **Ator:** Identifica o cliente presencialmente (`UC_POS_004`).
4. **Ator:** Adiciona os produtos solicitados bipando os itens ou digitando quantidades (`<<include>> UC_POS_006`).
5. **Ator:** Seleciona a modalidade de atendimento:
   - *Retirada Imediata no Balcão:* O vendedor separa as peças da prateleira e acondiciona na sacola de balcão;
   - *Entrega em Domicílio / Obra:* O vendedor insere o CEP da obra, o sistema calcula a taxa de frete do caminhão e adiciona à comanda.
6. **Ator:** Pressiona a tecla `[F10] Salvar e Gerar Ticket`.
7. **Sistema:** Invoca `<<include>> UC_POS_007 (Salvar Pré-Venda Pendente)`.
8. **Ator:** Entrega o ticket impresso ao cliente e orienta-o a se dirigir aos terminais de caixa.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cancelamento / Descarte de Pré-Venda em Digitação:**
  1. O cliente desiste antes de salvar o ticket.
  2. O vendedor pressiona `[ESC] Cancelar Atendimento`, o sistema limpa a tela e descarta os itens sem afetar o banco.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Tentativa de Salvar Comanda sem Nenhum Item:**
  1. O vendedor tenta salvar uma pré-venda vazia.
  2. O sistema bloqueia com sinal sonoro e exibe: *"Adicione ao menos 1 produto para gerar a pré-venda."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 & RN008:** Controle rigoroso de estoque e distinção operacional entre retirada imediata e entrega com caminhão.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Itens, quantidades, identificação do cliente e opção de entrega (Retirada ou Frete).

### Saídas:
- Total da pré-venda consolidado e ticket impresso na impressora térmica do balcão.
