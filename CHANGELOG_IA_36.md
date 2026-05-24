# Registro de Modificações IA (Sessão 36)

---

### Correção de Mapeamento: SubscriptionPlan e SubscriptionStatus
**Data:** [Data Atual]
**O que foi feito:**
- Entidade `SubscriptionPlan`: Removidas as colunas órfãs `price` e `trialPrice` que não faziam parte da tabela no banco de dados.
- Entidade `SubscriptionStatus`: Substituído o relacionamento incorreto de traduções (que usava OneToMany para uma tabela de description fictícia) pela injeção das colunas primárias compostas corretas: `subscription_status_id`, `language_id` e `name`.
**Benefícios:**
- O ORM agora sabe como salvar perfeitamente os dicionários de assinaturas no banco sem conflitar com "Unknown column", normalizando a forma como o OpenCart rastreia o andamento e renovação de contratos localizados.