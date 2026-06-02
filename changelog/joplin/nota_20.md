---
### Melhoria: Injeção de Banners com Fragment Caching e Invalidação de Produtos (Home)
---
**Implementação:**
- Alteração da chave de cache nos painéis de produtos da página inicial para `home_latest_v3` e `home_featured_v3`. Adição do bloco estrutural `$this->remember()` para a chave estática `home_banner`, processando o carregamento dos banners originais da OpenCart (`model_design_banner`) e redimensionamento dinâmico de imagens.
**Motivo:**
- Análise profunda da sessão de debug revelou que as variáveis injetadas na View continham o setup de idioma de forma imaculada, porém dados de produtos continuavam retornando como arrays vazios em virtude da persistência (TTL) do envenenamento gerado por falhas prévias. Foi requisitada também uma prova de conceito para renderização nativa de Banners de interface fora do sistema de posições visuais do OpenCart.
**Benefício:**
- A mudança de chaves liberta o motor de produtos para ler ativamente do banco de dados na próxima requisição. A implementação do Banner proporciona ao desenvolvedor um ponto fixo ultra-rápido ($O(1)$ após 1º carregamento) de configuração de outdoors na página inicial da loja sem necessitar de widgets de interface.

