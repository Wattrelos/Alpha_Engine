# Registro de Modificações IA

---

### Refatoração: Limpeza de Anti-Patterns (Null Coalescing) nos Endereços de Checkout

- **Implementação:** Varredura e refatoração dos controladores de endereço (`payment_address.php` e `shipping_address.php`). Substituição de múltiplos blocos condicionais legados (`if (isset(...))`) pelo operador de coalescência nula do PHP 8.4 (`??`).
- **Motivo:** Embora os controladores já estivessem integrados com a Alpha Engine (`BaseController`, `loadLanguageData`, Repositórios), o código ainda carregava a verbosidade de checagem de variáveis nativa do OpenCart.
- **Benefício:** Consagração do padrão *Skinny Controller*. O código torna-se muito mais legível, enxuto e seguro perante o modo estrito do PHP, processando as requisições e a injeção de variáveis na View de forma direta e elegante.