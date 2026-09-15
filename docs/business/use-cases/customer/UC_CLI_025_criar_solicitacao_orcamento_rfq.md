# UC_CLI_025 - Criar Solicitação de Orçamento (RFQ)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_025` |
| **Nome** | Criar Solicitação de Orçamento (RFQ) |
| **Módulo** | Loja Virtual - Cotações & Projetos (RFQ / BoQ) |
| **Atores Primários** | Cliente Logado (*Customer* - Construtoras e Clientes em Reforma) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / B2B & Projetos |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF004](/docs/requirements/functional/functional_requirements.yaml) (Venda fracionada/múltiplas unidades), [RF014](/docs/requirements/functional/functional_requirements.yaml) (Perfil cliente)<br>**RN:** [RN015](/docs/requirements/business_rules/business_rules.yaml) (Desconto progressivo por volume), [RN017](/docs/requirements/business_rules/business_rules.yaml) (Preços de atacado B2B)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Interface de montagem de lista de materiais) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente corporativo ou consumidor em fase de construção criar uma Solicitação de Cotação Formal / Orçamento (RFQ - *Request for Quotation*) na rota `/projetos/novo`, especificando a lista completa de materiais de construção requeridos (BoQ - *Bill of Quantities*), volumes estimados, prazos de entrega da obra, arquivos anexos da planta/memorial descritivo e solicitação de condições comerciais personalizadas.

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- RFQ persistida na tabela `tbkk_rfq_project` com status `Aberta para Cotação`.
- Notificação encaminhada para fornecedores parceiros e vendedores técnicos da Alpha Engine.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Solicitar Orçamento de Obra (RFQ)" no menu superior ou no painel do cliente.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa `/projetos/novo`.
2. **Sistema:** Exibe o formulário estruturado de abertura de cotação de projeto:
   - Título do Projeto (ex: *"Reforma Residencial 180m² - Edifício Horizon"*);
   - Categoria da Obra (Alvenaria/Estrutural, Acabamentos & Pisos, Elétrica, Hidráulica);
   - Local da Obra (Endereço/CEP para cálculo de logística de carga pesada);
   - Data Limite para Recebimento de Propostas e Prazo Desejado de Entrega;
   - Tabela de Lista de Materiais (*Bill of Quantities* - BoQ): Item, Quantidade, Unidade (m², sacos, caixas, barras).
3. **Ator:** Preenche as informações da obra, insere os itens da lista de materiais e anexa a planilha ou memorial descritivo em PDF/DWG.
4. **Ator:** Clica em "Publicar Solicitação de Orçamento".
5. **Sistema:** Valida o preenchimento dos campos obrigatórios e integridade dos anexos.
6. **Sistema:** Gera o código do projeto (ex: `RFQ-2026-089`), salva a entidade e despacha notificação aos prestadores/vendedores credenciados.
7. **Sistema:** Redireciona o cliente para a página de acompanhamento do projeto (`UC_CLI_026`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Importação de Lista de Materiais via Excel/CSV:**
  1. O cliente faz o upload de uma planilha XLSX contendo as colunas SKU, Descrição e Quantidade.
  2. O sistema processa o arquivo, faz o mapeamento inteligente dos produtos do catálogo e pré-preenche a tabela do projeto automaticamente.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Arquivo Anexo Inválido ou Superior ao Tamanho Limite:**
  1. O cliente tenta anexar um arquivo com extensão não permitida ou maior que 25MB.
  2. O sistema bloqueia o upload e alerta: *"Formato não suportado. Por favor, envie arquivos em formato PDF, DWG, PNG ou XLSX de até 25MB."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN015 & RN017:** Elegibilidade para preços especiais por volume em projetos de grande porte.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `project_title`, `category_id`, `delivery_postcode`, `deadline_date`, `materials_list` (array), `attachments` (files).

### Saídas:
- Número do projeto RFQ gerado e painel de status da cotação.
