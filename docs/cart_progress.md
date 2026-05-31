# Refatoração do Carrinho de Compras - Alpha Engine

Este documento registra o avanço na reestruturação arquitetural do módulo de carrinho de compras, consolidando a inteligência de negócios fora das bibliotecas legadas e reduzindo gargalos de banco de dados.

## O que foi implementado nesta rodada:

1. **A Ponte Legada (`cart.php`)**
   - Transformação da pesada biblioteca original do OpenCart em um *Proxy* leve que atua apenas para manter a retrocompatibilidade com extensões antigas, delegando todo o processamento para o `CartRepository`.

2. **Persistência Isolada (`CartMapper.php`)**
   - Todo o SQL do carrinho foi extraído e movido para métodos unitários no Mapper. Funcionalidades complexas, como limpeza de carrinhos abandonados e mesclagem de itens de visitantes após o login, agora ocorrem de forma explícita e controlada.

3. **Orquestração de Regras (`CartRepository.php`)**
   - Fim dos loops com queries SQL: a hidratação de produtos agora consome o `ProductMapper`.
   - Isolamento das regras de prioridade de descontos (progressivos e promoções limitadas).
   - Processamento iterativo de opções do carrinho (`checkbox`, `select`, etc.), aplicando seus devidos prefixos de valor, peso e pontuação de forma performática.
   - Integração das lógicas físicas e fiscais através da injeção do `WeightClassRepository` (para `getWeight`) e da biblioteca global de impostos (para `getTaxes` e `getTotal`).

4. **Controlador da API (`api/cart.php`)**
   - Completamente desacoplado do carrinho legado, passando a consumir as métricas consolidadas pelo `CartRepository`.
   - Reparo de contexto: A API agora mapeia o Idioma, Loja e Grupo de Clientes do usuário, forçando o `ProductMapper` a processar dados regionalizados e aplicar os preços corretos na hora de formatar o JSON de resposta do Checkout.

5. **Integração Nativa de Sessão (`Session.php` e `CartRepository.php`)**
   - Implementação do método `getId()` na classe de suporte a sessões `Session` para retornar o identificador único da sessão ativa via `session_id()`. Isso garante que o `CartRepository` consulte e gerencie o carrinho de compras de forma consistente e desacoplada.

6. **Suporte de Pesos Autônomo (`Weight.php`)**
   - Criação da classe helper `Alpha\Support\Weight` e registro no Registry global para a conversão e formatação moderna de pesos de produtos. Isso resolveu o erro de membro nulo ao acessar `$this->weight->format` durante a exibição do carrinho.

7. **Rotas de Adição Modernizadas (`cart_add`)**
   - Substituição total da rota antiga `checkout/cart.add` pelo padrão amigável internacionalizado `/{lang}/carrinho/adicionar` nos repositórios `ProductRepository` e `WishlistRepository`. Isso alinha a submissão dos botões de compra das páginas de categoria e vitrines ao processamento moderno da Alpha Engine.

8. **Rota de Checkout Ativa (`/checkout`)**
   - Implementação da ação `Alpha\Controller\Actions\Cart\Checkout` com a assinatura `__invoke` e injeção do `CountryRepository` para prover a lista de países nos formulários.
   - Vinculação da rota no Slim em `public_html/index.php` e registro do `Registry` no container DI.

9. **Estilização e Dinamismo do Checkout**
   - Adicionada estilização responsiva e premium (glassmorphism) para as etapas de checkout em `new-stylesheet.css`.
   - Adicionado script reativo em `checkout.js` para alternar a visibilidade dos blocos de formulário (Login, Registro, Visitante, Endereço de Entrega diferente) de forma dinâmica no lado do cliente.

10. **Login Integrado e Sincronização de Itens**
    - Implementada a submissão assíncrona do formulário de login na etapa de identificação do checkout.
    - Sincronização automática dos itens locais do visitante do `localStorage` com a conta no banco de dados imediatamente após o login bem-sucedido, redirecionando o cliente de volta para o checkout com seu perfil e endereços carregados.

11. **Autocomplemento de CEP e Novos Campos de Endereço**
    - Campos de endereço reordenados no `checkout.twig` posicionando o campo CEP antes do endereço.
    - Adicionado suporte e inputs para os campos customizados `number` (Número) e `neighborhood` (Bairro) em conformidade com o novo schema de dados.
    - Integração de preenchimento automático em Javascript nativo (`checkout.js`) consultando a API do ViaCEP e carregando dinamicamente os estados via endpoint `/api/paises/{country_id}/estados`.
    - Otimização do mapeamento de estados: a seleção do estado na resposta do ViaCEP passa a cruzar a UF (`data.uf`) diretamente com o código de estado do banco de dados (`code`) via atributo `data-code` das opções, dispensando mapeamentos rígidos em JS.

12. **Correção de Legibilidade e Contraste nos Formulários**
    - Corrigido o bug visual de "texto branco em fundo branco" nas opções (`option`) dos elementos `<select>` no checkout (`payment_zone_id` e `shipping_zone_id`), garantindo uma cor de fundo escura e texto claro para perfeita visibilidade.
    - Corrigido o background de `.egen-form-input` de amarelo (`var(--egen-accent-color)`) para o fundo semitransparente correto (`var(--egen-bg-input)`).

13. **Sincronização Global do Contador de Produtos do Mini-Cart**
    - Adicionado suporte no `LanguageMiddleware.php` para prover a contagem física de produtos através da global do Twig `cart_count`.
    - Atualizados os badges do cabeçalho no `top-nav.twig` e `utility-bar.twig` para renderizarem os valores dinâmicos de backend no carregamento inicial da página.
    - Ajustado o script `cart.js` para atualizar sincronizadamente todas as referências visuais de contadores de carrinho na página em tempo real para visitantes.

14. **Recálculo Dinâmico de Totais no Cliente**
    - Adicionados os atributos de preços puros e flutuantes (`price_raw` e `total_raw`) na hidratação de produtos do `CartRepository.php` e injetados nas marcações HTML do `cart.twig`.
    - Implementado script reativo em JavaScript no `cart.twig` para recalcular instantaneamente os totais por produto, subtotal e total geral del pedido na barra lateral assim que as quantidades forem modificadas.

15. **Mecanismo de Fallback de Totais Nativos**
    - Implementada uma detecção no `CartRepository.php` que intercepta quando os modelos de totalização legados estão ausentes no disco.
    - Se a totalização padrão falhar ou retornar vazia, o repositório gera dinamicamente os totais (`Sub-Total` e `Total`) a partir dos dados e métodos locais da própria Alpha Engine, garantindo a exibição do resumo do pedido de forma precisa para usuários autenticados.

16. **Submissão de Checkout e Página de Sucesso**
    - Mapeado o método POST para `/checkout` no `index.php` para armazenar endereços da requisição na sessão e chamar o processamento de pedidos do `OrderRepository` via `createFromSession()`.
    - Criada a rota GET `/checkout/sucesso` no `index.php` e o template [success.twig](file:///var/www/html/agsonhos/resources/views/pages/cart/success.twig) para mostrar a confirmação da compra e o número identificador do pedido ao usuário após esvaziar o carrinho.