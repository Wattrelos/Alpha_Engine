# DP-85: Arquivos importantes para a raiz do site

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-18 19:37:02
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/85

## Descrição

### 📂 Arquivos Criados e Suas Funções

1. **[`robots.txt`](file:///var/www/html/agsonhos/public_html/robots.txt)**
   * **Objetivo:** Orienta indexadores (Googlebot, Bingbot, etc.) permitindo o catálogo e bloqueando endpoints privados (`/admin`, `/setup`, `/api/`, `/*/account`, `/*/carrinho`, `/*/checkout`, buscas dinâmicas).
   * **Conexão:** Aponta formalmente a URL canônica para o `sitemap.xml`.

2. **[`sitemap.xml`](file:///var/www/html/agsonhos/public_html/sitemap.xml)**
   * **Objetivo:** Estrutura XML padrão (com suporte a `xhtml:link` multi-idioma pt-br/en/es) para indexação de homepages, páginas institucionais, mapa do site e categorias principais.

3. **[`/.well-known/security.txt`](file:///var/www/html/agsonhos/public_html/.well-known/security.txt)** *(e fallback [`/security.txt`](file:///var/www/html/agsonhos/public_html/security.txt))*
   * **Objetivo:** Conformidade estrita com o padrão internacional **RFC 9116** para reporte responsável de vulnerabilidades por pesquisadores de segurança.

4. **[`llms.txt`](file:///var/www/html/agsonhos/public_html/llms.txt)** & **[`llms-full.txt`](file:///var/www/html/agsonhos/public_html/llms-full.txt)**
   * **Objetivo:** Padrão [llmstxt.org](https://llmstxt.org/) formatado em Markdown para mecanismos de busca generativa e agentes de IA (ChatGPT Search, Perplexity, Claude, Gemini), resumindo o propósito da loja, catálogo de colchões, móveis e materiais de construção, frete e políticas institucionais.

---

### 💡 Sugestões Extras Implementadas

* **[`humans.txt`](file:///var/www/html/agsonhos/public_html/humans.txt)**: Créditos padronizados da equipe de desenvolvimento (Josias Sobrinho, Gabriel Calidônio, Wallace Miranda), referências à FATEC e stack tecnológica (PHP 8.2, Slim 4, Twig, GoF).
* **[`manifest.webmanifest`](file:///var/www/html/agsonhos/public_html/manifest.webmanifest)**: Manifesto PWA (*Progressive Web App*) para suporte a instalação mobile em celulares (ícones, cor de tema e nome da aplicação).

---

### 🚀 Próximos Passos Recomendados

* **No template Twig principal (`header.twig`):** Incluir a tag para vincular o manifesto:
  ```html
  <link rel="manifest" href="/manifest.webmanifest">
  ```
* **Gerador Dinâmico de Sitemap (Futuro):** Criar uma CLI ou Action agendada via Cron (`/bin/console sitemap:generate`) para atualizar automaticamente o `sitemap.xml` conforme novos produtos e categorias forem cadastrados no banco de dados.

