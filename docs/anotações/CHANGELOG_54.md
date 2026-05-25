# Registro de Modificações IA

---

### Refatoração de Segurança e Arquitetura: Controlador de Login (Account)

- **Implementação:** Remoção do construtor manual no `login.php` em favor do *Lazy Loading* via `$this->getRepository()`. Substituição das chamadas dispersas por operações centralizadas no Domínio: `isLockedOut()`, `authenticate()` e `resetLoginAttempts()`.
- **Motivo:** O Controlador estava violando o princípio de *Skinny Controller* e vazando regras de negócios em seu escopo (como realizar `password_verify` na mão). Essa prática desconsiderava lógicas cruciais internas do repositório, como o `password_needs_rehash`, que migra transparentemente senhas legadas fracas para hashes modernos durante o fluxo de entrada do cliente.
- **Benefício:** Restauração do isolamento da aplicação. A camada HTTP (Controller) agora atua como um mero despachante de requisição e resposta JSON, enquanto a segurança algorítmica e a proteção contra ataques de *força bruta* operam silenciosamente e de forma rigorosa nos bastidores do `CustomerRepository`.