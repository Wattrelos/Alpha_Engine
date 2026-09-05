# DP-4: Relatório do andamento do projeto Alpha em 2026-06-05

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-20 12:35:02
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/4

## Descrição

# Relatório do andamento do projeto Alpha em 2026-06-05

Aqui está um resumo consolidado do andamento e da estrutura atual do projeto Alpha Engine:

### 🏛️ 1. Arquitetura e Decisões de Design

-   **Abandono do Legado:** O runtime antigo foi completamente descartado. A **Alpha Engine** opera como um sistema standalone e moderno baseado em Slim Framework, Twig (Atomic Design), e PHP 8.4.

-   **Padrões Utilizados:** Implementa DDD (Domain-Driven Design), *Repository Pattern*, *Data Mappers*, *Identity Map* no DAO para evitar consultas redundantes (N+1 queries), e *Lazy Loading* via proxies.

-   **Banco de Dados & Integridade:** Foram sanados problemas como o "Pseudo-Null" (onde FKs recebiam 0 ao invés de NULL) e o acúmulo de dados na sessão (travamento por sessões obesas). A loja principal foi migrada de um ID 0 virtual para um registro físico real store_id = 1 na tabela tbkk_store.

### 🛒 2. Subsistema de Carrinho e Checkout (Recém-Refatorado)

-   **Desacoplamento Completo:** A biblioteca clássica de carrinho foi substituída por CartRepository e CartMapper dedicados.

-   **Estilização Premium:** A interface do checkout e do carrinho foi refeita no padrão BEM/CSS (com foco em glassmorphic e visual dark) sem Bootstrap.

-   **ViaCEP Integrado:** A API de CEP foi integrada para preenchimento automático de endereços e estados (sem a necessidade de drop-downs complexos de países, mantendo foco estrito no Brasil).

-   **Sessão e Autenticação:** Sincronização automática entre o carrinho local (localStorage de visitantes) e a conta no banco de dados após o login.

### 🔐 3. Autenticação e Cadastro (Auth)

-   **Segurança Robustecida:** O sistema conta com proteção contra Brute Force (bloqueio após 5 tentativas mal-sucedidas) para clientes e administradores, persistência híbrida e fallback para $_SESSION caso o Redis esteja inacessível.

### 📍 4. Ponto Atual de Pausa e Próximos Passos

Conforme a última nota de fim de expediente:

1.  **Vitrine de Categorias:** Já está ativa e listando produtos com URLs amigáveis (/{lang}/categoria/{slug}).

2.  **Próximo Passo:** Testar e consolidar a abertura da página individual de produtos (ShowProductAction.php).

3.  **Carrinho e Checkout:** Validar se o CartRepository está completamente livre de dependências nulas remanescentes de variáveis globais legadas e prosseguir com o fluxo de checkout.

Estou pronto para ajudar no desenvolvimento de qualquer uma dessas etapas. Como deseja proceder?

