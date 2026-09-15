# DP-62: Internacionalização de Papéis de Usuário (User Group Descriptions)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-07-30 18:49:13
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/62

## Descrição

# Plano de Implementação - Internacionalização de Papéis de Usuário (User Group Descriptions)

Este plano descreve as etapas necessárias para integrar a nova tabela `agsc_user_group_description` às camadas de Entidade, Mapper, Repositório, Actions e Views da **Alpha Engine**, permitindo o gerenciamento e exibição multilíngue dos nomes dos papéis de usuário no painel administrativo.

---

## 🛠️ Alterações Propostas

### 1. Camada de Domínio & Persistência (Model / Mappers)

#### [MODIFY] [UserGroup.php](/core/Model/Domain/Entities/UserGroup.php)
- Adicionar suporte a descrições por idioma (`$descriptions = []`), onde a chave é o `language_id` e o valor é o nome traduzido.
- Manter `getName()` fornecendo fallback seguro (ex: nome no idioma atual ou nome legado).
- Adicionar métodos `getDescriptions()`, `setDescriptions(array $descriptions)` e `getNameByLanguage(int $languageId)`.

#### [MODIFY] [UserGroupMapper.php](/core/Mappers/EntityMappers/UserGroupMapper.php)
- Atualizar o método de busca (`find` e `findAll`) para realizar `LEFT JOIN agsc_user_group_description` com base no `language_id` informado.
- Adicionar método `findDescriptions(int $userGroupId): array` para carregar todas as traduções de um papel (útil na edição).
- Implementar a gravação/atualização em `agsc_user_group_description` no método `save()` para todos os idiomas ativos.

#### [MODIFY] [UserGroupRepository.php](/core/Model/Domain/Repositories/UserGroupRepository.php)
- Adicionar métodos para busca multilíngue (`findWithLanguage(int $id, int $languageId)` e `findAllWithLanguage(int $languageId)`).
- Adicionar método `saveWithDescriptions(UserGroup $userGroup, array $namesByLanguage)`.

---

### 2. Ações do Painel Administrativo (Actions)

#### [MODIFY] [ListUserGroupsAction.php](/core/Admin/Controllers/Actions/User/UserGroup/ListUserGroupsAction.php)
- Obter o ID do idioma ativo na sessão/request.
- Buscar a lista de papéis trazendo o nome traduzido correspondente ao idioma atual.

#### [MODIFY] [CreateUserGroupAction.php](/core/Admin/Controllers/Actions/User/UserGroup/CreateUserGroupAction.php)
- Carregar os idiomas ativos do sistema via `LanguageRepository`.
- Processar o payload contendo o nome traduzido para cada idioma (ex: `name[language_id]`).
- Persistir as entradas na tabela `agsc_user_group_description`.

#### [MODIFY] [EditUserGroupAction.php](/core/Admin/Controllers/Actions/User/UserGroup/EditUserGroupAction.php)
- Carregar as descrições existentes de todas as linguagens ativas para exibir no formulário.
- Atualizar os registros em `agsc_user_group_description` no envio do formulário.

#### [MODIFY] [DeleteUserGroupAction.php](/core/Admin/Controllers/Actions/User/UserGroup/DeleteUserGroupAction.php)
- Garantir a exclusão em cascata dos registros na `agsc_user_group_description` ao remover um grupo.

---

### 3. Camada de Apresentação (Twig Views)

#### [MODIFY] [user_group_form.html.twig](/resources/views/admin/user_group/user_group_form.html.twig)
- Adicionar suporte a abas de idiomas ou campos por idioma para a entrada do **Nome do Papel / Perfil**, identificando cada campo com a bandeira/código do idioma.
- Manter validação de token Anti-CSRF intacta.

---

## 🧪 Plano de Verificação

### Testes Automatizados / Scripts de Teste
- Criar um script em `tests/security_tests/test_user_group_i18n.php` que valida:
  1. Criação de um novo `UserGroup` com descrições em múltiplos idiomas (`language_id = 1` EN, `language_id = 2` PT-BR).
  2. Leitura filtrando por idioma ativo no repositório (`findWithLanguage`).
  3. Atualização das descrições de idiomas existentes.
  4. Exclusão em cascata das descrições ao excluir o grupo.

### Verificação Manual no Painel
- Acessar o painel administrativo em `/LPDHED2dC7Gjrg2b/papeis`.
- Testar a criação de um novo papel informando nomes nos diferentes idiomas disponíveis.
- Alterar o idioma do painel administrativo no topo da página e confirmar se os nomes dos papéis são atualizados dinamicamente de acordo com o idioma selecionado.

# Lista de Tarefas - Internacionalização de Papéis (User Group Descriptions)

- [x] Atualizar Camada de Domínio e Mappers
  - [x] Atualizar a entidade `UserGroup.php` com suporte a descrições por idioma
  - [x] Atualizar `UserGroupMapper.php` com JOINs, leitura e gravação em `agsc_user_group_description`
  - [x] Atualizar `UserGroupRepository.php` com métodos de busca/salvamento multilíngues
- [x] Atualizar Controladores Administrativos (Actions)
  - [x] Atualizar `ListUserGroupsAction.php` para exibir nome no idioma ativo
  - [x] Atualizar `CreateUserGroupAction.php` para carregar idiomas e salvar traduções
  - [x] Atualizar `EditUserGroupAction.php` para carregar e atualizar traduções por idioma
  - [x] Atualizar `DeleteUserGroupAction.php` para remover registros de descrição
- [x] Atualizar Interface Twig
  - [x] Atualizar `user_group_form.html.twig` para campos multilíngues por idioma
- [x] Teste e Validação
  - [x] Executar script de testes e verificar no banco MySQL

# Walkthrough - Internacionalização de Papéis de Usuário (agsc_user_group_description)

Implementamos com sucesso o suporte multilíngue para os nomes de papéis de usuários (User Groups) no ecossistema da **Alpha Engine**, consumindo a tabela `agsc_user_group_description` de forma integrada em todas as camadas da aplicação.

---

## 🛠️ Modificações Realizadas

### 1. Camada de Domínio & Persistência

#### [UserGroup.php](/core/Model/Domain/Entities/UserGroup.php)
- Adicionado atributo `$descriptions` (`[language_id => name]`).
- Adicionados métodos `getDescriptions()`, `setDescriptions(array $descriptions)` e `getNameByLanguage(int $languageId)`.

#### [UserGroupMapper.php](/core/Mappers/EntityMappers/UserGroupMapper.php)
- Implementado `findWithLanguage(int $id, int $languageId)` e `findAllWithLanguage(int $languageId)` realizando `LEFT JOIN agsc_user_group_description` com fallback seguro (`COALESCE(ugd.name, ug.name)`).
- Implementado `findDescriptions(int $userGroupId)` para recuperar todas as traduções de um grupo.
- Implementado `saveDescriptions(int $userGroupId, array $namesByLanguage)` e `deleteDescriptions(int $userGroupId)` operando com *prepared statements* via PDO `ConnectionDB`.

#### [UserGroupRepository.php](/core/Model/Domain/Repositories/UserGroupRepository.php)
- Métodos `find()` e `findAll()` ajustados para ler automaticamente as descrições no idioma ativo da sessão.
- Adicionado `saveWithDescriptions(UserGroup $userGroup, array $namesByLanguage)`.
- Método `delete($id)` atualizado para limpar descrições vinculadas.

#### [AbstractRepository.php](/core/Model/Domain/Repositories/AbstractRepository.php)
- Adicionado método `clearIdentityMap()` para possibilitar limpeza de cache do IdentityMap quando necessário em operações de lote.

---

### 2. Ações do Painel Administrativo (Controllers)

#### [CreateUserGroupAction.php](/core/Admin/Controllers/Actions/User/UserGroup/CreateUserGroupAction.php)
- Carrega os idiomas ativos do sistema via `LanguageRepository`.
- Processa o array `name[language_id]` no envio do formulário `POST` e persiste as descrições multilíngues na tabela `agsc_user_group_description`.

#### [EditUserGroupAction.php](/core/Admin/Controllers/Actions/User/UserGroup/EditUserGroupAction.php)
- Carrega as descrições de todas as linguagens ativas e envia para a view Twig.
- Atualiza as entradas na `agsc_user_group_description` ao submeter o formulário.

---

### 3. Camada de Apresentação (Twig Views)

#### [user_group_form.html.twig](/resources/views/admin/user_group/user_group_form.html.twig)
- Atualizado para renderizar campos de entrada individuais para cada idioma ativo com a identificação do idioma e bandeiras correspondentes, preenchendo automaticamente os valores salvos.
- Mantida injeção de tokens Anti-CSRF.

---

## 🧪 Validação e Testes Realizados

1. **Validação de Sintaxe PHP (Linting):**
   - Todos os arquivos PHP modificados passaram na checagem `php -l` sem erros de sintaxe.

2. **Teste Automatizado de Integração (`test_user_group_i18n.php`):**
   - Executado o script `tests/security_tests/test_user_group_i18n.php`.
   - **Resultado:**
     - Busca por idioma (EN / PT): ✅ Passou
     - Criação multilíngue (EN / PT / FR): ✅ Passou
     - Atualização de descrições: ✅ Passou
     - Exclusão e limpeza em cascata na `agsc_user_group_description`: ✅ Passou

