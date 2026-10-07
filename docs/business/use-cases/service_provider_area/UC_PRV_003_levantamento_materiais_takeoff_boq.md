# UC_PRV_003 - Levantamento Técnico de Materiais (Takeoff & BoQ)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_PRV_003` |
| **Nome** | Realizar Levantamento Técnico de Materiais (*Material Takeoff / BoQ Tool*) |
| **Módulo** | Portal do Prestador - Orçamentação Técnica de Insumos (*Takeoff & BoQ*) |
| **Atores Primários** | Prestador de Serviços Selecionado (*Awarded Service Provider*) |
| **Atores Secundários** | Sistema Alpha Engine (Catálogo de Produtos, Motor de Importação BoQ) |
| **Tipo** | Condução / Orçamentação Técnica & Especificação de Engenharia |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF036](/docs/requirements/functional/products_quotation.yaml) (Levantamento técnico de materiais - MTO / BoQ), [RF037](/docs/requirements/functional/products_quotation.yaml) (Conversão em cotação e-commerce "Add to Quote")<br>**RN:** [RN001](/docs/requirements/business_rules/business_rules.yaml) (Unidades fracionadas m²/cx), [RN003](/docs/requirements/business_rules/business_rules.yaml) (Especificações obrigatórias), [RN015](/docs/requirements/business_rules/business_rules.yaml) (Desconto por volume no BoQ), [RN017](/docs/requirements/business_rules/business_rules.yaml) (Tabela atacado B2B)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Usabilidade da Takeoff Tool) |

---

## 1. 🎯 Descrição Sumária
Permite ao profissional técnico homologado e escolhido pelo cliente (`UC_CLI_028`) acessar a ferramenta de estimativa técnica (**Takeoff Tool**) na rota `/prestador/projetos/{rfq_id}/takeoff` para criar e gerenciar a lista quantitativa completa de insumos e materiais de construção (**Bill of Quantities - BoQ**). O profissional pesquisa itens do catálogo da Alpha Engine em tempo real (ou importa planilha CSV/XLSX), especifica quantidades com margem de segurança técnica, e submete a lista consolidada para que o cliente aprove e compre diretamente no e-commerce com descontos por volume.

---

## 2. ⚡ Pré-Condições
1. O cliente deve ter homologado e aceito a proposta comercial de mão de obra deste prestador (`UC_CLI_028`), definindo `agsc_project_rfq.selected_provider_id` com o ID do prestador e a proposta como `'accepted'`.
2. O prestador deve estar devidamente autenticado na sessão.
3. Catálogo de produtos da Alpha Engine disponível para consulta de preços e SKUs.

---

## 3. ✅ Pós-Condições
- Cabeçalho do BoQ criado ou atualizado na tabela `agsc_project_boq`.
- Itens discriminados vinculados a SKUs do catálogo persistidos na tabela `agsc_project_boq_item`.
- Valor total orçado de materiais recalculado automaticamente (`total_estimated_amount`).
- Status do projeto atualizado para `'boq_ready'`, notificando o cliente para aprovação e transferência dos itens para o carrinho (`UC_CLI_029`).

---

## 4. 🚀 Gatilho (Trigger)
O prestador clica em "Montar Lista de Materiais (Takeoff)" na notificação de contratação ou no painel de projetos em andamento.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa a rota `/prestador/projetos/{rfq_id}/takeoff`.
2. **Sistema:** Verifica a autorização: valida se `rfq.selected_provider_id == current_provider_id` e se a proposta vinculada está aceita.
3. **Sistema:** Renderiza a interface da ferramenta **Takeoff Tool**:
   - Cabeçalho com dados da obra e cliente contratante;
   - Tabela de Lista de Materiais (BoQ) editável em tempo real;
   - Barra de busca inteligente com autocomplete integrado ao catálogo de produtos;
   - Seletor de importação em lote de planilha (CSV/TSV/XLSX);
   - Painel resumo de custos parciais e cálculo preliminar de economia por volume (RN015).
4. **Ator:** Digita na busca o nome ou SKU de um insumo (ex: *"Cimento CP II-E-32 50kg Votoran"* ou *"Porcelanato Munari 60x60"*).
5. **Sistema:** Consulta via AJAX (`SearchCatalogItemsAction`) e retorna as opções do catálogo com foto miniatura, unidade de medida (saco, caixa, m², barra) e preço de referência.
6. **Ator:** Seleciona o produto, define a quantidade técnica necessária (ex: 45 sacos) e insere uma observação técnica opcional (ex: *"Considerada margem de 10% para quebra no corte"*).
7. **Sistema:** Adiciona a linha na tabela BoQ, aplica conversão de unidade se necessário (RN001) e recalcula o subtotal monetário.
8. **Ator:** Repete o processo para os demais insumos da obra (areia, brita, argamassa AC-III, rejunte, espaçadores).
9. **Ator:** Revisa os itens e clica em "Finalizar e Enviar Lista de Materiais ao Cliente".
10. **Sistema:** Salva os itens na tabela `agsc_project_boq_item`, consolida o valor total em `agsc_project_boq.total_estimated_amount` e altera o status para `'submitted'`.
11. **Sistema:** Envia notificação por e-mail e push ao cliente: *"O prestador [Nome] finalizou a lista de materiais do seu projeto. Clique para revisar e adicionar tudo ao carrinho com desconto."*
12. **Sistema:** Redireciona o prestador com mensagem: *"Lista de materiais (BoQ) enviada ao cliente com sucesso!"*

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Importação em Lote via Planilha Excel/CSV (RF036):**
  1. No passo 4, o prestador prefere não buscar item por item e clica em "Importar Planilha de Materiais (.csv, .xlsx)".
  2. O prestador anexa a planilha de levantamento orçamentário.
  3. O serviço `BoqSpreadsheetImportService` processa o arquivo:
     - Detecta delimitadores e encoding automaticamente;
     - Mapeia as colunas (`SKU/Código`, `Item`, `Unidade`, `Quantidade`, `Preço Unitário`);
     - Associa automaticamente os SKUs correspondentes do catálogo.
  4. O sistema popula a grade da Takeoff Tool instantaneamente com todos os itens importados para conferência visual do prestador.
  5. O fluxo segue para o passo 9.
- **FA02 - Adição de Insumo Especial Não Encontrado no Catálogo:**
  1. O prestador necessita de um item customizado/sob medida não comercializado diretamente na loja online.
  2. O prestador seleciona a opção "Adicionar Insumo Avulso/Genérico", preenchendo descrição, quantidade e unidade sem vincular `product_id`.
  3. O sistema inclui o item como observação de compra externa ou para cotação especial com a equipe comercial.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Tentativa de Acesso por Prestador Não Homologado:**
  1. No passo 2, o usuário tenta acessar a rota de Takeoff de um projeto no qual não foi o prestador selecionado pelo cliente.
  2. O sistema bloqueia o acesso com código HTTP 403 (Forbidden) e redireciona com mensagem: *"Acesso não autorizado. A ferramenta de levantamento de materiais só é liberada para o profissional contratado pelo cliente."*
- **FE02 - Planilha Anexa Corrompida ou com Estrutura Inválida:**
  1. No fluxo FA01, o prestador faz upload de um arquivo com extensões não permitidas ou colunas faltando.
  2. O sistema rejeita o arquivo e apresenta erro: *"Não foi possível ler a planilha. Certifique-se de utilizar colunas com Nome do Item, Quantidade e Unidade. Baixe nosso modelo de exemplo em CSV."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Conversão de Unidades):** Aplica conversão automática de medidas fracionadas (ex: metros quadrados convertidos para caixas fechadas inteiras).
- **RN005 (Estoque):** Sinaliza em tempo real a disponibilidade de estoque para evitar especificação de materiais indisponíveis.
- **RN015 (Descontos por Volume):** Dispara faixas de desconto progressivo para volumes elevados (5% para 10+ un, 10% para 50+ un, 15% para 100+ un e 20% para 250+ un).
- **RN017 (Tabela B2B):** Integração com condições especiais corporativas se o cliente solicitante for PJ/Construtora.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `rfq_id`, `items` (array com `product_id`, `item_name`, `unit`, `quantity`, `unit_price`, `notes`), ou `spreadsheet_file` (upload multipart).

### Saídas:
- `boq_id`, `total_estimated_amount` calculado, status atualizado e encaminhamento para aprovação do cliente.
