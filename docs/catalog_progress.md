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

### Estrutura de Estilos Modular (Sass/SCSS):
As declarações visuais foram refatoradas e especializadas em parciais SCSS dedicados (ex: [_product.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_product.scss) e [_category.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_category.scss)) sob a pasta `public_html/css/base/pages/`. A compilação é centralizada pelo entrypoint [new-stylesheet.scss](file:///var/www/html/agsonhos/public_html/css/custom/new-stylesheet.scss), gerando o arquivo final compilado [new-stylesheet.css](file:///var/www/html/agsonhos/public_html/css/custom/new-stylesheet.css). Os estilos respeitam a paleta de cores escura, acentos em laranja e efeitos de glassmorphism definidos para a nova identidade visual da loja.

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

---

## 🔒 4. Saneamento do Banco de Dados e Chaves Referenciais (Pseudo-Null)
Para viabilizar chaves estrangeiras restritivas reais no MySQL e manter o modelo Domain-Driven Design (DDD) livre de inconsistências:
- **Categorias e Fabricantes**: Os registros residuais com ID `0` (ex: `parent_id = 0` na tabela de categorias ou `manufacturer_id = 0` na tabela de produtos) foram migrados para `NULL` após a alteração estrutural das colunas para permitir valores nulos.
- **Associação de Produtos e Categorias**: Saneados os registros de `tbkk_product_to_category` onde `category_id = 0`, eliminando relações órfãs ou associando a categorias reais.

## 🏢 5. Gestão Administrativa de Fabricantes (Admin Panel)
Implementação completa da área de gerenciamento de marcas/fabricantes na administração da loja:
- **Controladores Slim (Actions)**: `ListManufacturersAction`, `CreateManufacturerAction`, `StoreManufacturerAction`, `EditManufacturerAction`, `UpdateManufacturerAction`, `DeleteManufacturerAction`.
- **Arquitetura Domain & Mapper**: Interação do controlador administrativo unicamente com as interfaces `ManufacturerRepository` e `ManufacturerMapper`, blindando o domínio contra regras de banco de dados.
- **Interface Visual (Twig)**: Criação de visões Twig responsivas sob `resources/views/admin/catalog/manufacturer/` para listagem e formulários de edição e criação.

---

## 🎨 6. Modularização e Compilação Dinâmica de CSS/SCSS
Para elevar a manutenibilidade visual e unificar o design premium da Alpha Engine, migramos todas as folhas de estilo personalizadas de CSS puro para Sass/SCSS estruturado:
*   **Compilação Pura PHP**: Integramos o pacote `scssphp/scssphp` no Composer. Criamos o script dinâmico [compile-scss.php](file:///var/www/html/agsonhos/scratch/compile-scss.php) que escaneia a pasta `custom/` e compila automaticamente todos os pontos de entrada principais (`new-stylesheet.scss`, `returns-institutional.scss`, `addresses.scss`, `orders.scss`) em arquivos `.css` equivalentes. O processo de compilação é disparado via CLI:
    ```bash
    composer build-css
    ```
*   **Compartilhamento de Estilos (Design Tokens)**: Criamos o arquivo parcial [_shared-mixins.scss](file:///var/www/html/agsonhos/public_html/css/base/_shared-mixins.scss) contendo seletores placeholders do Sass (`%premium-card`, `%premium-button`, `%premium-hero`, `%premium-input`, `%premium-breadcrumb`). Isso permite reutilizar a identidade visual unificada e os efeitos de glassmorphism em diferentes módulos sem duplicar código final compilado.
*   **Organização por Contexto**: Dividimos o CSS em módulos por raia de atuação em `public_html/css/base/pages/`:
    *   [_category.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_category.scss): Visual de listagem, grades e filtros de categorias.
    *   [_product.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_product.scss): Abas interativas, galeria e simulação de frete.
    *   [_cart.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_cart.scss): Carrinho de compras.
    *   [_checkout.scss](file:///var/www/html/agsonhos/public_html/css/base/pages/_checkout.scss): Fluxo de fechamento de pedido.

---

## ⚡ 7. Busca Textual de Alta Performance (MySQL Full-Text Search)

Substituição da busca legada via `LIKE '%...%'` por recurso nativo de **MySQL Full-Text Search**:
*   **Índice FULLTEXT Composto**: Adicionado o índice `idx_ft_product_search` nas colunas `(name, description, tag)` da tabela `agsc_product_description`.
*   **Modo Booleano & Sanitização**: O método `prepareFullTextQuery()` no [ProductMapper](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ProductMapper.php) higieniza caracteres de sintaxe booleana e adiciona operadores `+` e wildcards `*` para permitir buscas por prefixos (ex: `"smart tv"` $\rightarrow$ `"+smart* +tv*"`).
*   **Fallback de Segurança**: Para buscas por modelo (`p.model`) ou termos com menos de 3 caracteres (ex: `"TV"`), o sistema executa automaticamente o fallback estruturado garantindo que nenhum produto seja ignorado por limitações de tamanho de token do InnoDB.
*   **Diagrama de Sequência e EER**: Atualizados os diagramas [product_search.puml](file:///var/www/html/agsonhos/docs/workflows/sequence_diagrams/product_search.puml) e [EERDiagram.puml](file:///var/www/html/agsonhos/docs/database/EERDiagram.puml) documentando a nova estrutura de dados e fluxo de execução.


