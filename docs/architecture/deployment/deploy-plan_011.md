# DP-11: Plano de Implementação: Adição de Fabricante e Logotipo nos Produtos

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-21 12:25:09
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/11

## Descrição

# Plano de Implementação: Adição de Fabricante e Logotipo nos Produtos

Este plano descreve as modificações necessárias para exibir o nome e logotipo do fabricante de um produto no card do produto (exibido na busca e categorias), na página de detalhe do produto e nos produtos relacionados.

## User Review Required

> [!IMPORTANT]
> A imagem do fabricante será redimensionada dinamicamente utilizando a classe utilitária `ImagePresenter` do sistema. Se um fabricante não possuir imagem atribuída ou se o arquivo correspondente estiver ausente, apenas o nome textual será exibido, mantendo a harmonia visual.

## Proposed Changes

---

### Backend: Mapeamento de Dados e Controllers

#### [MODIFY] [ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php)
- **`getProduct()`**: Ajustar a query de produto único adicionando `m.image AS manufacturer_logo` ao select de forma a expor a imagem do fabricante.
- **`getProducts()`**: Adicionar o `leftJoin` com a tabela `manufacturer` (`m`) através do relacionamento `p.manufacturer_id = m.id`. Adicionar `m.name AS manufacturer` e `m.image AS manufacturer_logo` ao select principal da listagem.
- **`getProductsByIds()`**: Adicionar o `leftJoin` com a tabela `manufacturer` (`m`) e expor `m.name AS manufacturer` e `m.image AS manufacturer_logo`.
- **`getRelated()`**: Adicionar o `leftJoin` com a tabela `manufacturer` (`m`) e expor `m.name AS manufacturer` e `m.image AS manufacturer_logo`.

#### [MODIFY] [ShowProductAction.php](/core/Controller/Actions/Product/ShowProductAction.php)
- Redimensionar a imagem do fabricante do produto principal (usando `60x60` px) se `manufacturer_logo` estiver definida, armazenando em `manufacturer_logo_thumb`.
- Redimensionar a imagem do fabricante dos produtos relacionados (`related`) se estiverem disponíveis (usando `40x40` px), armazenando em `manufacturer_logo_thumb`.

#### [MODIFY] [SearchAction.php](/core/Controller/Actions/Product/SearchAction.php)
- Redimensionar o logotipo do fabricante no laço de pós-processamento dos produtos encontrados (`products`) usando `40x40` px.

#### [MODIFY] [ShowCategoryAction.php](/core/Controller/Actions/Category/ShowCategoryAction.php)
- Redimensionar o logotipo do fabricante no laço de pós-processamento dos produtos da categoria (`products`) usando `40x40` px.

---

### Frontend: Templates Twig e Estilos

#### [MODIFY] [product-card.html.twig](/resources/views/pages/product/product-card.html.twig)
- Adicionar o bloco de informações do fabricante logo acima do título do produto para dar um visual moderno de e-commerce de marcas:
```twig
		{% if prod.manufacturer %}
			<div class="egen-prod-card-manufacturer">
				{% if prod.manufacturer_logo_thumb %}
					<img src="{{ prod.manufacturer_logo_thumb }}" alt="{{ prod.manufacturer }}" class="egen-prod-card-manufacturer-logo" loading="lazy">
				{% endif %}
				<span class="egen-prod-card-manufacturer-name">{{ prod.manufacturer }}</span>
			</div>
		{% endif %}
```

#### [MODIFY] [show.html.twig](/resources/views/pages/product/show.html.twig)
- Exibir as informações da marca no cabeçalho do produto, ao lado ou logo abaixo do código do modelo:
```twig
                {% if product.manufacturer %}
                    <div class="egen-product-info__manufacturer">
                        {% if product.manufacturer_logo_thumb %}
                            <img src="{{ product.manufacturer_logo_thumb }}" alt="{{ product.manufacturer }}" class="egen-product-info__manufacturer-logo">
                        {% endif %}
                        <span class="egen-product-info__manufacturer-name">{{ product.manufacturer }}</span>
                    </div>
                {% endif %}
```

#### [MODIFY] [new-stylesheet.css](/public_html/css/custom/new-stylesheet.css)
- Adicionar estilização CSS para o fabricante no card do produto e na página de detalhes, garantindo harmonização de layout e responsividade:
```css
/* Fabricante no Card de Produto */
.egen-prod-card-manufacturer {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.72rem;
    color: #a5b4fc;
    margin-bottom: 0.15rem;
}
.egen-prod-card-manufacturer-logo {
    width: 18px;
    height: 18px;
    object-fit: contain;
    border-radius: 4px;
    background: #ffffff;
    padding: 1px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.egen-prod-card-manufacturer-name {
    font-weight: 500;
}

/* Fabricante no Detalhe do Produto */
.egen-product-info__manufacturer {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.egen-product-info__manufacturer-logo {
    width: 28px;
    height: 28px;
    object-fit: contain;
    border-radius: 6px;
    background: #ffffff;
    padding: 2px;
    border: 1px solid var(--egen-border-color);
}
.egen-product-info__manufacturer-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--egen-primary-color);
}
```

## Verification Plan

### Manual Verification
1. Abrir a página de busca `/busca` e verificar se a marca e seu logotipo (se existirem no banco de dados) são listados em cada card de produto da listagem.
2. Abrir uma página de categoria `/categoria/{slug}` e validar a mesma exibição nos cards.
3. Acessar os detalhes do produto e constatar se a marca e logo aparecem no topo da coluna de especificações do produto, abaixo do modelo.
4. Validar se o logo e marca também aparecem nos cards de produtos recomendados ("relacionados") no rodapé da página de produto.

# Tarefas: Fabricante e Logotipo nos Produtos

- [x] Modificar o `ProductMapper.php` para buscar `m.name AS manufacturer` e `m.image AS manufacturer_logo` nos métodos `getProduct`, `getProducts`, `getProductsByIds` e `getRelated`.
- [x] Modificar o `ShowProductAction.php` para redimensionar o logotipo do fabricante do produto principal e dos produtos relacionados.
- [x] Modificar o `SearchAction.php` para redimensionar o logotipo do fabricante na listagem de produtos.
- [x] Modificar o `ShowCategoryAction.php` para redimensionar o logotipo do fabricante na listagem de produtos da categoria.
- [x] Atualizar o template Twig `product-card.html.twig` para exibir o nome e logotipo do fabricante.
- [x] Atualizar o template Twig `show.html.twig` para exibir o nome e logotipo do fabricante nos detalhes do produto.
- [x] Modificar o arquivo CSS `new-stylesheet.css` para adicionar os estilos visuais elegantes das marcas e logotipos.

# Walkthrough das Alterações: Fabricante e Logotipo de Produto

Implementamos a funcionalidade que permite exibir as informações da marca (fabricante), incluindo o nome e o logotipo correspondentes (quando cadastrados), tanto nas listagens de produtos quanto nas páginas de detalhes.

## Mudanças Realizadas

### 1. Camada de Persistência (Database)
- [ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php):
  - Atualizamos as consultas SQL de produto único (`getProduct`), listagem de produtos (`getProducts`), lote de IDs (`getProductsByIds`) e produtos recomendados/relacionados (`getRelated`).
  - Adicionamos o `LEFT JOIN` com a tabela de fabricantes `manufacturer` e expusemos as colunas `m.name AS manufacturer` e `m.image AS manufacturer_logo`.

### 2. Controladores (Actions)
- [ShowProductAction.php](/core/Controller/Actions/Product/ShowProductAction.php):
  - Redimensionamos dinamicamente os logotipos dos fabricantes usando a classe `ImagePresenter`: tamanho `60x60` px para o produto principal e `40x40` px para os produtos recomendados na seção de relacionados.
- [SearchAction.php](/core/Controller/Actions/Product/SearchAction.php):
  - Processamos o lote de produtos da busca e redimensionamos seus respectivos logos de fabricantes para `40x40` px.
- [ShowCategoryAction.php](/core/Controller/Actions/Category/ShowCategoryAction.php):
  - Processamos o lote de produtos da categoria ativa e redimensionamos seus respectivos logos de fabricantes para `40x40` px.

### 3. Templates Twig (Visão)
- [product-card.html.twig](/resources/views/pages/product/product-card.html.twig):
  - Adicionamos a exibição do logotipo e nome do fabricante antes do título do produto dentro do card.
- [show.html.twig](/resources/views/pages/product/show.html.twig):
  - Criamos o elemento `.egen-product-info__meta` agrupando o código do modelo e as informações da marca (logo e nome) no topo da coluna de compra.

### 4. Estilos Globais (Aparência)
- [new-stylesheet.css](/public_html/css/custom/new-stylesheet.css):
  - Adicionamos as regras de layout em CSS Flexbox para alinhar harmoniosamente os badges, logos e nomes de fabricantes.
  - O logotipo é renderizado em box com fundo contrastante e bordas arredondadas, mantendo a consistência com o tema visual escuro/moderno do e-commerce.

## Como Validar as Alterações
1. Faça uma busca no site ou acesse uma categoria e verifique se as marcas aparecem acima do nome do produto nos cards.
2. Acesse a página interna de um produto para ver o logo/nome do fabricante ao lado do modelo no cabeçalho do produto.
3. Se um fabricante não possuir imagem cadastrada na administração do e-commerce, a validação de segurança oculta a tag `<img>` e exibe apenas o nome da marca sem quebrar a UI.

