## 📦 Estrutura das páginas Twig


### Página Header

ag-header
  └── egen-header__container   (max-width: 1320px, centralizado)
       └── egen-header__grid   (grid: 1.5fr 1fr 1fr 1fr 1fr)
            ├── egen-header__brand-col  (logo + desc + redes sociais)
            ├── header-column × 4       (via molecule)
  └── ag-header-bottom
       └── egen-header__container
            └── egen-header__bottom-row  (copyright ←→ pagamentos)


### Página rodapé:
ag-footer
  └── egen-footer__container   (max-width: 1320px, centralizado)
       └── egen-footer__grid   (grid: 1.5fr 1fr 1fr 1fr 1fr)
            ├── egen-footer__brand-col  (logo + desc + redes sociais)
            ├── footer-column × 4       (via molecule)
  └── ag-footer-bottom
       └── egen-footer__container
            └── egen-footer__bottom-row  (copyright ←→ pagamentos)


### 





### HTML/Twig

### Assets
public_html/css/      # CSS Compilado
public_html/js/       # JS Compilado
public_html/fonts/    # Fontes



resources/views/
├── components/
│   ├── atoms/        # componentes atômicos (botões, inputs, cards)
│   ├── molecules/    # componentes moleculares (footer-column, navbar-item, product-card)
│   └── organisms/    # componentes orgânicos (header, footer, sidebar, full-product-card)
├── utilities/        # classes utilitárias (helpers)
└── pages/            # estilos específicos por página
     └── category
          ├── show.html.twig
                    🏷️ Badge "Oferta" — gradiente vermelho-laranja quando prod.special existe
                    🖼️ Overlay hover — rgba(99,102,241,0.75) + botão pill "Ver produto"
                    💚 Preço especial — verde #4ade80, preço original riscado em cinza
                    🛒 Botão carrinho — gradiente índigo/violeta com glow no hover, scale no click

          ├── list.html.twig
          └── search.html.twig
     └── users 
          ├── register.html.twig
          ├── login.html.twig
          ├── email-verification.html.twig
          └── account-dashboard.html.twig
        





### Fontes

resources/fonts/
└── fontawesome/      # FontAwesome 6

### Estilos

Hero (avatar circular + breadcrumb + título)
│
├── Seção: Minha Conta  [ícone roxo]
│   ├── 📝 Alterar informações
│   ├── 🔒 Alterar senha
│   ├── 📍 Endereços
│   └── ❤️  Lista de desejos
│
├── Seção: Meus Pedidos  [ícone verde]
│   ├── ✅ Histórico de pedidos  ← card destacado (verde)
│   ├── ⬇️  Downloads
│   ├── ★  Pontos (condicional)
│   ├── 🔄  Devoluções
│   └── 💲  Transações
│
├── Seção: Newsletter  [ícone azul]
│
└── Seção: Afiliado  [ícone âmbar] ← só aparece se `affiliate` for true

## Highlights dos cards
🏷️ Badge "Oferta" — gradiente vermelho-laranja quando prod.special existe
🖼️ Overlay hover — rgba(99,102,241,0.75) + botão pill "Ver produto"
💚 Preço especial — verde #4ade80, preço original riscado em cinza
🛒 Botão carrinho — gradiente índigo/violeta com glow no hover, scale no click

### 🛒 cart.twig — Redesign Premium
Destaques visuais
🖼️ Imagem 90×90 com scale no hover e border-radius
🏷️ Chips de opções — pills pequenos com bordas sutis
🔄 Botão update — integrado ao input de qty com borda esquerda
🗑️ Botão remover — vermelho com scale no hover
🔒 Trust badges — "Compra segura" e "Dados protegidos" com ícone verde
🟢 Total final — destacado em verde com tipografia maior
⚡ JS de recálculo — preservado 100%, seletores atualizados para o novo markup

### 🏠 home.html.twig — Hero Premium
Destaques responsivos e de layout:
- **Ajustes de Grid e Padding**: Redução proporcional do padding interno do `.egen-hero-card` (de `5rem 3rem` para `2rem 1.25rem` no mobile).
- **Tipografia Escalonável**: O título `.egen-hero-title` diminui de `3rem` para `1.75rem` em dispositivos mobile, evitando quebras abruptas de palavras.
- **Ações Alinhadas**: Botões `.egen-btn-primary` e `.egen-btn-outline` passam a se comportar com `width: 100%` empilhados verticalmente em telas menores que 576px, mantendo-se lado a lado de forma flexível em telas maiores (corrigido conflito de largura total do botão primário em telas intermediárias).

### 🏷️ category/show.html.twig — Faceted Search & Navigation
Reestruturação estética e funcional:
- **Layout de Duas Colunas**: Grid `.egen-category-layout` que alinha a barra de filtros (`.egen-aside-filters` com `280px` de largura) à esquerda e o conteúdo principal de listagem à direita.
- **Visual Premium no Aside**: O arquivo `aside_filters.html.twig` foi reescrito adotando a convenção de classes da Alpha Engine (`egen-`), aplicando estilizações refinadas com desfoque de fundo (glassmorphism), inputs numéricos estilizados, e botões padronizados.
- **Responsividade Flexível**: Abaixo de 768px, a barra de filtros passa a se posicionar no topo de forma empilhada para maximizar o espaço dos cards de produtos na tela mobile.
- **Busca de Marcas por Rolagem Interna com Lupa**: Adição de uma caixa de pesquisa dinâmica que oculta/mostra marcas instantaneamente por meio de JS, operando dentro de um contêiner de rolagem interna com altura máxima (`200px`) e scrollbar estilizado.