# Registro de Modificações IA (Sessão 31)

---

### Correção de Mapeamento: ExtensionPath, Gdpr e Notification
**Data:** [Data Atual]
**O que foi feito:**
- Entidade `ExtensionPath`: Removida a propriedade `extensionId` (sobrando) e adicionada `extensionInstallId` (faltando). Relacionamento de `Extension` alterado para `ExtensionInstall`.
- Entidade `Gdpr`: Removidas as propriedades e relacionamentos com `Customer` (pois o modelo foca a validação em `email` em vez de vincular a chave) e incluída a coluna `code`.
- Entidade `Notification`: Removidas colunas órfãs (`customerId`, `sender`, `link` e a relação) para seguir o mapeamento restrito da tabela global.
**Benefícios:**
- O Data Mapper agora vai hidratar as três entidades com 100% de integridade, eliminando anomalias e erros de sincronia na inserção e extração.