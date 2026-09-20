# UC_CORE_001 - Navegar no Catálogo & PDP

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_001` |
| **Nome** | Navegar no Catálogo & Visualizar Detalhes do Produto (PDP) |
| **Módulo** | Núcleo Central (Core) - Descoberta & Experiência de Compra |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine, Motor de Cache Redis |
| **Tipo** | Condução / Navegação & Consulta Técnica |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF001](/docs/requirements/functional/functional_requirements.yaml) (Fotos HD), [RF002](/docs/requirements/functional/functional_requirements.yaml) (Ficha técnica), [RF011](/docs/requirements/functional/functional_requirements.yaml) (Busca facetada), [RF012](/docs/requirements/functional/functional_requirements.yaml) (Página dedicada PDP)<br>**RN:** [RN001](/docs/requirements/business_rules/business_rules.yaml) (Venda m²/cx), [RN002](/docs/requirements/business_rules/business_rules.yaml) (Peso/cubagem), [RN003](/docs/requirements/business_rules/business_rules.yaml) (Specs por categoria)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Interface intuitiva), [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (LCP < 2.5s) |

---

## 1. 🎯 Descrição Sumária
Permite ao usuário (visitante anônimo ou cliente autenticado) explorar o catálogo completo de materiais de construção por meio de busca textual indexada, filtros facetados (preço, marca, acabamento, voltagem) e árvores taxonômicas de departamentos. Ao selecionar um item, o sistema renderiza a Página de Detalhes do Produto (PDP) completa, apresentando galeria de fotos HD com zoom, especificações técnicas detalhadas, calculadora de metragem quadrada (para pisos e revestimentos) e simulador de frete por CEP.

---

## 2. ⚡ Pré-Condições
1. O sistema deve estar operacional e com os servidores web e de banco de dados ativos.
2. Devem existir categorias e produtos ativos cadastrados no sistema.

---

## 3. ✅ Pós-Condições
- O usuário visualiza as informações técnicas precisas, variações de acabamento/voltagem, disponibilidade de estoque em tempo real e valor do frete estimado para sua região.

---

## 4. 🚀 Gatilho (Trigger)
O usuário acessa o site, digita um termo na barra de pesquisa ou clica em uma categoria de produtos.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Digita um termo de busca (ex: "Porcelanato Esmaltado 60x60") ou navega pelo menu de categorias.
2. **Sistema:** Consulta o índice de busca e o cache Redis, retornando a listagem com filtros facetados laterais (marca, formato, cor, preço, tipo de borda).
3. **Ator:** Aplica um filtro (ex: "Acabamento: Polido") e clica no card do produto desejado.
4. **Sistema:** Carrega a Página de Detalhes do Produto (PDP) contendo:
   - Galeria de imagens em alta definição (WebP otimizado) com zoom dinâmico;
   - Nome, código SKU, fabricante, avaliação média e selo de disponibilidade;
   - Preço à vista (com desconto no PIX - RN016) e preço parcelado no cartão;
   - Ficha técnica completa (normas ABNT, PEI, absorção de água, peso por caixa - RN002, RN003);
   - Calculadora integrada de metragem: campo para informar a área do ambiente em m² com margem de segurança de quebra recomendada (10%);
   - Simulador de frete por CEP com opções para correios ou transporte rodoviário com Munck.
5. **Ator:** Insere a área de 35 m² na calculadora.
6. **Sistema:** Converte a área informada para a quantidade necessária de caixas fechadas (`Math.ceil(35 / m2_por_caixa)` - RN001) e atualiza o valor total em tempo real.
7. **Ator:** Informa o CEP de destino e clica em "Calcular Frete".
8. **Sistema:** Calcula a cubagem e o peso total, consultando a tabela logística e exibindo as opções de frete (entrega rodoviária com descarga na obra ou retirada gratuita em loja - RN007, RN008).
9. O caso de uso encerra com o produto pronto para inserção no carrinho.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Seleção de Variação / Voltagem:**
  1. No passo 4, tratando-se de ferramentas elétricas (ex: Serra Mármore), o sistema apresenta seletores de voltagem (110V / 220V).
  2. O ator seleciona "220V".
  3. O sistema atualiza o SKU específico, o saldo de estoque correspondente e os dados técnicos.
- **FA02 - Consulta por Cliente PJ (Preço de Atacado):**
  1. No passo 4, se o cliente estiver logado com perfil Pessoa Jurídica (Construtora), o sistema exibe automaticamente a tabela de preços corporativos com desconto por volume (RN017).

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Produto Sem Estoque (Ruptura):**
  1. No passo 4, o sistema detecta que o saldo em estoque é zero (`quantity = 0`).
  2. O botão "Comprar" é desabilitado e substituído pelo componente "Avise-me quando chegar" (coleta de e-mail e WhatsApp).
- **FE02 - CEP Não Atendido para Cargas Pesadas:**
  1. No passo 8, o CEP informado localiza-se fora do raio de atuação de transporte pesado com descarga mecânica.
  2. O sistema exibe um aviso informando a limitação e sugere a modalidade de Retirada na Loja (BOPIS - RN008).

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN001 (Variações de Unidades):** Produtos classificados como pisos/revestimentos só podem ser vendidos em caixas completas fracionadas por m².
- **RN002 (Peso e Dimensões Obrigatórias):** Dados fundamentais para a determinação da cubagem e da transportadora adequada.
- **RN003 (Informações Técnicas por Categoria):** Exibição compulsória de atributos específicos de materiais de construção.
- **RN016 (Desconto por Modalidade):** Destaque ao desconto de 5% a 10% nas modalidades à vista (PIX e dinheiro).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Termo de busca (`query`), filtros facetados (`filter_attributes[]`).
- SKU ou ID do produto (`product_id`).
- Área de piso (`area_m2`), margem de perda (`breakage_margin_percent`).
- CEP de destino (`destination_cep`).

### Saídas:
- Ficha técnica completa, galeria de imagens WebP, quantidade convertida em caixas, prazos e valores de frete por transportadora/Correios.
