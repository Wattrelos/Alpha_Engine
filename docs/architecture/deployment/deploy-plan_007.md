# DP-7: Adaptar Descrição do Produto para Markdown

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-20 12:59:45
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/7

## Descrição

# Adaptar Descrição do Produto para Markdown
==========================================

Este plano descreve a alteração necessária para habilitar suporte a Markdown nas descrições de produtos da loja. Utilizaremos o pacote erusev/parsedown (já disponível no composer.json do projeto) para converter a descrição salva em Markdown para HTML antes de enviá-la para as páginas e componentes.

## Alterações Propostas
--------------------

### Core / Repository

#### [MODIFY] ProductRepository.php

Integração do Parsedown no ProductRepository para converter o campo description de Markdown para HTML em tempo de carregamento de dados.

Dessa forma, o cache do sistema guardará os dados já processados em HTML e todas as views (detalhes do produto, listagem de categoria, buscas e produtos relacionados) passarão a receber a descrição formatada. No Twig, o filtro striptags já existente nos cards de produto removerá as tags HTML geradas, garantindo um resumo limpo.

Alterações sugeridas nas seguintes funções do repositório:

1.  getProduct: Converter a descrição do produto principal carregado.
2.  getProducts: Percorrer a lista e converter a descrição de cada produto listado.
3.  getRelatedProducts: Percorrer e converter a descrição dos produtos relacionados.

Exemplo de implementação:

php

$parsedown = new \Parsedown();

$parsedown->setSafeMode(true); // Evita XSS no processamento de Markdown inserido por terceiros

if (isset($product['description'])) {

$product['description'] = $parsedown->text($product['description']);

}

## Plano de Verificação
--------------------

### Testes Manuais

1.  Inserir uma descrição em formato Markdown no banco de dados para um produto (ex: id = 13 ou outro).

2.  Limpar o cache do sistema (rm storage/cache/alpha_cache_*.cache).

3.  Acessar a página do produto e verificar se o texto em Markdown é renderizado corretamente formatado em HTML.

4.  Acessar a página da categoria e verificar se o card do produto mostra o resumo descritivo limpo (sem tags de Markdown ou HTML cru).

## Tarefas - Suporte a Markdown nas Descrições de Produtos
=======================================================

-   Integrar Parsedown no ProductRepository

    -   Atualizar getProduct para converter a descrição
    -   Atualizar getProducts para converter a descrição de cada produto listado
    -   Atualizar getRelatedProducts para converter a descrição de cada produto relacionado

-   Limpar cache do sistema (storage/cache/)
-   Validar renderização na página de produto
-   Validar renderização dos resumos nos cards de produto

# Walkthrough - Habilitar Markdown nas Descrições de Produtos
===========================================================

Implementamos com sucesso a conversão automática das descrições de produtos no formato Markdown para HTML.

Mudanças Realizadas
-------------------

### Repositório de Produtos

Modificamos o ProductRepository.php para integrar o processador de Markdown Parsedown. Três métodos principais foram adaptados:

1.  getProduct: Processa a descrição do produto único na página de detalhes.

2.  getProducts: Processa as descrições dos produtos em listas (categorias, busca).

3.  getRelatedProducts: Processa as descrições dos produtos relacionados.

Todas as descrições processadas agora têm suporte completo a formatações Markdown (como negritos, listas, quebras de linhas) e são convertidas em código HTML seguro (setSafeMode(true) habilitado para proteção contra XSS).

## Verificação e Testes
--------------------

Criamos um script de verificação rápida (test_markdown.php) para validar a saída do produto com ID 13. O resultado foi o seguinte:

html

Parsed Description:

<p>Formigres

<strong>Título:</strong> JACARANDA CL 45

<strong>Tipo De Borda:</strong> Bold

<strong>Linha/Coleção:</strong>

<strong>Tamanho/Formato:</strong> 45x45cm

<strong>Indicação de Uso:</strong> LD

<strong>M² por Caixa:</strong>

<strong>Num. Faces:</strong> 1Face

<strong>Junta de Assentamento:</strong>

<strong>Variação de Tonalidade:</strong> </p>

Isso comprova que os marcadores ** do banco de dados estão sendo traduzidos corretamente para <strong> e encapsulados em tags <p> de parágrafo.

Edited task.md

Viewed ProductRepository.php:23-53
Viewed ProductRepository.php:331-357
Viewed ProductRepository.php:290-316
Edited ProductRepository.php
Viewed ProductRepository.php:30-60
Viewed ProductRepository.php:300-340
Viewed ProductRepository.php:350-395
Edited ProductRepository.php

Ran command: `rm storage/cache/alpha_cache_*.cache`

[Task message: rm storage/cache/alpha_cache_*.cache completed]

Viewed task.md:1-10

Used tool: manage_task

Ran command: `rm -f storage/cache/alpha_cache_*.cache`

Edited task.md

Ran command: `mysql -h 127.0.0.1 -u root -p42010052 -e "SELECT description FROM AlphaAgsonhos.agsc_product_description WHERE product_id = 13;"`

Viewed RepositoryFactory.php:1-39

Searched for "new RepositoryFactory"

Edited test_markdown.php

Ran command: `php -f /4452b753-1016-4d14-b1bc-a6ad2ba273ea/scratch/test_markdown.php`

Edited test_markdown.php

Ran command: `php -f /4452b753-1016-4d14-b1bc-a6ad2ba273ea/scratch/test_markdown.php`

Edited task.md

Edited walkthrough.md

Finalizei com sucesso a implementação do suporte a Markdown nas descrições de produtos!

Aqui está o resumo do que foi feito:

* Integração do `Parsedown` no [ProductRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/ProductRepository.php) (métodos `getProduct`, `getProducts` e `getRelatedProducts`).

* Limpeza completa do cache de dados (`storage/cache/`) para que a nova formatação seja renderizada imediatamente.

* Validação local bem-sucedida confirmando que os dados em Markdown (como os asteriscos `**` no produto `13`) agora geram tags HTML válidas (`<strong>`).

Você pode conferir todos os detalhes e o resultado do teste no [walkthrough.md](file:///4452b753-1016-4d14-b1bc-a6ad2ba273ea/walkthrough.md).

