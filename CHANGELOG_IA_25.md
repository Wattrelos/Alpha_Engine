# Registro de Modificações IA (Sessão 25)

---

### Correção de Anomalias de Mapeamento: Cron
**Data:** [Data Atual]
**O que foi feito:**
- Criada e aplicada a versão final refatorada da entidade `Cron` em `core/Model/Domain/Entities/Cron.php`.
- Adicionadas as propriedades omitidas: `description` e `action`, além dos respectivos getters e setters com tipagem do PHP 8.4.
**Benefícios:**
- Garante que as strings de ações das tarefas CRON cadastradas no banco de dados (`db_schema.php`) possam ser corretamente lidas pelo DataMapper, permitindo a correta serialização e execução assíncrona do sistema (como no controlador de rotinas). Completa o ciclo de sincronização deste lote ORM.