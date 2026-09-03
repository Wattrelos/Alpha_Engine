# [FEATURE #98] Implementar Detalhes do Produto no PDV (Terminal de Vendas)

## 1. Descrição e Contexto
Atualmente, a interface do Ponto de Venda (**PDV / POS**) da **Alpha Engine** exibe os produtos no catálogo de forma simplificada (apenas imagem miniatura, nome, código/modelo e preço de venda). 

Durante o atendimento de balcão e pré-venda, os operadores e vendedores frequentemente precisam consultar informações técnicas adicionais sobre os produtos — como dimensões (comprimento, largura, altura), peso, código de barras (EAN/GTIN), categoria, estoque disponível e descrição detalhada — para sanar dúvidas imediatas dos clientes sem interromper o fluxo de atendimento nem abandonar a tela de vendas.

Esta funcionalidade visa implementar um **painel contextual de "Detalhes do Produto"** na tela do PDV (`register-control.twig`), atualizado em tempo real conforme o operador foca ou seleciona itens no catálogo ou na busca, garantindo máxima agilidade operacional e rica experiência de uso.

---

## 2. Comportamento Atual vs. Comportamento Esperado

### 2.1 Comportamento Atual
* O catálogo do PDV (`#products_grid`) exibe apenas cards compactos com thumbnail, nome, preço e botão de inserção.
* Informações técnicas como medidas, peso para transporte/frete balcão, código de barras EAN e especificações completas não estão acessíveis na tela do PDV.
* Para verificar tais detalhes, o vendedor precisa acessar o painel administrativo geral em outra aba, impactando o tempo de atendimento.

### 2.2 Comportamento Esperado
* Um painel lateral integrado (ou card contextual retrátil/responsivo) dedicado à exibição rica de detalhes do produto em foco.
* Ao navegar pelos produtos via mouse (hover/click) ou via teclado (setas direcionais `ArrowUp` / `ArrowDown` na lista de busca), o painel reflete instantaneamente os dados do item focado.
* Suporte à alternância de dados para produtos com variações (ex: atributos de cor, voltagem ou tamanho selecionados).
* Fallbacks elegantes para produtos sem imagem cadastrada, medidas zeradas ou sem código de barras.

---

## 3. Requisitos Funcionais (RF)

* **RF01 - Painel Lateral de Detalhes**: Exibir um painel contextual na área de trabalho do PDV (`.pos-workspace`) contendo os metadados do produto selecionado.
* **RF02 - Atualização Reativa em Tempo Real**: Atualizar o conteúdo do painel de detalhes instantaneamente ao navegar pelos itens da grade ou pelos resultados da busca, sem necessidade de recarregar a página.
* **RF03 - Metadados Exibidos**:
  - **Identificação**: Imagem em alta definição (com fallback para `no-image.png`), Nome do Produto, Marca/Fabricante e Categoria.
  - **Códigos**: SKU / Modelo interno e Código de Barras (EAN/GTIN).
  - **Precificação e Promoção**: Preço normal, Preço especial/promocional (se houver) e indicador de desconto.
  - **Disponibilidade**: Quantidade em estoque atual e status de estoque (em estoque / sob encomenda / esgotado).
  - **Especificações Físicas**: Dimensões (Comprimento × Largura × Altura com unidade `cm`/`m`) e Peso (com unidade `kg`/`g`).
  - **Descrição / Ficha Técnica**: Resumo ou descrição curta do item para consulta rápida.
* **RF04 - Ação Direta de Adição**: Disponibilizar atalho/botão de ação rápida dentro do próprio painel de detalhes para adicionar a quantidade desejada ao carrinho de pré-venda.
* **RF05 - Suporte a Variações**: Ao selecionar uma variação específica no modal de variações ou na lista, os campos de SKU, preço, estoque e imagem devem se ajustar dinamicamente à variação filha.

---

## 4. Requisitos Não Funcionais (RNF)

* **RNF01 - Performance & Latência Zero**: A renderização dos detalhes deve ocorrer no client-side utilizando os dados já cacheados/hidratados na busca ou requisições assíncronas com debounce, evitando travamentos na digitação da busca do PDV.
* **RNF02 - Usabilidade & Ergonomia**: O painel deve se integrar harmoniosamente ao layout existente sem comprimir excessivamente a grade de produtos e a lista do carrinho.
* **RNF03 - Responsividade**: Em telas menores ou tablets de PDV, o painel deve se adaptar graciosamente (ex: colapsável ou gaveta deslizante/drawer lateral).
* **RNF04 - Acessibilidade por Teclado**: Permitir navegação fluida pelos produtos e abertura dos detalhes utilizando atalhos de teclado (ex: `Enter` para adicionar, setas para navegar).

---

## 5. Critérios de Aceite (DoD)

- [ ] **Exibição Inicial**: Ao carregar o PDV, o painel apresenta um estado vazio amigável (*placeholder* instruindo o operador a selecionar um produto).
- [ ] **Sincronização de Foco**: Ao clicar ou focar em qualquer produto do catálogo/busca, o painel é preenchido imediatamente com todos os dados correspondentes.
- [ ] **Campos Completos**: O painel exibe com clareza: Nome, SKU, EAN/Código de Barras, Categoria, Dimensões, Peso, Preço, Estoque e Descrição.
- [ ] **Tratamento de Dados Ausentes**: Campos vazios (ex: produto sem dimensões ou sem EAN) exibem traço `-` ou rótulo discreto "Não informado", sem quebrar o layout.
- [ ] **Integração com Carrinho**: O botão de adicionar no painel insere o produto focado diretamente no carrinho de pré-venda.
- [ ] **Compatibilidade com Variações**: Produtos com variações refletem corretamente os dados da variação ativa.
- [ ] **Isolamento de Estilos**: O CSS do novo componente segue a arquitetura modular de estilos da Alpha Engine, sem efeitos colaterais nos demais elementos do PDV.

---

## 6. Considerações de Arquitetura e Implementação

1. **Backend (`SearchProductAction.php` / `ProductMapper.php`)**:
   - Garantir que a ação de busca do PDV (`SearchProductAction`) projete os campos necessários (`ean`, `weight`, `length`, `width`, `height`, `category_name`, `description`) no payload JSON retornado ao front-end.
2. **Frontend Twig (`backend/resources/views/pos/sales-rep/register-control.twig`)**:
   - Estruturar a marcação HTML do painel utilizando as classes utilitárias e componentes Twig existentes.
3. **Gerenciamento de Estado JavaScript**:
   - Manter um objeto de estado local (`currentFocusedProduct`) no script do PDV para renderização reativa do painel de detalhes via funções desacopladas de template.
