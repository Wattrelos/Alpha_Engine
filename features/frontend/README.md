# Especificações BDD: Módulo Frontend & Experiência do Usuário (UX/UI)

Este diretório contém os arquivos de especificação executável em **Gherkin (.feature)** dedicados à interface com o usuário, navegação no catálogo, busca facetada, página de produto (PDP) e painel de autoatendimento do cliente na **Alpha Engine**.

---

## 🗺️ Estrutura de Arquivos e Cenários

| Arquivo Feature | Funcionalidade / Casos de Uso | Requisitos Mapeados | Contexto Behat |
| :--- | :--- | :--- | :--- |
| [navegacao_catalogo.feature](/features/frontend/navegacao_catalogo.feature) | Vitrines da Home, Banners, Menu de Categorias, Breadcrumbs e Paginação | `RF003`, `RF011`, `RN018` | `FrontendContext` |
| [busca_filtros.feature](/features/frontend/busca_filtros.feature) | Busca Preditiva (Autocomplete), Filtros Facetados (Preço, Marca, Categoria) e Tratamento de Resultados Vazios | `RF011` | `FrontendContext` |
| [detalhes_produto_pdp.feature](/features/frontend/detalhes_produto_pdp.feature) | Galeria de Fotos HD com Zoom, Ficha Técnica, Seletor Dinâmico de Variantes e Seção de Cross-selling | `RF001`, `RF002`, `RF008`, `RF012`, `RN001`, `RN002`, `RN003` | `FrontendContext` |
| [painel_cliente.feature](/features/frontend/painel_cliente.feature) | Histórico de Pedidos, Rastreamento Last-mile, Gestão de Múltiplos Endereços e Logística Reversa (Devolução CDC) | `UC10`, `UC11`, `RF016`, `RF017`, `RF022`, `RN009`, `RN011`, `RN012` | `FrontendContext` |

---

## 🚀 Como Executar os Testes do Frontend

Execute a suíte específica de frontend via Behat:

```bash
# Execução direta das features de frontend
./vendor/bin/behat features/frontend/ --no-snippets

# Execução com visualização detalhada em tempo real
./vendor/bin/behat features/frontend/ --format=pretty
```

---

## 🧠 Arquitetura de Validação
Os passos implementados em `features/bootstrap/FrontendContext.php` interagem diretamente com o motor de renderização Twig, helpers de layout (`LayoutMapper`), controladores Slim 4 e os repositórios de dados, assegurando conformidade entre os requisitos de UX/UI e o backend da Alpha Engine.
