# Registro de Modificações IA

---

### Refatoração e Limpeza: APIs de Carrinho, Pedido e Frete (Lazy Loading Integrado)

- **Implementação:** Limpeza das chamadas legadas `$this->load->model('checkout/cart')` nos controladores `api/cart.php` e `api/order.php`, substituindo pelo uso do método `$cartRepository->getTotals()` da Alpha Engine. Além disso, a importação e chamada de classe antiga `$this->load->model('checkout/shipping_method')` em `api/shipping_method.php` foi refatorada para a estrutura nativa via `$this->mapper->get(ShippingMapper::class)`.
- **Motivo:** O código ignorava a injeção arquitetural de repositórios da Alpha Engine rodando Models antigas do núcleo MVC do OpenCart dentro do fluxo de finalização das requisições via API, duplicando regras de negócio com queries desnecessárias de banco de dados e alocações ineficientes de memória.
- **Benefício:** A rota da API passa a utilizar a mesma fonte da verdade de domínio, economizando o carregamento de arquivos na memória (Overhead de Load) e assegurando que descontos, regras de frete e totalizadores sigam 100% o padrão da Alpha Engine.