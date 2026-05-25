# Registro de Modificações IA

---

### Refatoração e Limpeza: Remoção de Models Legadas na API do OpenCart

- **Implementação:** Substituição das chamadas diretas às models clássicas (`$this->load->model(...)`) pelo uso dos Repositórios e Mappers da Alpha Engine nas classes `api/payment_method.php`, `api/affiliate.php`, `api/customer.php`, `api/order.php` e `api/subscription.php`.
- **Motivo:** Estes controladores da API de retaguarda (utilizados pelo painel administrativo e integrações) ainda invocavam *Service Locators* do OpenCart para acessar banco de dados. Para consolidar a arquitetura de Domínio, toda a persistência de clientes, assinaturas, métodos de pagamento e pedidos foi roteada para os Repositórios unificados.
- **Benefício:** Redução de sobrecarga de memória (Overhead de Load), unificação de lógicas de negócio cruciais, melhor cobertura por Cache e remoção de bugs silenciosos (como a chamada equivocada de histórico de pedido para registrar status de assinatura, que existia antes da refatoração).