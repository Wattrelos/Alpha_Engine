# Registro de Modificações IA

---

### Refatoração: Ampliação do Lazy Loading nos Controladores de Conta

- **Implementação:** Verificação do controlador `account.php` onde confirmou-se que o Lazy Loading já estava sendo aplicado corretamente e de forma isolada. Em seguida, foram limpos os construtores remanescentes nos arquivos `edit.php`, `password.php`, `affiliate.php` e `wishlist.php`, substituindo a injeção inicial por chamadas de carregamento preguiçoso (`$this->getRepository(Class::class)`) invocadas sob demanda.
- **Motivo:** O carregamento desnecessário de múltiplos repositórios e mappers de banco durante a instanciação das classes causava um consumo estático de memória. As rotas internas de `Account` compartilhavam esse vício arquitetural do código legado.
- **Benefício:** Redução contínua do *Memory Footprint* em toda a área logada do cliente. Classes muito utilizadas, como as de edição de perfil e lista de desejos, instanciam objetos do Domínio e efetuam queries somente quando o código efetivamente entra nos desvios lógicos.