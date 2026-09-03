# UC_CLI_003 - Visualizar Detalhes do Produto (PDP)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_003` |
| **Nome** | Visualizar Detalhes do Produto |
| **Módulo** | Loja Virtual - Catálogo, Busca & Mídia |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Exibição |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF001](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Fotos HD), [RF002](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Especificações técnicas), [RF012](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (PDP dedicada)<br>**RN:** [RN001](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Variações de unidades), [RN002](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Peso e dimensões), [RN003](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Info técnica obrigatória)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Ergonomia visual), [RNF002](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Velocidade) |

---

## 1. 🎯 Descrição Sumária
Permite ao usuário acessar a Página de Detalhes do Produto (PDP - *Product Detail Page*), visualizando galeria de imagens em alta resolução com zoom, descrição técnica completa, dimensões, peso, garantia, disponibilidade de estoque em tempo real, preço à vista no PIX, opções de parcelamento no cartão e simulador de frete por CEP.

---

## 2. ⚡ Pré-Condições
- O produto deve existir no banco de dados e possuir status ativo.

---

## 3. ✅ Pós-Condições
- A PDP é renderizada com todos os dados técnicos e comerciais.
- O histórico de produtos vistos recentemente pelo cliente é atualizado na sessão.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica no card de um produto a partir da vitrine, busca, catálogo ou link direto.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Clica no card de um produto na vitrine ou busca.
2. **Sistema:** Resolve a URL SEO amigável (`/product/{slug}`) através do roteador.
3. **Sistema:** Carrega os dados do produto (tabelas `tbkk_product`, `tbkk_product_description`, `tbkk_product_image`), invocando `<<include>> UC_CLI_005 (Carregar Mídia & Cache On-Demand)`.
4. **Sistema:** Renderiza a PDP estruturada:
   - Breadcrumb navegável com hierarquia de categorias;
   - Galeria de imagens HD com suporte a miniaturas e zoom óptico;
   - Título oficial, SKU, código de barras e marca do fabricante com logotipo;
   - Preço regular, preço com desconto para PIX (RN016) e tabela de parcelamento;
   - Badge de disponibilidade de estoque (RN005);
   - Simulador de frete dinâmico por CEP;
   - Seletor de variações de opções (`<<extend>> UC_CLI_004`);
   - Abas inferiores com: Ficha Técnica Detalhada (RN003), Instruções de Aplicação/Uso, Avaliações de Clientes e Produtos Relacionados (*Cross-Selling* - RF008).
5. **Ator:** Avalia as especificações do produto e decide prosseguir para adicionar ao carrinho (`UC_CLI_006`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Simulação de Frete na PDP:**
  1. O ator digita seu CEP no campo "Calcular Frete" da PDP.
  2. O sistema calcula o peso cúbico com base em peso e dimensões (RN002) e exibe as modalidades de entrega disponíveis (Correios, Transportadora, Retirada BOPIS - RN007/RN008) com prazos e valores.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Produto Inativo ou Inexistente (404):**
  1. O slug da URL não corresponde a nenhum produto ativo.
  2. O sistema exibe a página de erro 404 personalizada sugerindo categorias e produtos em destaque.
- **FE02 - Produto Fora de Estoque (Esgotado):**
  1. O produto possui saldo zerado (`quantity = 0`).
  2. O sistema desabilita o botão "Comprar", exibe o badge "Produto Indisponível" e apresenta o formulário "Avise-me quando chegar" (*Back-in-stock notification*).

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN002 (Peso e Dimensões):** Exibição de peso bruto, largura, altura e comprimento para subsidiar o frete.
- **RN003 (Informações Técnicas):** Apresentação obrigatória de especificações por segmento (ex: tempo de secagem para tintas, resistência para cimento).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Slug ou ID do produto.
- CEP para simulação de frete.

### Saídas:
- Página completa do produto com galeria de fotos, especificações, tabela de frete e botão de compra.
