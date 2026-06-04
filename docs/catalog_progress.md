# Refatoração do Subsistema de Catálogo (Categorias e Produtos) - Alpha Engine

Este documento registra as melhorias e modernizações arquiteturais realizadas no subsistema de Catálogo da **Alpha Engine**, com foco em SEO amigável, roteamento nativo, refatoração de templates Twig com padrões BEM e remoção total do framework Bootstrap.

---

## 🔍 1. Roteamento Amigável e Paginação de Categorias

Com o abandono das rotas herdadasdo código legado, a geração de URLs para o catálogo foi migrada integralmente para o novo padrão amigável da **Alpha Engine**:

*   **Padrão de Rota**: As categorias agora são acessadas por meio da estrutura de rotas limpas:
    `/{lang}/categoria/{slug}`
*   **Geração de Links no Repositório**: A responsabilidade de gerar os links corretos foi centralizada no [CategoryRepository](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CategoryRepository.php), eliminando a query string antiga `index.php?route=product/category` de todos os seletores e menus.
*   **Paginação e Filtros Limpos**: Parâmetros de navegação como página (`?page={page}`), limites (`&limit=X`) e ordenação (`&sort=Y&order=Z`) foram padronizados de forma desacoplada e injetados de forma segura nos componentes Twig, garantindo conformidade com boas práticas de SEO.

---

## 🎨 2. Refatoração Visual da Página do Produto (`show.html.twig`)

O template de exibição de detalhes do produto ([show.html.twig](file:///var/www/html/agsonhos/resources/views/pages/product/show.html.twig)) foi totalmente reescrito para extinguir o acoplamento com o Bootstrap.

### Marcação Semântica e Metodologia BEM:
Toda a marcação foi reestruturada utilizando a convenção BEM (Block, Element, Modifier) sob o prefixo `egen-` para isolamento de escopo:
*   `.egen-product-page`: Container raiz da página.
*   `.egen-product-layout`: Grid de duas colunas (mídia e informações).
*   `.egen-product-gallery`: Sistema de galeria de imagens e miniaturas com troca interativa.
*   `.egen-product-price-card`: Card com estilo exclusivo (gradiente e borda colorida) destacando preços especiais e normais.
*   `.egen-product-options`: Controles para seleção de opções como rádio, checkbox e inputs de texto.
*   `.egen-product-action-row`: Área de compra integrando seletor de quantidade e o botão principal de adição ao carrinho.

### Folha de Estilos Customizada (`new-stylesheet.css`):
As declarações visuais foram acopladas ao fim de [new-stylesheet.css](file:///var/www/html/agsonhos/public_html/css/custom/new-stylesheet.css), respeitando a paleta de cores escura, acentos em laranja e efeitos de glassmorphism definidos para a nova identidade visual da loja.

---

## ⚡ 3. Interatividade Standalone com Vanilla JavaScript

Para assegurar o funcionamento dos componentes sem carregar bibliotecas JS robustas de terceiros ou acoplamento a frameworks, foi implementado comportamento direto na view:

*   **Abas de Informação (Tabs Controller)**: 
    A alternância entre as abas de "Descrição" e "Especificações Técnicas" foi codificada em Vanilla JS nativo no rodapé do template, gerenciando as classes de ativação (`egen-tab-trigger--active` e `egen-tab-pane--active`) de forma direta via seletores de eventos no DOM.
*   **Seletor de Quantidades Dinâmico**:
    Foram adicionados botões de incremento e decremento (`+` e `-`) que manipulam o input de quantidade de compra em tempo real, limitando dinamicamente o valor mínimo estabelecido pelo cadastro do produto.

---

## 📈 Benefícios Obtidos

*   **SEO Avançado**: URLs amigáveis e limpas indexam muito melhor nos motores de busca (como Google) em comparação com URLs procedurais cheias de parâmetros dinâmicos.
*   **Isolamento Estético**: O layout do catálogo está totalmente imune a modificações globais do Bootstrap, garantindo que o tema dark e premium do e-commerce permaneça inalterado.
*   **Performance (Lightweight)**: A eliminação do Bootstrap JS reduziu o tempo de processamento de scripts no navegador, resultando em interações instantâneas na galeria de imagens, abas e carrinho.
