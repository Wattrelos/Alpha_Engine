# Débitos Técnicos e Anti-Patterns do OpenCart (Sanados e Isolados)

Este documento registra as falhas de design arquitetural originais do OpenCart e documenta como a **Alpha Engine** as neutralizou na transição para o modelo standalone. Como o banco de dados ainda preserva a estrutura de tabelas herdada para garantir a compatibilidade com dados de cadastros históricos, as incoerências de esquema foram isoladas na camada de acesso a dados (DAO/Mappers).

---

## 1. O "Pseudo-Null" em Chaves Estrangeiras (FK = 0)
**Módulo Afetado:** Categorias (`parent_id`), Clientes (`customer_id` em sessões/carrinho), downloads, etc.

### O Problema Original:
O OpenCart armazena o valor inteiro `0` em vez de `NULL` para representar a ausência de um relacionamento (ex: categoria raiz sem categoria pai, ou visitante anônimo no carrinho). 
* **Impacto:** Isso impede a criação de chaves estrangeiras restritivas (`FOREIGN KEY`) reais no MySQL (que acusa erro de integridade, já que ID `0` não existe na tabela pai). Em um modelo fortemente tipado (DDD), isso causa quebras de hidratação.

### A Solução Standalone na Alpha Engine:
O `DataAccessObject` (DAO) da Alpha Engine atua como um escudo durante a hidratação recursiva via Reflection (`fillEntityRecursively`). Caso uma propriedade do tipo `InterfaceEntity` receba um valor `0` vindo do banco, o DAO converte-o automaticamente para `null` no objeto PHP de destino. Isso protege a tipagem estrita do PHP 8.4 sem exigir a alteração imediata de todos os registros históricos no MySQL.

### 🧹 Ação de Saneamento:
À medida que as tabelas de banco forem migradas permanentemente para o novo esquema nativo da Alpha Engine, todas as colunas que representam associações opcionais devem sofrer `ALTER TABLE` para permitir `NULL`, acompanhado da conversão dos dados: `UPDATE tabela SET coluna = NULL WHERE coluna = 0`.

---

## 2. Memory Leak de Eventos (O Acoplamento Recursivo de JSON)
**Módulo Afetado:** Sistema de Eventos de Inicialização (`event/language.php`).

### O Problema Original:
Para manter estados isolados de tradução entre controladores e componentes parciais (widgets), o OpenCart serializava arrays inteiros de chaves de tradução em strings JSON e as guardava recursivamente em propriedades estáticas. Em páginas com muitos blocos parciais, isso gerava um crescimento exponencial de consumo de memória RAM, estourando facilmente o limite de execução (`Allowed memory size exhausted`).

### A Solução Standalone na Alpha Engine:
Com a introdução do novo `BaseController` e do `TranslationRepository`, o sistema de internacionalização opera 100% via memória RAM controlada, utilizando pilhas nativas PHP (`$backupStack[] = $data`) que compartilham ponteiros em vez de duplicar strings. O consumo de memória RAM para carregar traduções foi reduzido a valores insignificantes.

---

## 3. Travamento de Conexões e Sessões Obesas
**Módulo Afetado:** Driver de Sessão do Banco de Dados.

### O Problema Original:
O OpenCart inicializava sessões sem controle sobre o tamanho do payload. Quando ocorriam erros ou loops no frontend, strings massivas de erro HTML ou dados brutos de depuração eram salvos na tabela `session`. Na requisição seguinte, a leitura desse registro gordo travava a conexão PDO ou causava estouro de memória no encerramento da execução.

### A Solução Standalone na Alpha Engine:
O runtime de sessão é gerenciado exclusivamente pela Alpha Engine. O `SessionMapper` executa uma auditoria de tamanho ativa antes de carregar o payload (`SELECT id, LENGTH(data)`). Se o tamanho exceder 5MB, a linha é imediatamente eliminada e uma sessão limpa é gerada para o visitante. Além disso, as conexões da Alpha Engine impõem queries bufferizadas (`PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true`), eliminando os travamentos por consultas concorrentes ativas.