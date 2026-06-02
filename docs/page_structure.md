## 📦 Estrutura das páginas Twig


resources/views/
├── admin/                                <-- Toda a interface do painel administrativo
│    ├── auth/                            <-- Pasta focada no fluxo de acesso
│    │   ├── login.html.twig              <-- A tela de login do painel
│    │   ├── forgot_password.html.twig    <-- Tela de "Esqueci minha senha"
│    │   └── reset_password.html.twig     <-- Tela de redefinição de senha
│    ├── layouts/
│    │   └── components/                  <-- Componentes exclusivos do admin
│    │   │   ├── atoms/
│    │   │   ├── molecules/
│    │   │   └── organisms/
│    │   └── layouts/
│    │       ├── base_admin.html.twig         <-- Layout com o Dashboard completo (Sidebar, Topbar)
│    │       └── base_auth.html.twig          <-- Novo layout limpo (apenas a caixa centralizada)
│    └── pages/ 
│              ├── dashboard/               # Métricas e visão geral do e-commerce
│              ├── catalog/                 # Gestão de tudo que é vendido
│              │   ├── products/            # Produtos
│              ├── categories/          # Categorias
│              │   │   ├── list.html.twig       <-- Listagem/Tabela de categorias
│              │   │   ├── create.html.twig     <-- Formulário de nova categoria
│              │   │   └── edit.html.twig       <-- Formulário de edição
│              │   └── brands/              # Marcas / Fabricantes
│              ├── sales/                   # Gestão do dinheiro e pedidos
│              │   ├── orders/              # Pedidos feitos
│              │   ├── transactions/        # Status de pagamentos (Gateway)
│              │   └── vouchers/            # Cupons de desconto
│              ├── customers/               # Gestão de usuários compradores
│              ├── inventory/               # Controle físico de estoque e fornecedores
│              ├── configurations/          # Configurações do sistema
│              └── users/                   # Administradores do painel (gerentes, estoquistas)
│ 
│ 
├── components/
│   ├── atoms/        # Componentes atômicos (botões, inputs, cards)
│   ├── molecules/    # Componentes moleculares (footer-column, navbar-item, product-card)
│   └── organisms/    # Componentes orgânicos (header, footer, sidebar, full-product-card)
├── layouts/
│   └── base.html.twig  # Template base principal da aplicação
└── pages/            # Estrutura de páginas do site
     ├── cart/
     │    ├── cart.twig             # Carrinho de compras principal
     │    ├── checkout.twig         # Fluxo de checkout unificado
     │    ├── payment_method.twig   # Métodos de pagamento do checkout
     │    ├── shipping-address.twig # Endereço de entrega do checkout
     │    └── success.twig          # Tela de sucesso pós-checkout
     ├── category/
     │    └── show.html.twig        # Listagem/Filtro de categoria (Aside de filtros, lupas de marcas)
     ├── errors/
     │    └── 404.html.twig         # Página de erro 404
     ├── information/
     │    ├── contact.twig          # Formulário de contato
     │    ├── show.html.twig        # Páginas institucionais (Termos, etc.)
     │    └── sitemap.twig          # Mapa do site
     ├── product/
     │    ├── search.html.twig      # Resultados da busca de produtos
     │    └── show.html.twig        # Detalhe do produto (Preços, opções, botão comprar)
     └── users/
          ├── login.twig            # Login do cliente
          ├── register.twig         # Registro de novo cliente
          ├── edit.html.twig        # Edição de dados do cliente
          ├── index.html.twig       # Página geral de conta
          ├── return.twig           # Solicitações de devoluções
          ├── accounts/             # Subtelas do painel do cliente
          │    ├── account.twig       # Detalhes da conta principal
          │    ├── newsletter.twig    # Configuração de Newsletter
          │    ├── order-history.twig # Histórico detalhado de um pedido
          │    ├── orders.twig        # Listagem de pedidos anteriores
          │    └── wishlist.twig      # Lista de desejos
          └── addresses/            # Gerenciamento de endereços do cliente
               ├── create.twig        # Novo endereço
               ├── edit.twig          # Edição de endereço existente
               └── index.twig         # Listagem de endereços

### Assets
public_html/
          ├── css/                         # CSS Compilado 
          ├── js/                          # JS Compilado
          └── fonts/                       # Fontes
                └── fontawesome/           # FontAwesome 6 




### Fontes



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