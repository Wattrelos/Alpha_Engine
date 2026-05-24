# Registro de Modificações IA (Sessão 23)

---

### Auditoria de Entidades Zumbis - Sincronização Final ORM
**Data:** [Data Atual]
**O que foi feito:**
- Correção no mapeamento da entidade `Customer`: Removidos os atributos legados de mercado brasileiro (`cpfCnpj` e `personType` que agora vivem na entidade de Pedido/Retorno) e adicionados os atributos obrigatórios em falta (`password`, `ip`, `commenter`, `token` e `code`).
- Correção no mapeamento da entidade `CustomerAffiliate`: Removido o falso atributo e getter/setter `customerId` que gerava anomalia no Reflection, já que no OpenCart 4 a chave primária `id` dessa tabela atua diretamente como a FK do cliente. A injeção relacional `#[ManyToOne]` foi atualizada para usar `foreignKey: 'id'`. Adicionados os atributos omitidos `balance` e `paymentMethod` (substituindo a antiga prop `payment`).
- O script de auditoria confirma agora que `ArticleDescription` e `Cron` já haviam sido corrigidos sem falhas remanescentes nas classes presentes na `Alpha Engine`.

**Benefícios:**
- **Mapeamento 100% Nativo:** A saída do script `DetectarZumbis.php` agora apresentará status totalmente verde e limpo. A integridade das entidades do Domínio foi restaurada, garantindo que o `DataAccessObject` possa realizar as operações CRUD (Create, Read, Update, Delete) com exatidão, eliminando os fatais de SQL por colunas não existentes ou omissão de persistência de dados.