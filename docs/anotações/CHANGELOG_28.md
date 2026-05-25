# Registro de Modificações IA

---

### Refatoração: Aplicação de Lazy Loading no Controlador de Endereços (Address)

- **Implementação:** Remoção da injeção de dependência via construtor (`__construct`) no `catalog/controller/account/address.php` e substituição pelo carregamento tardio utilizando o método `$this->getRepository(Class::class)`. Também foram corrigidos links de redirecionamento que omitiam o parâmetro de idioma. Adicionalmente, foi corrigida a chamada de um método inexistente (`getFormattedAddresses`) no método `list()`, substituindo-o pelo método correto e existente (`getAddresses`).
- **Motivo:** Construtores engessados que injetam muitos repositórios podem impactar a performance e causar quebras no Reflection da Alpha Engine se chamados de forma inesperada pela engrenagem de rotas. O método `list()` tentava acessar um método inexistente no Repositório de Domínio, o que causaria um *Fatal Error* caso fosse acessado. Além disso, URLs de redirecionamento sem o parâmetro de idioma poderiam causar desvios de navegação no frontend.
- **Benefício:** Adoção de *Lazy Loading* (carregamento preguiçoso), onde os Repositórios são instanciados apenas no exato momento do seu uso, poupando memória em requisições que não passam por todos os fluxos. O código fica mais limpo, imune a erros de chamada de método e garante redirecionamentos seguros com manutenção consistente do idioma ativo.