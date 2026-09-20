# UC_CORE_002 - Gestão CRUD de SKUs

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_002` |
| **Nome** | Gestão CRUD de SKUs e Catálogo Administrativo |
| **Módulo** | Núcleo Central (Core) - Backoffice & Catálogo |
| **Atores Primários** | Administrador Geral (*Admin*), Operador do Painel (*Operator*) |
| **Atores Secundários** | Sistema Alpha Engine, Sistema de Arquivos / CDN |
| **Tipo** | Condução / CRUD Administrativo de Governança |
| **Frequência de Uso** | Diária / Alta |
| **Rastreabilidade** | **RF:** [RF001](/docs/requirements/functional/functional_requirements.yaml) (Cadastro produtos/fotos HD), [RF003](/docs/requirements/functional/functional_requirements.yaml) (Categorização), [RF005](/docs/requirements/functional/functional_requirements.yaml) (CRUD Admin de SKUs)<br>**RN:** [RN001](/docs/requirements/business_rules/business_rules.yaml) (Unidades fracionadas m²), [RN002](/docs/requirements/business_rules/business_rules.yaml) (Peso e dimensões obrigatórias), [RN003](/docs/requirements/business_rules/business_rules.yaml) (Info técnica), [RN006](/docs/requirements/business_rules/business_rules.yaml) (Ponto de pedido mínimo)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Interface administrativa responsiva), [RNF006](/docs/requirements/non_functional/non_functional_requirements.yaml) (Auditoria e concorrência) |

---

## 1. 🎯 Descrição Sumária
Permite aos administradores e operadores autorizados realizar a manutenção completa do ciclo de vida dos produtos (SKUs) no sistema: criação, leitura, atualização e inativação (CRUD). O cadastro exige a parametrização rigorosa de campos vitais para materiais de construção: dimensões físicas e peso real para cubagem de frete, código NCM fiscal, tipo de unidade de venda (unidade, m², caixa, barra, saco), fator de conversão de metragem, estoque de segurança e upload de múltiplas fotos em alta definição.

---

## 2. ⚡ Pré-Condições
1. O usuário deve estar autenticado no Painel Administrativo com perfil que possua permissão de escrita no módulo `catalog/product` (RBAC).
2. Devem existir categorias fiscais e mercadológicas previamente cadastradas.

---

## 3. ✅ Pós-Condições
- Dados do produto persistidos nas tabelas `tbkk_product`, `tbkk_product_description`, `tbkk_product_to_category` e `tbkk_product_image`.
- Imagens otimizadas e redimensionadas para WebP no servidor de mídia.
- Cache de vitrine e busca invalidado imediatamente no Redis.

---

## 4. 🚀 Gatilho (Trigger)
O administrador acessa o menu "Catálogo > Produtos" e clica em "Adicionar Novo" ou "Editar SKU".

---

## 5. 🔄 Fluxo Principal (Cadastrar / Atualizar SKU)

1. **Ator:** Acessa o formulário de cadastro/edição de produto no painel.
2. **Sistema:** Exibe a tela com abas organizadas:
   - **Geral:** Nome comercial, descrição completa em rich text/Markdown, tags para busca e meta-dados SEO;
   - **Dados Fiscais & Logística:** Código interno SKU, Código EAN-13, NCM (Nomenclatura Comum do Mercosul), CEST, Peso Bruto (kg), Comprimento (cm), Largura (cm) e Altura (cm) (RN002);
   - **Preço & Estoque:** Preço de Venda Varejo, Preço Atacado/Construtora (RN017), Quantidade em Estoque Físico e Limiar de Ponto de Pedido Mínimo (RN006);
   - **Unidade & Fracionamento:** Unidade base (`UN`, `M2`, `CX`, `SAC`, `KG`), seletor de produto fracionável e fator de metragem por caixa (ex: `1 caixa = 2.40 m²`) (RN001);
   - **Atributos Técnicos:** Seleção de categoria (ex: Tintas) e preenchimento dos campos obrigatórios (acabamento, rendimento, tempo de secagem - RN003);
   - **Galeria de Imagens:** Upload em lote com arraste (*drag-and-drop*) de imagens HD.
3. **Ator:** Preenche os dados obrigatórios e seleciona 4 fotos de alta resolução.
4. **Ator:** Clica em "Salvar Produto".
5. **Sistema:** Executa as validações de integridade no backend:
   - Verifica se o SKU ou código EAN já existem no catálogo;
   - Valida se peso e dimensões são maiores que zero (`peso > 0`, `comprimento > 0`, `largura > 0`, `altura > 0`);
   - Valida a consistência do código NCM (8 dígitos numéricos válidos).
6. **Sistema:** Salva os registros em transação atômica MySQL.
7. **Sistema:** Processa o upload das imagens, gerando miniaturas e versões WebP de alta compressão sem perda de nitidez.
8. **Sistema:** Dispara evento de invalidação de cache no Redis (`catalog_cache:product_{id}`).
9. **Sistema:** Exibe mensagem de sucesso: *"Produto gravado com sucesso no catálogo!"*.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Clonar Produto Existente:**
  1. No passo 1, o administrador seleciona um produto com especificações semelhantes (ex: Porcelanato 60x60 Bege) e clica em "Duplicar".
  2. O sistema pré-preenche o formulário limpando apenas o SKU e EAN, permitindo cadastro acelerado.
- **FA02 - Desativação Lógica (Soft Delete):**
  1. O administrador clica em "Desativar Produto".
  2. O sistema altera o status para `Inativo` (`status = 0`), mantendo o histórico de pedidos passados íntegro e removendo o item da vitrine.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Bloqueio por Falta de Dimensões de Cubagem ou NCM (RN002):**
  1. No passo 5, o administrador deixa o peso ou as dimensões zeradas ou omite o código NCM.
  2. O sistema bloqueia a gravação e destaca os campos em vermelho: *"Atenção: Dimensões físicas e NCM são mandatórios para o cálculo de frete de materiais de construção e emissão fiscal."*.
- **FE02 - Conflito de Concorrência Otimista:**
  1. Outro operador atualizou o mesmo produto simultaneamente.
  2. O sistema alerta: *"Este registro foi modificado por outro usuário. Recarregue a página antes de salvar."*.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Variações de Unidades):** Exigência de fator de conversão fixo caso a unidade seja comercializada em caixas fracionadas por m².
- **RN002 (Peso e Dimensões Obrigatórias):** Requisito indispensável para qualquer item físico que utilize o motor de cálculo logístico.
- **RN003 (Informações Técnicas por Categoria):** Garante que especificações obrigatórias de cada departamento estejam preenchidas.
- **RN006 (Alerta de Baixo Estoque):** Registro do ponto de pedido para o monitoramento de ruptura.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `name`, `description`, `sku`, `ean`, `ncm`, `price`, `price_wholesale`.
- `weight` (kg), `length`, `width`, `height` (cm).
- `unit_code`, `box_coverage_m2`, `min_stock_alert`.
- Upload de imagens (`files[]`).

### Saídas:
- ID interno do produto criado/atualizado (`product_id`).
- Mensagem de confirmação e listagem atualizada na grade administrativa.
