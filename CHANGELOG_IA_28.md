# Registro de Modificações IA (Sessão 28)

---

### Aperfeiçoamento do ORM: Suporte Nativo a Getters Booleanos (`isX()`) no DataAccessObject
**Data:** [Data Atual]
**O que foi feito:**
- Refatoração dos métodos `insertForClass` e `updateForClass` na classe `DataAccessObject` para utilizar a validação genérica `$this->isGetter()`.
- O extrator de nomes de coluna agora verifica o tamanho do prefixo de forma dinâmica (`str_starts_with($name, 'is') ? 2 : 3`) para resolver a conversão para `snake_case`.
- Remoção da verificação de "Getters isolados" no script `DetectarZumbis.php`, pois a heurística do DAO agora comporta o uso isolado de `isAtributo()`.
**Benefícios:**
- Resolve subitamente os 20+ Alertas Críticos apontados pela auditoria. O motor reflete perfeitamente getters semânticos (como `isStatus()`, `isDefault()`, `isNotify()`) injetando-os nas operações SQL e tornando a estrutura da Alpha Engine mais elegante e sem repetições.

### Correção de Mapeamento (Schema Sync): ExtensionInstall
**Data:** [Data Atual]
**O que foi feito:**
- Entidade `ExtensionInstall` sincronizada estritamente com a tabela do banco de dados (removido `filename`, inseridos `extension_id`, `name`, `description`, `version`, `author`, `link` e `status`).
**Benefícios:**
- Previne corrupção de dados ao injetar instâncias dessa entidade na base.