# 🔒 Análise de Desacoplamento do Sistema de Sessões (Alpha Engine)

Este documento analisa a viabilidade técnica e os caminhos de implementação para tratar as Sessões (`Session`) de forma 100% exclusiva no **Alpha Engine**, desvencilhando o sistema dos adaptadores legados do OpenCart.

---

## 🔍 Como as Sessões Funcionam Hoje (Estado Atual)

O OpenCart 4 inicializa a sessão através da biblioteca `\Opencart\System\Library\Session` usando o padrão **Adapter**. O motor configurado por padrão (definido em `system/config/catalog.php` e `admin.php`) é `'db'`.

Atualmente, a Alpha Engine já se infiltrou e neutralizou esse fluxo através de um **Bridge** no driver de banco de dados:
1. O core do OpenCart instancia `\Opencart\System\Library\Session('db', $registry)`.
2. O driver [db.php](file:///var/www/html/agsonhos/system/library/session/db.php) atua como um proxy: em vez de fazer queries SQL brutas na tabela `session`, ele delega todas as operações de I/O para o [SessionRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/SessionRepository.php).
3. O `SessionRepository` consome o [SessionMapper.php](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/SessionMapper.php), que gerencia a persistência de forma tipada, segura contra estouro de memória (bloqueando pacotes > 5MB) e transacionada via DAO.

### O Diagnóstico dos Recursos Atuais:
- **Temos os recursos necessários?** **Sim**. Já possuímos a Entidade `Session`, o `SessionMapper` com blindagens de segurança e o `SessionRepository` 100% funcionais no banco de dados.
- **Onde reside o acoplamento legado?** O acoplamento está nos outros adaptadores não-banco (como `file.php` e `redis.php`) que continuam operando de forma procedural, e na própria classe inicializadora do OpenCart (`system/library/session.php`).

---

## 🛠️ Cenários de Implementação para Exclusividade Alpha

Se o objetivo é nos desvencilharmos inteiramente do módulo original de sessão do OpenCart, temos dois caminhos:

### Opção 1: Substituição Integral por uma Classe de Sessão Alpha (Desacoplamento Total)
Podemos criar a classe `Alpha\Support\Session\AlphaSession`, com a mesma interface pública da classe original do OpenCart (propriedade pública `$data`, métodos `getId`, `start`, `close`, `destroy`, `gc`).

Em seguida, substituímos a instanciação da classe do OpenCart nos 4 pontos de entrada do sistema:
1. [system/framework.php](file:///var/www/html/agsonhos/system/framework.php#L200)
2. [catalog/controller/startup/session.php](file:///var/www/html/agsonhos/catalog/controller/startup/session.php#L17)
3. [core/Model/Domain/Repositories/StoreRepository.php](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/StoreRepository.php#L110)
4. `cron.php`

* **Prós**: 
  - Independência total de qualquer arquivo da pasta `system/library/session/`.
  - Permite unificar as estratégias de persistência de sessão (seja banco de dados ou arquivos temporários) através da `CacheStrategyInterface` (ex: `FilesystemCacheStrategy`) da própria engine Alpha, abolindo os arquivos `db.php`, `file.php` e `redis.php` legados.
* **Contras**:
  - Exige modificação direta no arquivo `system/framework.php` (embora este arquivo já contenha injeções da Alpha Engine, como o `AlphaContainer` e `RepositoryFactory`).

### Opção 2: Unificação e Proxy Geral via Adaptadores (Abordagem Strangler Fig)
Mantemos a classe principal `\Opencart\System\Library\Session` do OpenCart intacta, mas refatoramos os outros adaptadores (`file.php` e `redis.php`) para delegarem a leitura/escrita à Alpha Engine, semelhante ao que foi feito em `db.php` e `api.php`.

* **Prós**:
  - Evita alterar assinaturas de classe no `framework.php`.
  - Baixo risco de incompatibilidade com extensões de terceiros muito invasivas.
* **Contras**:
  - Mantém a pasta `system/library/session/` ativa com múltiplos arquivos apenas atuando como adaptadores.

---

## 💡 Recomendação Técnica

Dado que o banco de dados (`session_engine = 'db'`) é o padrão de produção da loja e **já está 100% migrado para a Alpha Engine**, não há necessidade imediata de implementar um motor de sessão do zero. Os recursos atuais já nos desvencilham do SQL procedural original.

Caso seja necessário suportar drivers de arquivo/Redis sob controle exclusivo da Alpha Engine no futuro, a **Opção 1** (introduzindo a `AlphaSession` no `framework.php` e plugando-a nas estratégias de cache do repositório) é a escolha ideal e conceitualmente mais pura para consolidar o domínio da aplicação.
