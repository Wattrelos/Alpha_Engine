# UC_CLI_026 - Acompanhar Meus Projetos

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_026` |
| **Nome** | Acompanhar Meus Projetos |
| **Módulo** | Loja Virtual - Cotações & Projetos (RFQ / BoQ) |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Gestão de Cotações |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF014](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Perfil), [RF016](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Histórico)<br>**RN:** [RN015](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Preços progressivos)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Visão consolidada) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente visualizar a relação de todos os seus projetos e cotações de obras abertas (`/account/projetos`), monitorando o status atual de cada solicitação (Em Aberto, Propostas Recebidas, Em Negociação, Aprovado, Concluído), visualizando a quantidade de propostas comerciais enviadas por fornecedores/vendedores e acessando o comparador de preços (`<<extend>> UC_CLI_027`).

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- Listagem dos projetos do cliente renderizada com indicadores visuais de progresso e contadores de lances/propostas.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Meus Projetos & Cotações" no painel da conta.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa `/account/projetos`.
2. **Sistema:** Consulta os registros na tabela `tbkk_rfq_project` vinculados ao `customer_id`.
3. **Sistema:** Renderiza os cartões de projetos contendo:
   - Código e Título da Obra (ex: `RFQ-2026-089: Reforma Residencial 180m²`);
   - Data de Criação e Prazo Limite;
   - Status atual (badge colorido);
   - Contador de propostas recebidas (ex: *"3 propostas comerciais disponíveis"*);
   - Botões de Ação: `[ Ver Propostas ]`, `[ Editar Itens ]`, `[ Cancelar Cotação ]`.
4. **Ator:** Clica em "Ver Propostas" em um projeto que possui lances comerciais.
5. **Sistema:** Executa a extensão `<<extend>> UC_CLI_027 (Comparar Propostas Recebidas)`.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cancelamento de Cotação:**
  1. O cliente decide não prosseguir com a obra e clica em "Cancelar Projeto".
  2. O sistema altera o status para `Cancelado` e notifica os prestadores concorrentes.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Nenhum Projeto Cadastrado:**
  1. O cliente ainda não criou cotações.
  2. O sistema exibe tela de boas-vindas com botão destacado: *"Criar Meu Primeiro Orçamento de Obra"*.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN017 (B2B):** Suporte a negociações customizadas para clientes do setor de construção civil.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Filtros por status do projeto (`status`: `open`, `bids_received`, `completed`).

### Saídas:
- Painel de cartões de projetos de obras com métricas e atalhos de decisão.
