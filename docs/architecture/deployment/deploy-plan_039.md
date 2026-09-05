# DP-39: Fase 4 (Otimização Arquitetural e Modularização CSS)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-09 21:12:36
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/39

## Descrição

# Plano de Implementação — Fase 4 (Otimização Arquitetural e Modularização CSS)

Este plano descreve o processo de modularização dos estilos consolidados no e-commerce, introdução de variáveis CSS globais (`:root`) para temas dinâmicos e carregamento sob demanda dos arquivos CSS específicos nos templates Twig.

---

## 🎯 Objetivos

1. **Variáveis CSS**: Adicionar variáveis de tema (Premium UI) no `:root` do arquivo `personalizada.css` para centralizar cores, fontes e bordas.
2. **Modularização**: Extrair os estilos consolidados anteriormente de `personalizada.css` e dividi-los em três arquivos sob demanda:
   * 📄 `addresses.css` (para as páginas de Endereço)
   * 📄 `orders.css` (para as páginas de Pedidos)
   * 📄 `returns-institutional.css` (para Devoluções e Institucional)
3. **Limpeza**: Remover do arquivo `personalizada.css` os mais de 1.600 blocos de linhas de CSS específicos adicionados nas Fases 1, 2 e 3.
4. **Carregamento sob Demanda**: Adaptar o arquivo base de layout `base.html.twig` para oferecer suporte a blocos Twig de folhas de estilo e injetar os respectivos arquivos nos templates de destino.

---

## 🛠️ Alterações Propostas

### 1. Extensão de Layout

#### [MODIFY] [base.html.twig](file:///var/www/html/agsonhos/resources/views/base.html.twig)
* Adicionar `{% block stylesheets %}{% endblock %}` após a inclusão de `new-stylesheet.css`.

### 2. Estilos CSS (Global e Módulos)

#### [MODIFY] [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)
* Atualizar o bloco `:root` no topo do arquivo para incluir todas as variáveis semânticas do tema Premium UI.
* Remover todos os estilos específicos adicionados a partir do comentário `/* ADDRESSES AREA ... */` até o fim do arquivo (linhas 1440 a 3041).

#### [NEW] [addresses.css](file:///var/www/html/agsonhos/public_html/css/custom/addresses.css)
* Criar a folha de estilos contendo as regras unificadas de Endereços, utilizando as variáveis CSS declaradas no `:root`.

#### [NEW] [orders.css](file:///var/www/html/agsonhos/public_html/css/custom/orders.css)
* Criar a folha de estilos contendo as regras unificadas de Pedidos e Histórico, utilizando as variáveis CSS declaradas no `:root`.

#### [NEW] [returns-institutional.css](file:///var/www/html/agsonhos/public_html/css/custom/returns-institutional.css)
* Criar a folha de estilos contendo as regras unificadas de Devoluções, Contato e Institucional, utilizando as variáveis CSS declaradas no `:root`.

### 3. Templates Twig (Carregamento dos Módulos)

#### [MODIFY] [create.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/create.twig)
* Adicionar o bloco de estilos carregando `addresses.css`.

#### [MODIFY] [edit.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/edit.twig)
* Adicionar o bloco de estilos carregando `addresses.css`.

#### [MODIFY] [index.twig](file:///var/www/html/agsonhos/resources/views/pages/users/addresses/index.twig)
* Adicionar o bloco de estilos carregando `addresses.css`.

#### [MODIFY] [orders.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/orders.twig)
* Adicionar o bloco de estilos carregando `orders.css`.

#### [MODIFY] [order-history.twig](file:///var/www/html/agsonhos/resources/views/pages/users/accounts/order-history.twig)
* Adicionar o bloco de estilos carregando `orders.css`.

#### [MODIFY] [return.twig](file:///var/www/html/agsonhos/resources/views/pages/users/return.twig)
* Adicionar o bloco de estilos carregando `returns-institutional.css`.

#### [MODIFY] [return-info.html.twig](file:///var/www/html/agsonhos/resources/views/pages/users/return-info.html.twig)
* Adicionar o bloco de estilos carregando `returns-institutional.css`.

#### [MODIFY] [product-returns.html.twig](file:///var/www/html/agsonhos/resources/views/pages/product/product-returns.html.twig)
* Adicionar o bloco de estilos carregando `returns-institutional.css`.

#### [MODIFY] [contact.twig](file:///var/www/html/agsonhos/resources/views/pages/information/contact.twig)
* Adicionar o bloco de estilos carregando `returns-institutional.css`.

#### [MODIFY] [show.html.twig](file:///var/www/html/agsonhos/resources/views/pages/information/show.html.twig)
* Adicionar o bloco de estilos carregando `returns-institutional.css`.

---

## 📋 Definição das Variáveis CSS (:root)
```css
  /* Premium UI Tokens */
  --premium-bg-page: #0d0f14;
  --premium-bg-card: rgba(255, 255, 255, 0.03);
  --premium-bg-card-dark: rgba(255, 255, 255, 0.02);
  --premium-bg-card-hover: rgba(255, 255, 255, 0.05);
  --premium-bg-card-default: rgba(99, 102, 241, 0.05);
  --premium-bg-header: rgba(99, 102, 241, 0.05);
  --premium-bg-primary-light: rgba(99, 102, 241, 0.12);
  --premium-text-primary: #f1f5f9;
  --premium-text-secondary: #cbd5e1;
  --premium-text-muted: #94a3b8;
  --premium-color-primary: #6366f1;
  --premium-color-primary-hover: #4f46e5;
  --premium-color-secondary: #8b5cf6;
  --premium-border-card: rgba(255, 255, 255, 0.08);
  --premium-border-card-inner: rgba(255, 255, 255, 0.06);
  --premium-border-glow: rgba(99, 102, 241, 0.2);
  --premium-border-glow-hover: rgba(99, 102, 241, 0.5);
  --premium-shadow-color: rgba(99, 102, 241, 0.3);
  --premium-font-family: 'Inter', 'Outfit', system-ui, sans-serif;
```

---

## 🧪 Plano de Verificação

### Verificação de Rede (DevTools)
1. Abrir a página de Endereço e verificar no painel Network do navegador se o arquivo `addresses.css` é baixado corretamente.
2. Confirmar se `orders.css` **não** é baixado nesta página (provando o carregamento sob demanda).
3. Confirmar se os estilos continuam renderizando com as variáveis dinâmicas aplicadas.
4. Repetir o teste para as páginas de Pedidos, Devoluções e Fale Conosco.

# Tarefas — Fase 4 (Otimização Arquitetural e Modularização CSS)

- `[x]` Estender o layout base em [base.html.twig](file:///var/www/html/agsonhos/resources/views/base.html.twig)
- `[x]` Inserir as variáveis CSS de tema no [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)
- `[x]` Criar o arquivo [addresses.css](file:///var/www/html/agsonhos/public_html/css/custom/addresses.css) com variáveis
- `[x]` Criar o arquivo [orders.css](file:///var/www/html/agsonhos/public_html/css/custom/orders.css) com variáveis
- `[x]` Criar o arquivo [returns-institutional.css](file:///var/www/html/agsonhos/public_html/css/custom/returns-institutional.css) com variáveis
- `[x]` Remover os blocos específicos de [personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css) (linhas 1440+)
- `[x]` Incluir as folhas de estilo sob demanda nos templates Twig
- `[x]` Validar layout final e carregamento sob demanda

# Walkthrough — Otimização Arquitetural e Modularização CSS (Fase 1, 2, 3 e 4)

Concluímos com sucesso a execução de todas as fases de consolidação e otimização dos estilos da Premium UI do e-commerce.

---

## 🛠️ Fase 4: Otimização Arquitetural e Modularização CSS

Na Fase 4, implementamos as 4 melhorias recomendadas para aprimorar o desempenho, carregamento e manutenção do design do site.

### 1. Variáveis CSS (Tema Dinâmico)
* **[personalizada.css](file:///var/www/html/agsonhos/public_html/css/custom/personalizada.css)**:
  * Inserimos no bloco `:root` todas as variáveis semânticas de cores, bordas, sombras e fontes da Premium UI (`--premium-bg-page`, `--premium-bg-card`, `--premium-text-primary`, `--premium-color-primary`, `--premium-border-card`, etc.).
  * Com isso, o tema visual do e-commerce pode ser alterado dinamicamente modificando apenas estes tokens no arquivo global.

### 2. Criação de Folhas de Estilo sob Demanda (Módulos)
Extraímos os estilos consolidados anteriormente das Fases 1, 2 e 3 de `personalizada.css` e os dividimos em arquivos modulares menores:
* 📄 **[addresses.css](file:///var/www/html/agsonhos/public_html/css/custom/addresses.css)**: Estilos da área de endereços reescritos usando variáveis CSS.
* 📄 **[orders.css](file:///var/www/html/agsonhos/public_html/css/custom/orders.css)**: Estilos da listagem e detalhes de pedidos reescritos usando variáveis CSS.
* 📄 **[returns-institutional.css](file:///var/www/html/agsonhos/public_html/css/custom/returns-institutional.css)**: Estilos unificados de formulário de devolução, timeline, lista de devoluções, página de contato e páginas institucionais reescritos usando variáveis CSS.

### 3. Limpeza Geral de `personalizada.css`
* Removemos mais de **1.600 linhas** de código de estilos específicos agregados nas fases passadas, fazendo o arquivo global `personalizada.css` retornar ao seu propósito inicial (estilos de cabeçalho, rodapé, menu e utilitários globais).

### 4. Carregamento sob Demanda via Twig
* **[base.html.twig](file:///var/www/html/agsonhos/resources/views/base.html.twig)**:
  * Adicionamos a extensão de blocos `{% block stylesheets %}{% endblock %}` na tag `<head>` para permitir a injeção condicional de folhas de estilo específicas de cada página.
* **Atualização dos Templates Twig**:
  * Adicionamos a injeção do arquivo respectivo através de seu bloco em 10 templates customizados:
    1. **Endereços**: `create.twig`, `edit.twig`, `index.twig` (carregam `addresses.css`).
    2. **Pedidos**: `orders.twig`, `order-history.twig` (carregam `orders.css`).
    3. **Devoluções e Institucionais**: `return.twig`, `return-info.html.twig`, `product-returns.html.twig`, `contact.twig`, `show.html.twig` (carregam `returns-institutional.css`).

---

## 🔬 Resultados Obtidos

* **Carregamento Mais Rápido (Performance)**: As páginas que não fazem parte do fluxo de endereços ou pedidos agora não fazem o download dessas folhas de estilo, diminuindo o tempo de renderização da página (First Contentful Paint) e poupando banda.
* **Cache Inteligente**: O navegador faz o cache individual do arquivo de endereço ou pedido apenas quando o cliente acessa essas áreas.
* **Código Limpo**: As tags `<style>` foram totalmente banidas dos templates Twig e o CSS global está livre de regras redundantes e específicas.
* **Fácil Customização**: Todo o tema do e-commerce agora pode ser customizado a partir do bloco `:root` em `personalizada.css`.

