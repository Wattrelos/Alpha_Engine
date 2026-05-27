# Registro de Modificações IA

---

### Refatoração de Arquitetura e Limpeza: Controlador de Registro de Cliente

- **Implementação:** Remoção do construtor manual (`__construct`) que injetava brutalmente 5 repositórios (`CustomField`, `Customer`, `Information`, `CustomerGroup`, `Extension`) na inicialização da classe `register.php`. A substituição ocorreu invocando `$this->getRepository()` localmente apenas onde os dados são efetivamente demandados nos métodos `index()` e `register()`.
- **Motivo:** O controlador herdava o *Anti-Pattern* de instanciar todas as dependências no momento em que a classe era carregada pelo roteador, violando o princípio do *Lazy Loading*. Isso forçava o servidor a despender RAM excessiva para instanciar repositórios que sequer seriam usados caso o cliente abandonasse a página.
- **Benefício:** Redução massiva do uso de memória (`Memory Footprint`) no fluxo de cadastro. A classe agora obedece estritamente às diretrizes da Alpha Engine. O caminho também foi preparado para que, numa etapa futura, a lógica residual de instanciação e validação de senhas seja isolada 100% no *Domínio* (CustomerRepository).