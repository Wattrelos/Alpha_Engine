# UC_ADM_001 - Gerenciar Catálogo de Produtos

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_001` |
| **Nome** | Gerenciar Catálogo de Produtos, Categorias e Opções |
| **Módulo** | Painel Administrativo - Operações de Negócio |
| **Atores Primários** | Operador do Painel (*Operator*), Administrador Geral (*Admin*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / CRUD Administrativo |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF001](/docs/requirements/functional/functional_requirements.yaml) (Cadastro produtos/fotos HD), [RF002](/docs/requirements/functional/functional_requirements.yaml) (Specs técnicas), [RF003](/docs/requirements/functional/functional_requirements.yaml) (Categorização), [RF004](/docs/requirements/functional/functional_requirements.yaml) (Múltiplas unidades), [RF005](/docs/requirements/functional/functional_requirements.yaml) (CRUD Admin), [RF023](/docs/requirements/functional/functional_requirements.yaml) (Gestão catálogo/preços)<br>**RN:** [RN001](/docs/requirements/business_rules/business_rules.yaml) (Unidades fracionadas), [RN002](/docs/requirements/business_rules/business_rules.yaml) (Peso e dimensões cubagem), [RN003](/docs/requirements/business_rules/business_rules.yaml) (Info técnica), [RN017](/docs/requirements/business_rules/business_rules.yaml) (Tabelas de preço varejo/atacado)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Interface administrativa em abas), [RNF006](/docs/requirements/non_functional/non_functional_requirements.yaml) (Controle de concorrência otimista) |

---

## 1. 🎯 Descrição Sumária
Permite aos operadores comerciais e administradores o controle completo do ciclo de vida dos produtos, departamentos, categorias e tabelas de atributos técnicos da loja, incluindo criação de novos produtos com upload de fotos em alta definição, parametrização obrigatória de peso e cubagem para frete, configuração de unidades fracionadas (m² para caixas), precificação segmentada (varejo vs. atacado B2B) e controle de versões com bloqueio otimista.

---

## 2. ⚡ Pré-Condições
- Usuário administrativo autenticado no dashboard com permissão de acesso ao módulo de catálogo (`catalog/product`).

---

## 3. ✅ Pós-Condições
- Produto, categorias e SKUs variantes persistidos nas tabelas `tbkk_product`, `tbkk_product_description`, `tbkk_product_image`, `tbkk_product_to_category`.
- Cache do catálogo invalidado no Redis.

---

## 4. 🚀 Gatilho (Trigger)
O operador clica em "Catálogo > Produtos" no menu lateral do painel e seleciona "Novo Produto" ou "Editar".

---

## 5. 🔄 Fluxo Principal (Cadastrar Novo Produto)

1. **Ator:** Acessa o formulário de cadastro de produto no painel.
2. **Sistema:** Exibe a interface estruturada em abas:
   - **Geral:** Nome, Descrição formatada em Markdown, Meta Tags SEO e Palavras-chave;
   - **Dados:** Modelo/SKU, Código de Barras (EAN), Preço Padrão (Varejo), Preço Construtora (Atacado - RN017), Quantidade em Estoque Inicial, Ponto de Pedido Mínimo (RN006), Peso Bruto (kg), Comprimento, Largura e Altura (cm) (RN002);
   - **Ligações:** Fabricante/Marca, Categorias e Filtros Técnicos associados;
   - **Opções & Unidades:** Tipo de Unidade (`UN`, `M2`, `CX`), fator de conversão de metragem por caixa e variações de voltagem/cor (RN001/RN003);
   - **Imagens:** Upload múltiplo de fotos HD com gerador de WebP;
   - **SEO:** Slug da URL amigável (`/produto/porcelanato-60x60`).
3. **Ator:** Preenche os campos obrigatórios, anexa as fotos e clica em "Salvar".
4. **Sistema:** Valida as regras de negócio via DTO no backend.
5. **Sistema:** Abre transação MySQL, persiste os dados com coluna `version = 1`, gera as miniaturas e expurga o cache do Redis.
6. **Sistema:** Exibe alerta de sucesso e lista o novo item no catálogo.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Edição de Preços em Lote:**
  1. O operador seleciona múltiplos produtos na tabela e clica em "Reajuste de Preços".
  2. Informa uma porcentagem de aumento/desconto geral e aplica em massa.
- **FA02 - Inativação Lógica de Produto:**
  1. O operador altera o status para `Desativado (0)`. O sistema oculta o item das vitrines públicas preservando o histórico de compras.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Conflito de Concorrência Otimista (Dois Operadores Editando ao Mesmo Tempo):**
  1. O operador B tenta salvar um produto que já foi alterado e salvo pelo operador A segundos antes.
  2. O sistema detecta que `version_enviada != version_banco`, bloqueia a sobreposição acidental e alerta: *"Este produto foi modificado por outro usuário. As alterações foram recarregadas para sua revisão."* (RNF006).

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001, RN002, RN003, RN017:** Validação estrita de unidades, pesos, atributos técnicos e segmentação de preços.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Formulário administrativo de produto completo com abas e upload de mídia.

### Saídas:
- Registro persistido e catálogo público sincronizado em tempo real.
