# Registro de Modificações IA

---

### Refatoração de Domínio: Encapsulamento Total do Registro de Clientes

- **Implementação:** Criação do método `registerCustomer()` no `CustomerRepository`, assumindo integralmente a responsabilidade de validar dados de formulário, campos customizados (Custom Fields), regras de força de senha, aceitação de termos e hidratação da Entidade `Customer` (via `EntityMapper`). No controlador `register.php`, mais de 100 linhas de código procedural e injeções de classes orfãs foram suprimidas.
- **Motivo:** O controlador de registro continuava a violar o padrão *Skinny Controller*, executando regras de negócio pesadas e injetando *fallbacks* de infraestrutura do MySQL (como IP e datas em branco) diretamente no escopo da requisição HTTP, acoplando a interface ao armazenamento de forma incorreta.
- **Benefício:** Redução massiva da complexidade do controlador de registro (que agora atua como um simples despachante). A Alpha Engine ganhou uma API sólida de criação de clientes. Qualquer sistema futuro (como um app mobile, um modal de compra rápida, ou uma importação em lote) que instanciar o `$customerRepository->registerCustomer()` terá exatamente as mesmas validações de segurança e regras de salvamento rigorosas.