# DP-101: Implementação de Detalhes do Produto no PDV

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-09-03 17:14:50
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/101

## Descrição

# Plano de Implementação - Detalhes do Produto no PD

Adição de uma área contextual de **Detalhes do Produto** no Terminal de Vendas (**PDV / POS**) da **Alpha Engine** ([register-control.twig](file:///var/www/html/agsonhos/backend/resources/views/pos/sales-rep/register-control.twig)), permitindo ao vendedor e operador consultar especificações técnicas completas (dimensões, peso, código de barras/EAN, estoque, categoria e descrição) em tempo real sem sair do fluxo de venda.

---

## Revisão do Usuário Necessária

> [!IMPORTANT]
> **Disposição Visual no Workspace do PDV**:
> Propomos um layout de **3 colunas fluidas** no desktop (`.catalog-panel` | `.product-detail-panel` | `.cart-panel`) com comportamento responsivo:
> - Em telas largas (≥ 1400px): Painel de detalhes fixo e visível permanentemente entre a grade e o carrinho.
> - Em telas médias (1024px – 1399px): Painel de detalhes com largura otimizada (~280px-300px), colapsável via botão se desejado.
> - Em telas menores/tablets (< 1024px): Painel em formato *drawer* (gaveta lateral deslizante) ou acionado por clique no card do produto.

> [!NOTE]
> **Latência Zero via Payload Enriquecido**:
> O endpoint `/pos/produtos/buscar` ([SearchProductAction.php](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/POS/SearchProductAction.php)) já executa `SELECT p.*`. Vamos enriquecer o retorno JSON com os atributos técnicos já disponíveis no banco (`ean`, `sku`, `weight`, `length`, `width`, `height`, `manufacturer`, `description`), garantindo renderização client-side instantânea ao focar no produto, sem overhead de novas requisições HTTP a cada clique.

---

## Mudanças Propostas

### 1. Backend API (Slim / Controllers)

#### [MODIFY] [SearchProductAction.php](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/POS/SearchProductAction.php)
- Enriquecer o array `$products[]` com os campos adicionais de domínio:
  - `sku`: Código SKU do produto.
  - `ean`: Código de barras / EAN / GTIN.
  - `manufacturer`: Nome do fabricante/marca.
  - `dimensions`: Array formatado com `length`, `width`, `height` e unidade (ex: `cm` / `m`).
  - `weight`: Peso numérico e formatado (ex: `0.45 kg`).
  - `description_clean`: Texto resumido/limpo da descrição (sem tags HTML perigosas para visualização rápida).
  - `stock_status_name`: Rótulo de disponibilidade (ex: "Em estoque", "Sob consulta").
- Enriquecer as variações filhas (`variants[]`) com `sku`, `ean`, `weight` e `dimensions` caso possuam especificações próprias.

---

### 2. Estilos & Design System do PDV (CSS)

#### [MODIFY] [layout.css](file:///var/www/html/agsonhos/public_html/css/pov/layout.css)
- Ajustar `.pos-workspace` para acomodar a terceira coluna contextual (`.product-detail-panel`).
- Configurar transições suaves e layout flexível / CSS Grid com breakpoints responsivos.

#### [MODIFY] [components.css](file:///var/www/html/agsonhos/public_html/css/pov/components.css)
- Criar a estilização de `.product-detail-panel`:
  - Efeito *glassmorphism* com `background: rgba(19, 27, 46, 0.75)` e `backdrop-filter: blur(12px)`.
  - Imagem ampliada do produto com efeito de zoom suave ao hover.
  - Badges de categoria, fabricante e status de estoque.
  - Grid de especificações físicas (Dimensões L×W×H, Peso, EAN, SKU).
  - Área de descrição com rolagem sutil.
  - Seção de ação rápida: seletor de quantidade e botão de inserção direta no carrinho.
  - Estado vazio (*empty state*) elegante com ícone informativo quando nenhum produto estiver focado.
- Adicionar estilo de foco ativo `.product-card.is-selected` com borda iluminada (`--accent-glow`).

---

### 3. Camada de Apresentação e Interação (Twig & JavaScript)

#### [MODIFY] [register-control.twig](file:///var/www/html/agsonhos/backend/resources/views/pos/sales-rep/register-control.twig)
- **Estrutura HTML**:
  - Inserir o container `<aside id="product_detail_panel" class="product-detail-panel">` entre a grade de catálogo e o painel de carrinho.
- **Lógica JavaScript**:
  - Estado `currentFocusedProduct`: armazena o produto selecionado/em foco.
  - Função `renderProductDetail(product, variant = null)`: preenche dinamicamente o painel com animação de entrada (fade-in).
  - Interatividade no catálogo:
    - Clique no card seleciona o produto e atualiza os detalhes.
    - Suporte a navegação por teclado (setas para navegar entre produtos e atualizar o painel de detalhes).
  - Ação no Painel de Detalhes:
    - Seletor de quantidade com botões `+` e `-`.
    - Botão "Adicionar ao Carrinho" integrado diretamente na ficha técnica.
  - Sincronização com o Modal de Variações: se o produto possui variações, a seleção de uma variação atualiza o painel de detalhes com os dados específicos daquela variação.

---

## Plano de Verificação

### Testes Automatizados
- Executar os testes unitários e de integração existentes da Alpha Engine para assegurar integridade da camada de controle e repositórios:
  ```bash
  vendor/bin/phpunit tests/
  ```

### Verificação Manual no Navegador
1. Acessar o PDV do Vendedor em `/admin/pos/vendedor`.
2. **Estado Inicial**: Verificar se o painel de detalhes exibe o estado padrão de instrução (*empty state*).
3. **Seleção de Produto Simples**:
   - Clicar em um produto no catálogo.
   - Validar se o painel carrega instantaneamente: imagem, nome, marca, SKU, código de barras (EAN), medidas (C x L x A), peso, estoque e descrição.
4. **Adição ao Carrinho**:
   - Ajustar a quantidade no painel de detalhes e clicar em "Adicionar".
   - Verificar se o item é inserido corretamente no carrinho de pré-venda com o valor e quantidade corretos.
5. **Seleção de Produto com Variações**:
   - Selecionar um produto com variações (ex: tamanhos/voltagens diferentes).
   - Verificar atualização contextual dos detalhes conforme a variação ativa.
6. **Responsividade**:
   - Reduzir a largura da janela para simular tablets e telas compactas.
   - Verificar adaptação correta do layout sem quebras visuais.

# Walkthrough - Implementação de Detalhes do Produto no PDV (Feature #85)

Implementação concluída com sucesso para a especificação [[FEATURE #85] Implementar Detalhes do Produto no PDV](file:///var/www/html/agsonhos/docs/issues/85_implementar_detalhes_do_produto_no_pdv.md).

---

## Modificações Realizadas

### 1. Backend & API de Busca do PDV
- **Arquivo**: [SearchProductAction.php](file:///var/www/html/agsonhos/backend/core/Admin/Controllers/Actions/POS/SearchProductAction.php)
- **Alterações**:
  - Enriquecimento do array JSON com os campos: `sku`, `ean`, `manufacturer`, `length`, `width`, `height`, `weight`, `description` (higienizada/sem tags) e `stock_status`.
  - Enriquecimento das variações filhas (`variants`) com dimensões, peso, estoque e status.
  - Tipagem PHPDoc no método `__invoke` com conformidade nível 6 no **PHPStan**.

```php
$products[] = [
    'product_id'        => $productId,
    'name'              => $p['name'],
    'model'             => $p['model'] ?? '',
    'sku'               => $p['sku'] ?? '',
    'ean'               => $p['ean'] ?? '',
    'manufacturer'      => $p['manufacturer'] ?? '',
    'price'             => $price,
    'special'           => $special,
    'price_formatted'   => $priceFormatted,
    'special_formatted' => $specialFormatted,
    'image'             => !empty($p['image']) ? ... : '/image/no-image.png',
    'thumb'             => $imagePresenter->resize($p['image'] ?? '', 80, 80),
    'quantity'          => (int)($p['quantity'] ?? 0),
    'weight'            => (float)($p['weight'] ?? 0),
    'length'            => (float)($p['length'] ?? 0),
    'width'             => (float)($p['width'] ?? 0),
    'height'            => (float)($p['height'] ?? 0),
    'description'       => $cleanDescription,
    'stock_status'      => (int)($p['quantity'] ?? 0) > 0 ? 'Em Estoque' : 'Esgotado',
    'variants'          => $variants,
];
```

---

### 2. Estilos & Layout (Design System POV)
- **Arquivos**:
  - [layout.css](file:///var/www/html/agsonhos/public_html/css/pov/layout.css): Estruturação do `.pos-workspace` em 3 colunas fluidas (`.catalog-panel` | `.product-detail-panel` | `.cart-panel`) com breakpoints responsivos.
  - [components.css](file:///var/www/html/agsonhos/public_html/css/pov/components.css):
    - Estilo do card ativo `.product-card.is-active` com borda iluminada (`--accent`).
    - Componente `.product-detail-panel` com glassmorphism, visualização de imagem com zoom hover, badges de disponibilidade/estoque, grid de especificações técnicas (EAN, SKU, CxLxA, Peso Líquido) e caixa de descrição.
    - Seletor numérico de quantidade e botão de inserção direta no carrinho de pré-venda.
    - Estado vazio (*empty state*) elegante para instruir o operador ao abrir o PDV.

---

### 3. Apresentação e Interatividade no PDV
- **Arquivo**: [register-control.twig](file:///var/www/html/agsonhos/backend/resources/views/pos/sales-rep/register-control.twig)
- **Alterações**:
  - Inclusão do container `<aside id="product_detail_panel" class="product-detail-panel">`.
  - Funções reativas em JavaScript:
    - `selectProduct(productId, variantId)`: Destaca o card selecionado e aciona `renderProductDetail()`.
    - `renderProductDetail(product, variant)`: Preenche dinamicamente todos os metadados técnicos com fallbacks para dados ausentes.
    - Controles de quantidade (`+` / `-`) e limite respeitando o estoque disponível.
    - Botão "Adicionar" no painel de detalhes integrado ao fluxo do carrinho de pré-venda e modal de variações.

---

## Validação & Testes

### 1. Análise Estática (PHPStan)
```bash
php backend/vendor/bin/phpstan analyse backend/core/Admin/Controllers/Actions/POS/SearchProductAction.php --level=6
```
**Resultado**: `[OK] No errors`

### 2. Suíte de Testes Automatizados (PHPUnit)
```bash
backend/vendor/bin/phpunit -c backend/phpunit.xml
```
**Resultado**: `Tests: 105, Assertions: 392, OK` (todos os 105 testes da aplicação passaram com sucesso).

