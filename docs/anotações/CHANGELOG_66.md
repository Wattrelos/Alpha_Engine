# Registro de Modificações IA

---

### Refatoração Final e Arquitetural: Remoção de Models MVC Legadas (Blog, Comments, Cron e Register)

- **Implementação:** Os controladores `cms/blog.php`, `cms/comment.php` e `cron/cron.php` foram ajustados para estender o `Alpha\Controller\BaseController`. Todas as instâncias do antigo carregador do OpenCart (`$this->load->model(...)`) foram removidas. Em seu lugar, injetou-se os componentes sob demanda (`TopicRepository`, `ArticleRepository`, `AntispamRepository`, `CronRepository`) pelo método `$this->getRepository(...)`. Complementarmente, foram removidas chamadas orfãs ao `checkout/payment_method` no arquivo `register.php`.
- **Motivo:** Estes controladores ainda quebravam a padronização e rodavam com a infraestrutura nativa antiga de acesso a banco de dados e controle. O acoplamento impedia o acesso ao *Lazy Loading* e o tratamento seguro da camada de view por meio do `BaseController`.
- **Benefício:** Redução de complexidade, limpeza de código e centralização de 100% da lógica da loja na arquitetura de repetição padrão, resultando em menor consumo de memória nas rotas institucionais e no processo periódico (Cron) do sistema.