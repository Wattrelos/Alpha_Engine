# Registro de Modificações IA

---

### Refatoração Estrutural: ExtensionRepository e ExtensionMapper

- **Implementação:** O `ExtensionRepository` foi reescrito para implementar a `BaseRepositoryInterface`, recebendo os métodos obrigatórios de contrato (`find`, `findAll`, `findBy`, `findOneBy`). O método `getExtensionsByType` foi adicionado ao repositório (com suporte a cache). O `ExtensionMapper` foi atualizado para utilizar o método nativo `search()`.
- **Motivo:** O repositório estava carente da interface base da Alpha Engine e de métodos universais de abstração de dados, o que causou múltiplos erros `Call to undefined method` durante a refatoração dos controladores de autenticação/cadastro. Além disso, o Mapper estava executando queries cruas (retornando arrays associativos) em vez de entidades.
- **Benefício:** Padronização absoluta do Domínio de Extensões. Agora, todas as chamadas feitas por controladores (como Carrinho, Login, Registro ou Pagamentos) a este repositório retornarão objetos tipados `Extension` com segurança, permitindo o uso fluido de bibliotecas modernas e prevenindo quebras em produção.