# Registro de Modificações IA

---

### Correção de Bug: Variáveis de Tradução Faltando no Template de Frete

- **Implementação:** Instrução para adicionar a injeção do dicionário de idiomas no array `$data` dentro do controlador de frete (`extension/opencart/catalog/controller/checkout/shipping.php`).
- **Motivo:** O HTML da view de estimativa de frete (`shipping.twig`) estava apresentando partes incompletas, labels em branco e ausência de textos nos botões. Na arquitetura da Alpha Engine (e certas abordagens do OpenCart 4), o carregamento nativo não injeta automaticamente as chaves no escopo da View (Twig).
- **Benefício:** Restaura os textos dos rótulos, títulos e botões do painel de estimativa de frete, garantindo a acessibilidade e boa usabilidade para o usuário no frontend.