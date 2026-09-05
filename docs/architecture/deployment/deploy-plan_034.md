# DP-34: Plano de Implementação - Controle de Concorrência Otimista (RMA)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-28 19:38:42
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/34

## Descrição

# Plano de Implementação - Controle de Concorrência Otimista (RMA)

Este plano descreve as alterações necessárias para implementar o controle de concorrência otimista (Optimistic Locking) no fluxo de devolução de produtos (RMA) no painel administrativo do e-commerce AgSonhos, corrigindo as brechas de concorrência e atualizando a experiência do usuário (UX) no dashboard administrativo.

## User Review Required

**IMPORTANT**
A tabela do banco de dados `agsc_product_return` foi alterada com sucesso para incluir a coluna `version` (INT, padrão 1). Este plano foca na implementação das regras de negócio de domínio, no repositório, nos controllers e na atualização do front-end/JS para suportar chamadas AJAX com tratamento de erro HTTP 409 Conflict.

## Proposed Changes

### Componente: Domain Entities & Repositories

#### \[NEW\] VersionedEntityInterface.php

Criar uma interface marcadora para as entidades que necessitam de controle de concorrência otimista via controle de versão.

  - Namespace: `Alpha\Model\Domain`
  - Declara métodos `getVersion()` e `setVersion(int $version)`.

#### \[MODIFY\] OrderReturn.php

Implementar `VersionedEntityInterface` e mapear a propriedade `version` para a tabela.

  - Adicionar propriedade privada `$version` (padrão 1).
  - Adicionar métodos getter e setter `getVersion()` e `setVersion(int $val)`.

#### \[MODIFY\] OrderReturnRepository.php

Adicionar suporte a salvar as atualizações da entidade `OrderReturn` no repositório.

  - Adicionar método público `save(InterfaceEntity $entity): ?int` delegando para o mapper.

#### \[MODIFY\] ReturnHistoryRepository.php

Adicionar método de persistência para registros de histórico.

  - Adicionar método público `save(InterfaceEntity $entity): ?int` delegando para o mapper.

### Componente: Data Access Object & Exception

#### \[NEW\] ConcurrencyException.php

Lançar esta exceção quando ocorrer uma falha de concorrência otimista (zero linhas afetadas no UPDATE com versão correspondente).

  - Namespace: `Alpha\Model\DataAccessObject`
  - Estende a classe base de exceções do PHP `\Exception`.

#### \[MODIFY\] DataAccessObject.php

Atualizar o método de atualização `updateForClass` para detectar se a entidade implementa `VersionedEntityInterface` e adicionar a cláusula `AND version = ?` no SQL.

  - Se for versionado, adicionar `version = version + 1` nas colunas de set e usar a versão antiga no `where` clause.
  - Verificar o retorno de `rowCount()`. Se retornar 0, lançar `ConcurrencyException`.
  - Atualizar o valor em memória na entidade para a nova versão (`version + 1`).

### Componente: Admin Controller (Backend)

#### \[MODIFY\] UpdateReturnStatusAction.php

Refatorar a ação para remover consultas SQL brutas e usar a infraestrutura de Repositórios/Mappers.

  - Carregar os dados enviados no request (compatível com URL-encoded e JSON payload do AJAX).
  - Buscar a entidade `OrderReturn` usando `OrderReturnRepository`.
  - Iniciar uma transação atômica via `UnitOfWork`.
  - Validar se a versão informada no request corresponde à versão atual.
  - Mudar os atributos da entidade (Status, Action, data de modificação).
  - Salvar a entidade pelo `OrderReturnRepository` dentro do bloco try-catch.
  - Registrar o histórico em `ReturnHistoryRepository`.
  - Tratar `ConcurrencyException`:
      - Se for requisição AJAX (header `Accept: application/json` ou corpo JSON), retornar HTTP 409 Conflict com JSON: `{ "error": "DATA_STALE", "current_data": { "version": X, "status_name": "Y", "return_status_id": A, "return_action_id": B, "date_modified": "Z" } }`.
      - Se for requisição tradicional, redirecionar com parâmetro `?error=concurrency_conflict`.

### Componente: Frontend & Templates

#### \[MODIFY\] show.html.twig

Atualizar o formulário do dashboard para incluir o campo de versão e interceptar o envio para usar AJAX.

  - Adicionar `<input type="hidden" name="version" id="return_version" value="{{ return.version }}">` no formulário.
  - Adicionar exibição do alerta de erro se houver conflito de concorrência na requisição convencional.
  - Importar o script `/js/updateReturnStatus.js`.
  - Adicionar evento de submit no formulário para interceptar o POST tradicional e chamar a lógica JS.

#### \[MODIFY\] updateReturnStatus.js

Ajustar o script JavaScript para bater no endpoint correto e interagir com o DOM da tela de detalhes da devolução.

  - Corrigir a rota para `/LPDHED2dC7Gjrg2b/devolucoes/${id}/status`.
  - Passar os parâmetros adicionais como `comment`, `return_action_id` e `notify`.
  - Implementar as funções auxiliares dinamicamente (Modais e Notificações):
      - `exibirModalDeConflito`: Cria e renderiza dinamicamente um modal estilizado na tela exibindo as informações atuais do banco e o botão para aplicar as atualizações.
      - `atualizarDadosNaTela`: Atualiza os elementos HTML do dashboard (status, versão oculta, data de modificação) sem recarregar a página.
      - `exibirMensagemSucesso` e `exibirMensagemErro`: Mostra alertas toasts ou banners flutuantes.

### Componente: Arquitetura & Documentação

#### \[MODIFY\] productReturnSequenceDiagram.puml

Atualizar as referências de URL no diagrama de sequência para condizer com a rota `/LPDHED2dC7Gjrg2b/devolucoes/{id}/status`.

#### \[MODIFY\] lock\_otimista\_falha.puml

Atualizar a rota `/devolucao/atualizar-status` para `/LPDHED2dC7Gjrg2b/devolucoes/{id}/status` para alinhar com o código de produção real.

## Verification Plan

### Automated Tests

  - Executar `php tests/TestReturnProducts.php` para garantir que o contêiner resolva as dependências corretamente.
  - Escrever um script de teste unitário simples em `tests/TestConcurrencyLock.php` que simule dois updates paralelos com a mesma versão inicial e verifique se o segundo lança `ConcurrencyException`.

### Manual Verification

  - Acessar a tela de detalhes de uma devolução, abrir a mesma página em duas abas/navegadores diferentes.
  - Na Aba 1, alterar o status para "Aprovado" e salvar.
  - Na Aba 2, tentar alterar o status para "Recusado".
  - Verificar se o modal de conflito é exibido corretamente na Aba 2, mostrando o novo status ("Aprovado") e impedindo a gravação com dados obsoletos.
  - Confirmar que o clique no botão "Atualizar dados" da Aba 2 atualiza o formulário e a tela perfeitamente sem refresh completo.

# Tarefas - Controle de Concorrência Otimista (RMA)

  - Criar a interface `VersionedEntityInterface.php`
  - Ajustar a entidade `OrderReturn.php` para implementar `VersionedEntityInterface` e gerenciar `version`
  - Adicionar método `save` em `OrderReturnRepository.php` e `ReturnHistoryRepository.php`
  - Criar a classe `ConcurrencyException.php`
  - Atualizar o `DataAccessObject.php` para implementar o bloqueio otimista em `updateForClass` e lançar `ConcurrencyException`
  - Refatorar o `UpdateReturnStatusAction.php` para usar repositórios e responder com 409 Conflict em requisições JSON / AJAX em caso de falha de concorrência
  - Ajustar o template `show.html.twig` para conter o campo oculto `version`, importar o JS e interceptar o formulário com AJAX
  - Atualizar o script `updateReturnStatus.js` com suporte a envio completo, modais de conflito dinâmicos, atualização de tela em tempo real e toasts de sucesso/erro
  - Atualizar os diagramas PlantUML `productReturnSequenceDiagram.puml` e `lock_otimista_falha.puml`
  - Validar e testar a implementação

# Walkthrough - Controle de Concorrência Otimista (RMA)

Todas as tarefas descritas no plano de implementação foram finalizadas com sucesso. Adicionamos suporte ao controle de versão concorrente (Optimistic Locking) no fluxo de devolução de produtos e criamos uma experiência de usuário (UX) premium no painel administrativo em caso de colisões de gravação.

## Alterações Realizadas

### Camada de Domínio e Banco de Dados

1.  **Alteração do Banco de Dados:** A tabela `agsc_product_return` foi alterada e agora contém a coluna `version` (INT, padrão 1) para rastrear o estado da versão de cada registro de devolução.
2.  **Interface Marcadora `VersionedEntityInterface.php`:** Criada a interface para marcar e gerenciar entidades versionadas pelo Motor ORM da Alpha Engine.
3.  **Entidade `OrderReturn.php`:**
      - Implementou a `VersionedEntityInterface`.
      - Adicionou a propriedade `$version` mapeada para a coluna do banco de dados.
      - Definiu a constante `TABLE_NAME = 'product_return'` para resolver conflitos de mapeamento automático de tabelas.
      - Adicionou getters/setters para o controle da versão.
4.  **Repositórios `OrderReturnRepository.php` e `ReturnHistoryRepository.php`:**
      - Adicionado o método público `save(InterfaceEntity $entity)` para expor de forma limpa a persistência de mappers.

### Camada de Persistência e Tratamento de Exceções

1.  **Exceção `ConcurrencyException.php`:** Criada para representar erros causados por colisões de concorrência.
2.  **Atualização do `DataAccessObject.php`:**
      - No método `updateForClass`, adicionou suporte reativo a bloqueio otimista. Ao atualizar uma entidade que implementa `VersionedEntityInterface`, inclui `SET version = version + 1 WHERE id = ? AND version = ?`.
      - Caso o número de linhas afetadas pelo update seja 0, uma `ConcurrencyException` é disparada.
      - Em caso de sucesso, incrementa localmente a propriedade `$version` em memória.
      - No método `update`, adicionou verificação para propagar `ConcurrencyException` sem engoli-la, permitindo que suba até o controller.

### Backend e Regras de Negócio (Admin)

1.  **Ação `UpdateReturnStatusAction.php`:**
      - Totalmente refatorada de acordo com as regras de arquitetura: removeu SQLs puros e passou a usar `OrderReturnRepository`, `ReturnHistoryRepository`, entidades e transações via `UnitOfWork`.
      - Suporta recebimento de dados tanto de formulário padrão (POST) quanto payloads JSON de chamadas AJAX.
      - Ao capturar uma `ConcurrencyException`:
          - Limpa o Identity Map estático da Alpha Engine para buscar dados limpos do banco de dados.
          - Monta dados frescos atualizados no banco de dados (novo status, data de modificação e versão recente).
          - Se for requisição AJAX, responde com status HTTP 409 Conflict e JSON contendo `{ error: "DATA_STALE", current_data: { ... } }`.
          - Se for requisição comum, redireciona o usuário para a página de detalhes com `?error=concurrency_conflict`.

### Frontend e Template Engine (View)

1.  **Template `show.html.twig`:**
      - Adicionou o campo oculto `version` no formulário de status da devolução.
      - Renderiza a versão atual do registro na tabela informativa.
      - Mostra banner de erro de concorrência se a requisição tradicional falhar.
      - Vincula IDs aos campos informativos chaves (`current_status_pill`, `current_action_name`, `info_date_modified`, `info_version`) para atualização reativa no DOM.
      - Intercepta o envio do formulário padrão via Javascript AJAX.
2.  **Script `updateReturnStatus.js`:**
      - Ajustada a rota do AJAX para bater em `/LPDHED2dC7Gjrg2b/devolucoes/${id}/status`.
      - Se a resposta for HTTP 409 Conflict, exibe um Modal de Conflito de Concorrência totalmente customizado e premium com blur de fundo.
      - O modal exibe os dados novos do servidor e fornece a ação "Carregar novos dados".
      - Se o usuário aceitar carregar, atualiza dinamicamente todos os elementos do DOM na tela sem refresh.
      - Se a resposta for sucesso, exibe um Toast verde animado de confirmação e recarrega a página após 1,2s para atualizar a timeline de histórico de forma limpa.

### Documentação Arquitetural

1.  **Diagrama de Sequência `productReturnSequenceDiagram.puml`:** Corrigida a rota no fluxo de escrita de `/devolucao/atualizar-status` para `/LPDHED2dC7Gjrg2b/devolucoes/{id}/status`.
2.  **Diagrama de Falha `lock_otimista_falha.puml`:** Corrigidas as rotas, nome da tabela (`oc_product_return`), parâmetros e propriedades de retorno de erro no payload JSON (`status_name` ao invés de `status`).

## Validação Executada

Um script de testes automatizados dedicado (`tests/TestConcurrencyLock.php`) foi criado e executado com sucesso:

  - **Passo 1:** Inseriu uma devolução temporária com `version = 1`.
  - **Passo 2:** Carregou duas instâncias independentes da mesma devolução (Usuário A e Usuário B), ambas na versão 1.
  - **Passo 3:** Usuário A realizou uma alteração e salvou. A versão no banco foi para 2 (em memória para o Usuário A também).
  - **Passo 4:** Usuário B tentou salvar sua alteração com base na versão obsoleta (1). O banco de dados retornou 0 linhas afetadas, lançando uma `ConcurrencyException` e revertendo a transação do Usuário B.
  - **Passo 5:** A exceção foi capturada com sucesso no teste, atestando o funcionamento perfeito do bloqueio.

