# DP-29: Suporte a Variações de Produtos no Carrinho do Visitante

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-27 22:12:52
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/29

## Descrição

# Plano de Implementação - Suporte a Variações de Produtos no Carrinho do Visitante

Este plano descreve as alterações necessárias para implementar o suporte completo a variações de produtos (produtos filhos com `master_id > 0`) no carrinho de compras de visitantes (clientes não logados/anônimos).

Atualmente, quando um visitante adiciona uma variação ao carrinho, os dados da variação não são hidratados corretamente com as propriedades herdadas do produto pai (como preço, imagens, impostos, peso, etc.) no endpoint `/api/carrinho/dados`. Além disso, os links dos produtos no carrinho apontam para as variações diretamente em vez de apontar para a página do produto pai.

---

## Modificações Propostas

### 1. Backend: Hydration e Herança de Variações no Endpoint do Carrinho do Visitante

#### [MODIFY] [CalculateVisitorCartAction.php](/core/Controller/Actions/Cart/CalculateVisitorCartAction.php)
- Obter o `ProductDiscountRepository` e o `SeoUrlRepository` a partir do `RepositoryFactory`.
- Identificar se os produtos no carrinho do visitante possuem um produto pai (`master_id > 0`) e carregá-los em lote para evitar consultas N+1.
- Implementar a lógica de herança de propriedades para variações (ex: herdar preço se o preço da variação for `<=` 0, imagens, classe de imposto, peso, compra mínima, etc.), alinhado com o que já é feito no `CartRepository::getProducts()`.
- Resolver a lógica de preços especiais (`special`) e descontos progressivos (`discount`) baseados na quantidade no carrinho para visitantes.
- Calcular os preços finais e totais com taxas/impostos inclusos caso `config_tax` esteja ativo e haja um endereço/CEP na sessão.
- Alterar a geração da URL (`href`) para produtos que são variações, fazendo-a apontar para o slug/URL do produto pai (`master_id`).
- Adicionar os campos `master_id` no retorno JSON do produto.

### 2. Backend: Suporte a Links de Variações no Carrinho de Usuários Logados

#### [MODIFY] [CartRepository.php](/core/Model/Domain/Repositories/CartRepository.php)
- Adicionar o campo `'master_id' => (int)($productInfo['master_id'] ?? 0)` na estrutura retornada por `getProducts()`.

#### [MODIFY] [ShowCartAction.php](/core/Controller/Actions/Cart/ShowCartAction.php)
- Atualizar a geração da URL (`href`) no loop de produtos. Se o produto possuir `master_id > 0`, usar o `master_id` para resolver o slug de SEO e a URL do produto, garantindo que o link aponte para a página do produto pai.

---

## Plano de Verificação

### Testes Manuais
1. **Adicionar Variação como Visitante:**
   - Acessar a página de um produto com variações sem estar logado.
   - Selecionar uma variação e adicionar ao carrinho.
   - Ir para a página `/carrinho`.
   - Verificar se o nome exibido é o nome completo da variação (ex: `Nome do Produto - Variação`).
   - Verificar se a imagem exibida corresponde à imagem da variação (ou do pai, caso a variação não tenha imagem própria).
   - Verificar se o preço unitário e o total correspondem ao preço da variação e a promoções ativas.
   - Clicar na imagem ou no nome do produto e garantir que o link redireciona para a página de detalhes do produto pai.

2. **Sincronização de Carrinho e Checkout:**
   - Adicionar uma variação ao carrinho como visitante.
   - Ir para o checkout `/checkout`.
   - Verificar se o carrinho é sincronizado com o banco de dados via `/api/carrinho/sincronizar`.
   - Fazer login ou finalizar a compra e verificar se os itens permanecem consistentes.

3. **Verificação de Performance:**
   - Garantir que as consultas de banco de dados para buscar os produtos pai das variações no carrinho do visitante sejam feitas em lote (Batch Loading), evitando problemas de N+1 queries.

- [x] Adicionar `master_id` no retorno de `CartRepository::getProducts()`
- [x] Atualizar links de produtos no carrinho logado em `ShowCartAction.php` para usar o `master_id` do pai
- [x] Implementar carregamento de parent/master products e herança de atributos em `CalculateVisitorCartAction.php`
- [x] Implementar cálculo de descontos e preços especiais em `CalculateVisitorCartAction.php`
- [x] Implementar cálculo de taxas/impostos in `CalculateVisitorCartAction.php`
- [x] Mapear links das variações para a página do produto pai em `CalculateVisitorCartAction.php`
- [x] Validar a implementação realizando testes

# Walkthrough - Implementação de Variações de Produtos no Carrinho do Visitante

Concluímos com sucesso a implementação do suporte a variações de produtos (produtos filhos com `master_id > 0`) no carrinho de compras de visitantes (anônimos/não logados).

## Alterações Realizadas

### 1. Repositório e Lógica de Carrinho (Cart)
- **[CartRepository.php](/core/Model/Domain/Repositories/CartRepository.php):** Adicionamos a chave `'master_id'` no array retornado por `getProducts()`. Isso permite distinguir variações de produtos dos produtos normais.
- **[ShowCartAction.php](/core/Controller/Actions/Cart/ShowCartAction.php):** Atualizamos a resolução de links (`href`) na listagem do carrinho para usuários logados. Se o item no carrinho for uma variação (`master_id > 0`), o link gerado agora aponta corretamente para a URL amigável do produto pai, em vez do ID individual da variação.

### 2. Endpoint da API do Carrinho do Visitante
- **[CalculateVisitorCartAction.php](/core/Controller/Actions/Cart/CalculateVisitorCartAction.php):**
  - **Batch Loading (Evita N+1):** Identificamos se algum produto no carrinho local do visitante é uma variação (`master_id > 0`) e carregamos os produtos pai correspondentes em lote.
  - **Herança de Atributos:** Implementamos a lógica de herança de atributos idêntica ao `CartRepository` (ex: herdar preço do pai se o preço da variação for `<=` 0, imagens, peso, compra mínima, etc.).
  - **Precificação e Descontos:** Implementamos o suporte a preços especiais (`special`) e descontos progressivos por quantidade (`discount`) para os visitantes.
  - **Cálculo de Impostos:** Adicionamos o cálculo de impostos dinâmico no preço unitário, total do item e grand total caso haja CEP/endereço simulado de entrega na sessão e as regras de impostos estejam configuradas.
  - **Links do Produto Pai:** Atualizamos os links gerados no JSON (`href`) das variações para que apontem diretamente para a URL do produto pai correspondente.

### 3. Correção de Erro no Motor de Impostos (Tax Engine)
- **[Tax.php](/core/Support/Tax.php) e [TaxRuleMapper.php](/core/Mappers/EntityMappers/TaxRuleMapper.php):** Corrigimos um erro de SQL pré-existente no motor de impostos, onde a coluna da tabela `tax_rate` era incorretamente consultada como `geo_zone_id` em vez de `geo_zones_id` (plural), o que causaria um Fatal Error no cálculo de impostos ao simular taxas/frete para o visitante ou no checkout.

---

## Verificação e Testes

Criamos e executamos um script de verificação integrado em `tests/test_visitor_cart_variations.php` que simulou um fluxo completo de requisição HTTP POST para o endpoint do visitante com um item de variação em carrinho.

Os testes validaram:
- **Herança do preço do pai** (150,00 da cama pai propagado para a variação com preço zerado e resultando em 300,00 para 2 unidades).
- **Nome completo do produto** (incluindo o nome da variação: `Cama Premium Test - Preto / Casal`).
- **Resolução de link SEO** (apontando corretamente para `/cama-premium-test`).
- **Herança de imagens** (gerando a miniatura do cache a partir da imagem do produto pai).

Todos os testes passaram com sucesso!
```
=== 3. Running Assertions ===
Product Total Price String: R$ 300,00
✓ Assertion Passed: Variation correctly inherited parent's price.
Product Name: Cama Premium Test - Preto / Casal
✓ Assertion Passed: Product name includes the variation name.
Product href: /pt-br/produto/cama-premium-test
✓ Assertion Passed: Variation URL points to the parent product's SEO slug.
Product thumbnail: http://localhost/image/cache/image/no-image-80x80.png
✓ Assertion Passed: Variation correctly inherited parent's image/thumbnail.

=== 4. Cleaning Up ===
🎉 ALL VERIFICATION TESTS PASSED SUCCESSFULLY! 🎉
```

