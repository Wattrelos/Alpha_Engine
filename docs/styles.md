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

---

## 🛡️ Painel Administrativo (Admin Panel) — Modernização Visual e Boas Práticas

Os estilos visuais da área administrativa do painel da Alpha Engine foram reestruturados para remover acoplamentos de estilos inline e centralizar as regras de estilo em folhas externas organizadas sob `public_html/css/admin/` (`admin.css`, `variables.css`, `layout.css` e `components.css`).

### Destaques do Design System Administrativo:

- **Tons Suaves e Sobriedade (Slate Palette)**:
  - O fundo da página (`--color-bg-body`) utiliza um tom de cinza azulado muito claro e repousante `#f8fafc` (Slate 50).
  - O fundo da sidebar (`--color-bg-sidebar`) adota um azul-escuro profundo e elegante `#0f172a` (Slate 900).
  - Inputs de formulário (`.form-control`) possuem fundo suave e de baixo contraste (`#f8fafc`). Ao focar, o fundo transita suavemente para branco puro (`#ffffff`), com uma borda azul e um anel de brilho externo (`rgba(59, 130, 246, 0.15)`), destacando o elemento ativo de forma premium.

- **Sombras de Camada (Layered Shadows)**:
  - Introdução de sombras em variáveis (`--shadow-card`, `--shadow-topbar`, `--shadow-md`) que criam relevo tridimensional sem poluição visual.
  - Efeito de **Elevação nos Cards** no hover: ao passar o mouse em um card (`.card`, `.stat-card` ou `.card-large`), ele realiza uma translação vertical suave de `-2px` (`translateY`) e projeta uma sombra mais difusa e espalhada (`--shadow-card-hover`), proporcionando interatividade.

- **Menu Lateral e Navegação**:
  - Item ativo na sidebar (`.sidebar li a.active`): Fundo azul muito suave (`rgba(59, 130, 246, 0.08)`) e uma barra lateral vertical arredondada no canto esquerdo (`::before`), criando um indicador de navegação discreto.

- **Espaçamento e Cantos Arredondados**:
  - Arredondamento principal (`--border-radius`) aumentado para `12px` (cards e painéis), suavizando a interface em comparação com o design quadrado anterior.
  - Inputs e botões padronizados com arredondamento de `8px`.
  - Margens e paddings expandidos (ex: padding do card em `1.75rem`), dando mais respiro e melhorando a escaneabilidade dos relatórios.

- **Micro-interações e Animações**:
  - **Stat Cards**: Zoom dinâmico (`scale(1.1)`) no ícone decorativo (`.stat-icon-box`) que é ativado automaticamente ao passar o mouse em qualquer parte do card estatístico.
  - **Botões**: Animação de feedback ao clique (`scale(0.97)` active state) em todos os botões principais, acompanhada de expansão de sombra no hover.

