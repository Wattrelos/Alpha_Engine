# UC_CLI_005 - Carregar Mídia & Cache On-Demand

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_005` |
| **Nome** | Carregar Mídia & Cache On-Demand |
| **Módulo** | Loja Virtual - Catálogo, Busca & Mídia |
| **Atores Primários** | Sistema / Gateway (Alpha Engine) |
| **Atores Secundários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Tipo** | Inclusão de `UC_CLI_003` (`<<include>>`) / Processamento em Background |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF001](/docs/requirements/functional/functional_requirements.yaml) (Imagens HD)<br>**RNF:** [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Desempenho, compressão WebP e TTFB < 200ms) |

---

## 1. 🎯 Descrição Sumária
Caso de uso sistêmico invocado durante a renderização de páginas de produtos, categorias e vitrines para gerenciar o redimensionamento dinâmico de imagens em formato moderno WebP com diferentes resoluções (thumbnails, galeria, zoom) e armazenar os payloads e metadados no cache Redis com tempo de vida (TTL) configurado.

---

## 2. ⚡ Pré-Condições
- Imagem original existente no diretório de armazenamento (`image/catalog/...`).
- Serviço de cache Redis ou camada de cache em arquivo disponível.

---

## 3. ✅ Pós-Condições
- A imagem solicitada é servida em formato comprimido WebP otimizado para o dispositivo do usuário (Desktop/Mobile), reduzindo consumo de banda e acelerando o carregamento.

---

## 4. 🚀 Gatilho (Trigger)
Uma requisição HTTP solicita a exibição de uma miniatura ou imagem redimensionada de produto.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Recebe a requisição para renderizar uma imagem de produto com dimensões específicas (ex: $500 \times 500\text{ px}$).
2. **Sistema:** Verifica se o arquivo comprimido WebP já existe no cache do disco (`image/cache/...`).
3. **Sistema:** Caso exista em cache, entrega imediatamente os bytes com cabeçalhos HTTP `Cache-Control: public, max-age=31536000, immutable`.
4. **Sistema:** Caso não exista, carrega o arquivo original de alta definição, processa o redimensionamento mantendo a proporção (com fundo branco ou transparente) e converte para WebP com qualidade otimizada.
5. **Sistema:** Salva o arquivo derivado no diretório de cache e o entrega ao navegador do usuário.
6. **Sistema:** Registra a chave no índice de mídias em Redis para controle de expurgo automático.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Fallback de Formato Legado:**
  1. O navegador cliente não envia o cabeçalho `Accept: image/webp`.
  2. O sistema gera a versão redimensionada em formato JPEG/PNG tradicional com compressão progressiva.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Imagem Original Ausente ou Corrompida:**
  1. O arquivo físico original não é encontrado no disco.
  2. O sistema entrega uma imagem padrão de placeholder estilizada (*"Imagem não disponível - Alpha Engine"*) com status HTTP 200 para evitar quebra do layout visual.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF002 (Velocidade e Desempenho):** Otimização de tempo de carregamento de imagens abaixo de 1 segundo para conexões 4G/3G.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Caminho da imagem original (`image_path`).
- Largura (`width`) e Altura (`height`) desejadas.

### Saídas:
- Fluxo de dados binários da imagem comprimida WebP com cabeçalhos HTTP de cache.
