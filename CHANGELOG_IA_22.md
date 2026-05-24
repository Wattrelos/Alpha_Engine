# Registro de Modificações IA (Sessão 22)

---

### Refinamento da Heurística do Script Auditor (DetectarZumbis.php)
**Data:** [Data Atual]
**O que foi feito:**
- Atualização da lógica do auditor interno (`DetectarZumbis.php`) para extrair a definição de chaves primárias do script de banco de dados (`db_schema.php`).
- Inclusão de um supressor de alertas (bypass) que remove o `id` da lista de atributos "sobrando" no PHP sempre que a tabela equivalente não possui uma coluna `id` explícita ou utiliza Chaves Primárias Compostas (como tabelas pivot/`to_store` e tabelas de descrição/i18n).
**Benefícios:**
- Reduz substancialmente o ruído na saída do terminal, eliminando falsos positivos. Isso garante que a atenção do desenvolvedor fique focada estritamente em colunas essenciais que realmente foram esquecidas de serem mapeadas no Data Mapper.

### Correção de Mapeamento: ArticleDescription, Cron, Customer e CustomerAffiliate
**Data:** [Data Atual]
**O que foi feito:**
- Aplicadas correções de propriedades nas entidades de domínio para refletirem com exatidão as colunas do DB.
- Em `ArticleDescription`: Removido `title`, inseridos `name`, `image`, `tag`. Em `Cron`: Inseridos `description`, `action`.
**Benefícios:**
- O DataAccessObject agora hidrata plenamente os campos, prevenindo erros do Reflection e garantindo a inserção ou extração correta de dados no ecossistema Alpha.