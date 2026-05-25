# Registro de Modificações IA

---

### Refatoração: Remoção de Models Legadas no Core da Conta do Cliente

- **Implementação:** Substituição abrangente de todas as chamadas nativas de banco de dados (`$this->load->model()`) pela nova infraestrutura de Domínio e *Lazy Loading* (`$this->getRepository()`) nos arquivos `newsletter.php`, `order.php`, `payment_method.php` e `returns.php`. Além disso, foram removidas inclusões de modelos na memória que nunca eram de fato utilizadas no escopo dos métodos (como o `catalog/product` sendo carregado desnecessariamente em `order.php`).
- **Motivo:** O fluxo transacional da área do cliente, principalmente as páginas mais pesadas como a visualização de Pedidos e Solicitação de Devoluções, instigavam o *Service Locator* de forma bruta para instanciar classes antigas do OpenCart que duplicavam regras de negócio e criavam queries não otimizadas, indo na contramão da padronização dos Controladores da Alpha Engine.
- **Benefício:** Redução visível na requisição de memória (*Memory Footprint*) para visualização do histórico de pedidos. Melhorias na integridade do código, certificando de que faturas de compras, estornos e newsletters trafeguem utilizando os Mappers e Entidades centrais do Repositório da aplicação e isolando totalmente a interface de usuário de qualquer lógica suja de banco.