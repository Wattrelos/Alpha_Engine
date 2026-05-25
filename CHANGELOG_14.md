# Registro de Modificações IA

---

### Correção de Erro Fatal: White Screen of Death (WSOD) no Login

- **Implementação:** Substituição definitiva das chamadas do método obsoleto `findOneBy()` pelo método `search()` nas camadas de Domínio: `CustomerRepository`, `UserRepository`, `LanguageRepository`, `ProductRepository` e `LanguageMapper`.
- **Motivo:** Como complemento à refatoração do ORM (`CHANGELOG_7`), essas classes ainda possuíam resquícios do método antigo. Durante o acesso à página de Login, o OpenCart acionava o `CustomerRepository` para recuperar possíveis sessões passadas ou preparar validações de cadastro. A invocação do método inexistente causava um *Fatal Error* que o servidor Nginx/Apache interceptava (HTTP 500) descartando o *Output* de erro do PHP, resultando em uma tela completamente branca.
- **Benefício:** Restaura o acesso total às páginas de Autenticação (`account/login`), Registro de Clientes e Administrativo, garantindo que todo o ecossistema de Repositórios esteja 100% aderente aos contratos de busca em *Arrays* do novo *BaseMapper*.